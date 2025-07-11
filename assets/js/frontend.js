/**
 * Careers Manager Frontend JavaScript
 */

(function($) {
    'use strict';

    var CareersManager = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Auth form tabs
            $(document).on('click', '.careers-auth-tab', this.handleAuthTabs);
            
            // Login form
            $(document).on('submit', '.careers-login-form', this.handleLogin);
            
            // Register form
            $(document).on('submit', '.careers-register-form', this.handleRegister);
            
            // Application form
            $(document).on('submit', '.careers-application-form', this.handleApplicationSubmission);
            
            // Dashboard actions
            $(document).on('click', '.careers-dashboard-action', this.handleDashboardAction);
            
            // File input validation
            $(document).on('change', 'input[type="file"]', this.validateFileInput);
            
            // Form validation
            $(document).on('blur', '.careers-form-group input, .careers-form-group select', this.validateField);
        },
        
        handleAuthTabs: function(e) {
            e.preventDefault();
            
            var $tab = $(this);
            var targetTab = $tab.data('tab');
            
            // Update tab appearance
            $('.careers-auth-tab').removeClass('active');
            $tab.addClass('active');
            
            // Show/hide forms
            $('.careers-auth-form-container').removeClass('active');
            $('#careers-' + targetTab + '-form-container').addClass('active');
        },
        
        handleLogin: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('input[type="submit"]');
            var originalText = $submitBtn.val();
            
            // Validate form
            if (!CareersManager.validateForm($form)) {
                return;
            }
            
            // Show loading state
            $submitBtn.val('Logging in...').prop('disabled', true);
            CareersManager.clearMessages($form);
            
            // Prepare data
            var formData = new FormData($form[0]);
            formData.append('action', 'careers_login');
            
            // Send AJAX request
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        CareersManager.showMessage($form, response.data.message, 'success');
                        
                        // Redirect after success
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                location.reload();
                            }
                        }, 1500);
                    } else {
                        CareersManager.showMessage($form, response.data, 'error');
                    }
                },
                error: function() {
                    CareersManager.showMessage($form, 'An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $submitBtn.val(originalText).prop('disabled', false);
                }
            });
        },
        
        handleRegister: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('input[type="submit"]');
            var originalText = $submitBtn.val();
            
            // Validate form
            if (!CareersManager.validateForm($form)) {
                return;
            }
            
            // Check password match
            var password = $form.find('input[name="password"]').val();
            var confirmPassword = $form.find('input[name="confirm_password"]').val();
            
            if (password !== confirmPassword) {
                CareersManager.showMessage($form, 'Passwords do not match.', 'error');
                return;
            }
            
            // Show loading state
            $submitBtn.val('Creating Account...').prop('disabled', true);
            CareersManager.clearMessages($form);
            
            // Prepare data
            var formData = new FormData($form[0]);
            formData.append('action', 'careers_register');
            
            // Send AJAX request
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        CareersManager.showMessage($form, response.data.message, 'success');
                        
                        // Redirect after success
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                location.reload();
                            }
                        }, 1500);
                    } else {
                        CareersManager.showMessage($form, response.data, 'error');
                    }
                },
                error: function() {
                    CareersManager.showMessage($form, 'An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $submitBtn.val(originalText).prop('disabled', false);
                }
            });
        },
        
        handleApplicationSubmission: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('.careers-submit-application');
            var originalText = $submitBtn.text();
            
            // Validate form
            if (!CareersManager.validateForm($form)) {
                return;
            }
            
            // Validate required files
            var resumeFile = $form.find('input[name="resume"]')[0].files[0];
            if (!resumeFile) {
                CareersManager.showMessage($form, 'Please upload your resume.', 'error');
                return;
            }
            
            // Show loading state
            $submitBtn.text('Submitting Application...').prop('disabled', true);
            CareersManager.clearMessages($form);
            
            // Prepare data
            var formData = new FormData($form[0]);
            formData.append('action', 'careers_submit_application');
            
            // Send AJAX request
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        CareersManager.showMessage($form, response.data.message, 'success');
                        
                        // Redirect after success
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            }
                        }, 2000);
                    } else {
                        CareersManager.showMessage($form, response.data, 'error');
                    }
                },
                error: function() {
                    CareersManager.showMessage($form, 'An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $submitBtn.text(originalText).prop('disabled', false);
                }
            });
        },
        
        handleDashboardAction: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var action = $button.data('action');
            var applicationId = $button.data('application-id');
            
            if (!action || !applicationId) {
                return;
            }
            
            // Confirm withdrawal
            if (action === 'withdraw_application') {
                if (!confirm('Are you sure you want to withdraw this application?')) {
                    return;
                }
            }
            
            // Show loading state
            var originalText = $button.text();
            $button.text('Processing...').prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'careers_dashboard_action',
                    dashboard_action: action,
                    application_id: applicationId,
                    nonce: careers_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show updated status
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        },
        
        validateFileInput: function() {
            var $input = $(this);
            var file = this.files[0];
            var $group = $input.closest('.careers-form-group');
            
            CareersManager.clearFieldError($group);
            
            if (file) {
                // Check file size (5MB limit)
                var maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    CareersManager.showFieldError($group, 'File size must be less than 5MB.');
                    $input.val('');
                    return;
                }
                
                // Check file type
                var allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (allowedTypes.indexOf(file.type) === -1) {
                    CareersManager.showFieldError($group, 'Only PDF, DOC, and DOCX files are allowed.');
                    $input.val('');
                    return;
                }
            }
        },
        
        validateField: function() {
            var $field = $(this);
            var $group = $field.closest('.careers-form-group');
            var value = $field.val().trim();
            var isRequired = $field.prop('required');
            
            CareersManager.clearFieldError($group);
            
            if (isRequired && !value) {
                CareersManager.showFieldError($group, 'This field is required.');
                return false;
            }
            
            // Email validation
            if ($field.attr('type') === 'email' && value) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    CareersManager.showFieldError($group, 'Please enter a valid email address.');
                    return false;
                }
            }
            
            // Phone validation
            if ($field.attr('type') === 'tel' && value) {
                var phoneRegex = /^[\+]?[\d\s\-\(\)]{10,}$/;
                if (!phoneRegex.test(value)) {
                    CareersManager.showFieldError($group, 'Please enter a valid phone number.');
                    return false;
                }
            }
            
            return true;
        },
        
        validateForm: function($form) {
            var isValid = true;
            
            // Clear all previous errors
            CareersManager.clearMessages($form);
            $form.find('.careers-form-group').removeClass('error');
            $form.find('.careers-error-message').remove();
            
            // Validate required fields
            $form.find('input[required], select[required], textarea[required]').each(function() {
                var $field = $(this);
                if (!CareersManager.validateField.call(this)) {
                    isValid = false;
                }
            });
            
            // Validate radio groups
            $form.find('input[type="radio"][required]').each(function() {
                var name = $(this).attr('name');
                if (!$form.find('input[name="' + name + '"]:checked').length) {
                    var $group = $(this).closest('.careers-form-group');
                    CareersManager.showFieldError($group, 'Please select an option.');
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        showFieldError: function($group, message) {
            $group.addClass('error');
            if (!$group.find('.careers-error-message').length) {
                $group.append('<div class="careers-error-message">' + message + '</div>');
            }
        },
        
        clearFieldError: function($group) {
            $group.removeClass('error');
            $group.find('.careers-error-message').remove();
        },
        
        showMessage: function($form, message, type) {
            CareersManager.clearMessages($form);
            
            var cssClass = type === 'success' ? 'careers-success-message' : 'careers-error-message';
            var $message = $('<div class="' + cssClass + '">' + message + '</div>');
            
            $form.prepend($message);
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: $message.offset().top - 20
            }, 300);
        },
        
        clearMessages: function($form) {
            $form.find('.careers-success-message, .careers-error-message').not('.careers-form-group .careers-error-message').remove();
        },
        
        // Utility function to show loading spinner
        showLoading: function($element) {
            $element.addClass('careers-loading');
        },
        
        hideLoading: function($element) {
            $element.removeClass('careers-loading');
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        CareersManager.init();
    });
    
    // Admin Dashboard functionality
    var CareersAdmin = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Add new job button
            $(document).on('click', '#add-new-job', this.showJobModal);
            
            // Edit job buttons
            $(document).on('click', '.edit-job', this.loadJobForEdit);
            
            // Save job button
            $(document).on('click', '#save-job', this.saveJob);
            
            // Cancel job button
            $(document).on('click', '#cancel-job', this.closeJobModal);
            
            // View application buttons
            $(document).on('click', '.view-application', this.loadApplication);
            
            // Status select change
            $(document).on('change', '.careers-status-select', this.updateApplicationStatus);
            
            // Update status button (in modal)
            $(document).on('click', '#update-application-status', this.updateApplicationStatusFromModal);
            
            // Modal close buttons
            $(document).on('click', '.careers-modal-close', this.closeModals);
            
            // Click outside modal to close
            $(document).on('click', '.careers-modal', function(e) {
                if (e.target === this) {
                    CareersAdmin.closeModals();
                }
            });
            
            // Escape key to close modals
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    CareersAdmin.closeModals();
                }
            });
            
            // Filter change handlers
            $(document).on('change', '#filter-status, #filter-job', this.applyFilters);
        },
        
        showJobModal: function() {
            $('#job-modal-title').text('Add New Job');
            $('#save-job').text('Create Job');
            CareersAdmin.loadJobData(0);
            $('#careers-job-modal').show();
        },
        
        loadJobForEdit: function() {
            var jobId = $(this).data('job-id');
            $('#job-modal-title').text('Edit Job');
            $('#save-job').text('Update Job');
            CareersAdmin.loadJobData(jobId);
            $('#careers-job-modal').show();
        },
        
        loadJobData: function(jobId) {
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'careers_admin_load_job',
                    job_id: jobId,
                    nonce: careers_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var job = response.data;
                        $('#job-id').val(job.job_id);
                        $('#job-title').val(job.title);
                        $('#job-location').val(job.location);
                        $('#job-description').val(job.content);
                        $('#job-employment-type').val(job.employment_type);
                        $('#job-experience-level').val(job.experience_level);
                        $('#job-salary-min').val(job.salary_min);
                        $('#job-salary-max').val(job.salary_max);
                        $('#job-benefits').val(job.benefits);
                        $('#job-status').val(job.status);
                    } else {
                        alert('Error loading job: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error loading job data');
                }
            });
        },
        
        saveJob: function() {
            var formData = {
                action: 'careers_admin_save_job',
                nonce: careers_ajax.nonce,
                job_id: $('#job-id').val(),
                job_title: $('#job-title').val(),
                job_location: $('#job-location').val(),
                job_description: $('#job-description').val(),
                job_employment_type: $('#job-employment-type').val(),
                job_experience_level: $('#job-experience-level').val(),
                job_salary_min: $('#job-salary-min').val(),
                job_salary_max: $('#job-salary-max').val(),
                job_benefits: $('#job-benefits').val(),
                job_status: $('#job-status').val()
            };
            
            // Disable save button
            $('#save-job').prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        CareersAdmin.closeJobModal();
                        // Reload the page to show updated data
                        window.location.reload();
                    } else {
                        alert('Error saving job: ' + response.data);
                        $('#save-job').prop('disabled', false).text('Save Job');
                    }
                },
                error: function() {
                    alert('Error saving job');
                    $('#save-job').prop('disabled', false).text('Save Job');
                }
            });
        },
        
        closeJobModal: function() {
            $('#careers-job-modal').hide();
            $('#careers-job-form')[0].reset();
            $('#save-job').prop('disabled', false);
        },
        
        loadApplication: function() {
            var applicationId = $(this).data('application-id');
            
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'careers_admin_load_application',
                    application_id: applicationId,
                    nonce: careers_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#application-details').html(response.data.html);
                        $('#careers-application-modal').show();
                    } else {
                        alert('Error loading application: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error loading application');
                }
            });
        },
        
        updateApplicationStatus: function() {
            var $select = $(this);
            var applicationId = $select.data('application-id');
            var newStatus = $select.val();
            var originalStatus = $select.data('original-status') || $select.find('option:selected').data('original');
            
            // Store original status if not already stored
            if (!originalStatus) {
                $select.data('original-status', $select.find('option:first').val());
            }
            
            if (confirm('Are you sure you want to update this application status?')) {
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_admin_update_status',
                        application_id: applicationId,
                        status: newStatus,
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the visual status if in a table
                            var $statusCell = $select.closest('.careers-table-row').find('.careers-status');
                            if ($statusCell.length) {
                                $statusCell.removeClass().addClass('careers-status careers-status-' + newStatus).text(newStatus);
                            }
                            
                            // Show success message
                            CareersAdmin.showNotification('Status updated successfully', 'success');
                        } else {
                            alert('Error updating status: ' + response.data);
                            // Revert select to original status
                            $select.val(originalStatus);
                        }
                    },
                    error: function() {
                        alert('Error updating status');
                        // Revert select to original status
                        $select.val(originalStatus);
                    }
                });
            } else {
                // User canceled, revert to original status
                $select.val(originalStatus);
            }
        },
        
        updateApplicationStatusFromModal: function() {
            var $select = $('#application-status-select');
            var applicationId = $select.data('application-id');
            var newStatus = $select.val();
            
            $.ajax({
                url: careers_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'careers_admin_update_status',
                    application_id: applicationId,
                    status: newStatus,
                    nonce: careers_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        CareersAdmin.showNotification('Status updated successfully', 'success');
                        // Close modal and reload page
                        CareersAdmin.closeModals();
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert('Error updating status: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error updating status');
                }
            });
        },
        
        closeModals: function() {
            $('.careers-modal').hide();
            $('#careers-job-form')[0].reset();
            $('#application-details').empty();
        },
        
        applyFilters: function() {
            var status = $('#filter-status').val();
            var jobId = $('#filter-job').val();
            
            var params = new URLSearchParams(window.location.search);
            
            if (status) {
                params.set('status', status);
            } else {
                params.delete('status');
            }
            
            if (jobId) {
                params.set('job_id', jobId);
            } else {
                params.delete('job_id');
            }
            
            // Update URL and reload
            window.location.search = params.toString();
        },
        
        showNotification: function(message, type) {
            var $notification = $('<div class="careers-notification careers-notification-' + type + '">' + message + '</div>');
            $('body').append($notification);
            
            // Fade in
            $notification.fadeIn();
            
            // Auto remove after 3 seconds
            setTimeout(function() {
                $notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };
    
    // Initialize admin functionality
    $(document).ready(function() {
        if ($('.careers-admin-dashboard').length) {
            CareersAdmin.init();
        }
    });

})(jQuery); 