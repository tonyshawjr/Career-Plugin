# Careers Manager Plugin

A comprehensive WordPress plugin for managing job listings, applications, and career-related functionality.

## Features

- Custom post type for job listings
- User registration and authentication
- Job application system with file uploads
- Admin dashboard for managing jobs and applications
- User dashboard for tracking applications
- Email notifications
- Frontend forms and interfaces

## Installation

1. Upload the `careers` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create necessary database tables and set up required functionality

## Available Shortcodes

### 1. Job Listings - `[careers_job_listings]`

Displays a list of available job positions with filtering options.

**Parameters:**
- `limit` (optional) - Number of jobs to display per page (default: 10)
- `show_filters` (optional) - Show/hide filter options (default: true)
- `show_search` (optional) - Show/hide search box (default: true)
- `columns` (optional) - Number of columns for job grid (default: 3)

**Usage Examples:**
```
[careers_job_listings]
[careers_job_listings limit="5"]
[careers_job_listings show_filters="false"]
[careers_job_listings limit="20" show_filters="true" columns="2"]
```

**Features:**
- Search by job title or keywords
- Filter by location, employment type, experience level
- Filter by modality, certification, and state (taxonomies)
- Pagination support
- Responsive design

---

### 2. Job Application Form - `[careers_apply_form]`

Displays the job application form for a specific job or general application.

**Parameters:**
- `job_id` (optional) - Specific job ID to apply for
- `redirect_url` (optional) - URL to redirect after successful application

**Usage Examples:**
```
[careers_apply_form]
[careers_apply_form job_id="123"]
[careers_apply_form job_id="123" redirect_url="/thank-you"]
```

**Features:**
- Personal information form
- Background questions (citizenship, employment eligibility, etc.)
- File uploads (resume required, cover letter optional)
- Email confirmation upon submission
- AJAX form submission

---

### 3. User Dashboard - `[careers_dashboard]`

Displays the applicant's personal dashboard with profile and applications.

**Parameters:**
- None

**Usage Examples:**
```
[careers_dashboard]
```

**Features:**
- Two-column layout: "My Profile" and "My Applications"
- View and edit personal information
- Track application status
- View applied jobs
- Download submitted documents
- Responsive design

---

### 4. Authentication Forms - `[careers_auth_form]`

Displays login and registration forms for users.

**Parameters:**
- `form` (optional) - Which form to show: "login", "register", or "both" (default: both)
- `redirect_url` (optional) - URL to redirect after successful login/registration

**Usage Examples:**
```
[careers_auth_form]
[careers_auth_form form="login"]
[careers_auth_form form="register"]
[careers_auth_form form="both" redirect_url="/dashboard"]
```

**Features:**
- User registration with email verification
- User login functionality
- Password reset capability
- Form validation and error handling
- AJAX form submission

---

### 5. Admin Dashboard - `[careers_admin_dashboard]`

Displays the administrative dashboard for managing jobs and applications (requires appropriate permissions).

**Parameters:**
- None

**Usage Examples:**
```
[careers_admin_dashboard]
```

**Features:**
- Dashboard overview with statistics
- Job management (create, edit, delete jobs)
- Application management and review
- View job applicants
- Change application status
- Export functionality
- Modal-based editing
- AJAX operations

**Access Control:**
- Only users with `manage_options` capability can access
- Typically administrators and editors

---

### 6. Debug Shortcode - `[careers_debug]`

Displays diagnostic information to troubleshoot plugin issues (temporary use only).

**Parameters:**
- None

**Usage Examples:**
```
[careers_debug]
```

**Features:**
- Check if plugin is properly activated
- Verify shortcode registration status
- Display WordPress environment info
- Show user permissions
- Provide step-by-step troubleshooting guide
- Real-time system diagnostics

**Note:** Remove this shortcode after troubleshooting is complete.

---

## Page Setup Recommendations

### For a Complete Career Portal:

1. **Jobs Page** - Create a page with `[careers_job_listings]`
2. **Apply Page** - Create a page with `[careers_apply_form]`
3. **Dashboard Page** - Create a page with `[careers_dashboard]`
4. **Login/Register Page** - Create a page with `[careers_auth_form]`
5. **Admin Dashboard Page** - Create a page with `[careers_admin_dashboard]` (for staff only)

### Sample Page Structure:

**Jobs Listing Page:**
```
<h1>Current Opportunities</h1>
<p>Explore our available positions and find your next career opportunity.</p>
[careers_job_listings]
```

**Application Page:**
```
<h1>Apply for Position</h1>
<p>Please fill out the form below to submit your application.</p>
[careers_apply_form]
```

**User Dashboard:**
```
<h1>My Career Dashboard</h1>
[careers_dashboard]
```

**Login Page:**
```
<h1>Account Access</h1>
[careers_auth_form]
```

**Admin Dashboard:**
```
<h1>Careers Administration</h1>
[careers_admin_dashboard]
```

## Database Tables

The plugin creates the following custom table:
- `wp_careers_applications` - Stores job application data

## Custom Post Type

- `career_job` - Job listings with comprehensive metadata

## Taxonomies

- `modality` - Work modality (remote, hybrid, on-site)
- `certification` - Required certifications
- `state` - Job location by state

## Email Templates

The plugin includes email templates for:
- Application confirmation
- Status updates
- Admin notifications

## File Uploads

- Resumes: Required for applications
- Cover Letters: Optional for applications
- Supported formats: PDF, DOC, DOCX
- File size limit: 5MB per file

## Security Features

- CSRF protection via WordPress nonces
- File upload validation
- User capability checks
- Sanitized input/output
- SQL injection prevention

## Support

For support and customization, refer to the plugin documentation or contact your developer.

## Version

Current Version: 1.0.0 