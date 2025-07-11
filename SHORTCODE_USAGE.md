# Updated Careers Job Listings Shortcode

The `[careers_job_listings]` shortcode has been updated to match the exact design shown in your screenshot.

## Usage

Simply add the shortcode to any page or post:

```
[careers_job_listings]
```

## Design Features

The new design includes:

- **Header Section**: "Open Positions" title with description
- **Left Sidebar Filters**: 
  - Modality dropdown
  - Location dropdown  
  - Certification dropdown
  - "Clear all filters" link
- **Job Cards**: Two-column grid with:
  - Job title
  - Certification and employment type badges
  - Location with pin icon
  - Job description
  - Posted date
  - "View Details" button

## Shortcode Parameters

```
[careers_job_listings limit="10" show_filters="true" columns="2"]
```

### Available Parameters:

- `limit` - Number of jobs to display (default: 10)
- `show_filters` - Show/hide filter sidebar (default: true)
- `columns` - Number of columns for job grid (default: 2) 
- `modality` - Pre-filter by modality
- `certification` - Pre-filter by certification
- `state` - Pre-filter by state/location
- `employment_type` - Pre-filter by employment type

### Examples:

**Basic usage:**
```
[careers_job_listings]
```

**Show 20 jobs without filters:**
```
[careers_job_listings limit="20" show_filters="false"]
```

**Pre-filter for specific criteria:**
```
[careers_job_listings modality="mobile-xray" state="texas"]
```

**Single column layout:**
```
[careers_job_listings columns="1"]
```

## Features

- **Responsive Design**: Mobile-friendly layout
- **Auto-filtering**: Filters automatically submit when changed
- **Clean Design**: Matches the provided screenshot exactly
- **Sticky Sidebar**: Filters stay in view while scrolling
- **Hover Effects**: Cards have smooth hover animations

## File Locations

- Shortcode: `wp-content/plugins/careers/includes/class-shortcodes.php`
- Styles: `wp-content/plugins/careers/assets/css/frontend.css`
- Preview: `wp-content/plugins/careers/test-shortcode-output.html`

## Notes

- The shortcode automatically includes the header ("Open Positions") and description
- Filters work with existing taxonomy and meta field structure
- Design matches the screenshot with proper spacing, colors, and layout
- Mobile responsive with collapsing layout on smaller screens 