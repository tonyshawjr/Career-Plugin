/**
 * Careers Manager Admin JavaScript
 */

(function($) {
    'use strict';

    var CareersAdmin = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Status select change
            $(document).on('change', '.careers-status-select', this.handleStatusChange);
            
            // View application button
            $(document).on('click', '.careers-view-application', this.handleViewApplication);
            
            // Modal close
            $(document).on('click', '.careers-modal-close', this.closeModal);
            $(document).on('click', '.careers-modal', function(e) {
                if (e.target === this) {
                    CareersAdmin.closeModal();
                }
            });
            
            // Escape key to close modal
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    CareersAdmin.closeModal();
                }
            });
        },
        
        handleStatusChange: function() {
            var $select = $(this);
            var applicationId = $select.data('application-id');
            var newStatus = $select.val();
            var originalStatus = $select.data('original-status') || $select.find('option:selected').data('original');
            
            // Store original status if not set
            if (!$select.data('original-status')) {
                $select.data('original-status', originalStatus || newStatus);
            }
            
            // Don't proceed if status hasn't changed
            if (newStatus === originalStatus) {
                return;
            }
            
            // Confirm status change
            if (!confirm('Are you sure you want to change the application status?')) {
                $select.val(originalStatus);
                return;
            }
            
            // Show loading state
            $select.prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'careers_admin_action',
                    admin_action: 'update_status',
                    application_id: applicationId,
                    new_status: newStatus,
                    nonce: careers_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update the original status
                        $select.data('original-status', newStatus);
                        
                        // Show success message
                        CareersAdmin.showNotice('Status updated successfully.', 'success');
                        
                        // Update any status displays in the row
                        var $row = $select.closest('tr');
                        $row.find('.careers-status').removeClass().addClass('careers-status careers-status-' + newStatus);
                        
                    } else {
                        // Revert to original status
                        $select.val(originalStatus);
                        CareersAdmin.showNotice('Error: ' + response.data, 'error');
                    }
                },
                error: function() {
                    // Revert to original status
                    $select.val(originalStatus);
                    CareersAdmin.showNotice('An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $select.prop('disabled', false);
                }
            });
        },
        
        handleViewApplication: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var applicationId = $button.data('application-id');
            
            if (!applicationId) {
                return;
            }
            
            // Show loading state
            var originalText = $button.text();
            $button.text('Loading...').prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'careers_admin_action',
                    admin_action: 'view_application',
                    application_id: applicationId,
                    nonce: careers_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        CareersAdmin.showModal(response.data);
                    } else {
                        CareersAdmin.showNotice('Error: ' + response.data, 'error');
                    }
                },
                error: function() {
                    CareersAdmin.showNotice('An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        },
        
        showModal: function(content) {
            var $modal = $('#careers-application-modal');
            var $content = $modal.find('#careers-application-details');
            
            $content.html(content);
            $modal.show();
            
            // Focus on modal for accessibility
            $modal.focus();
        },
        
        closeModal: function() {
            $('#careers-application-modal').hide();
        },
        
        showNotice: function(message, type) {
            // Remove existing notices
            $('.careers-admin-notice').remove();
            
            var cssClass = 'careers-admin-notice';
            if (type === 'error') {
                cssClass += ' error';
            } else if (type === 'success') {
                cssClass += ' success';
            }
            
            var $notice = $('<div class="' + cssClass + '">' + message + '</div>');
            
            // Add to top of content area
            if ($('.wrap h1').length) {
                $('.wrap h1').after($notice);
            } else {
                $('.wrap').prepend($notice);
            }
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(function() {
                    $notice.fadeOut();
                }, 3000);
            }
            
            // Scroll to notice
            $('html, body').animate({
                scrollTop: $notice.offset().top - 20
            }, 300);
        },
        
        // Utility functions
        confirmAction: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },
        
        showLoading: function($element) {
            $element.addClass('careers-admin-loading');
        },
        
        hideLoading: function($element) {
            $element.removeClass('careers-admin-loading');
        },
        
        // Bulk actions (for future enhancement)
        handleBulkActions: function() {
            var $form = $('#posts-filter');
            var action = $form.find('select[name="action"]').val();
            var $checked = $form.find('input[name="post[]"]:checked');
            
            if (!action || action === '-1') {
                alert('Please select an action.');
                return false;
            }
            
            if ($checked.length === 0) {
                alert('Please select items to perform this action on.');
                return false;
            }
            
            var message = 'Are you sure you want to perform this action on ' + $checked.length + ' item(s)?';
            return confirm(message);
        },
        
        // Chart animation (for analytics page)
        animateCharts: function() {
            $('.careers-status-fill').each(function() {
                var $fill = $(this);
                var width = $fill.data('width') || $fill.width();
                
                $fill.css('width', '0%').animate({
                    width: width + '%'
                }, 1000);
            });
        },
        
        // Export functionality (for future enhancement)
        exportData: function(type) {
            var params = new URLSearchParams(window.location.search);
            params.set('export', type);
            
            // Create temporary link and trigger download
            var $link = $('<a>');
            $link.attr('href', window.location.pathname + '?' + params.toString());
            $link.attr('download', '');
            $link[0].click();
        },
        
        // Search functionality
        initSearch: function() {
            var searchTimeout;
            
            $(document).on('input', '.careers-admin-search', function() {
                var $input = $(this);
                var query = $input.val();
                
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    CareersAdmin.performSearch(query);
                }, 300);
            });
        },
        
        performSearch: function(query) {
            // Add search parameters to URL and reload
            var params = new URLSearchParams(window.location.search);
            
            if (query) {
                params.set('s', query);
            } else {
                params.delete('s');
            }
            
            window.location.search = params.toString();
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        CareersAdmin.init();
        
        // Animate charts if on analytics page
        if ($('.careers-analytics-content').length) {
            setTimeout(function() {
                CareersAdmin.animateCharts();
            }, 500);
        }
    });
    
})(jQuery); 