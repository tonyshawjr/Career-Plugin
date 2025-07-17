# Design Document

## Overview

This design document outlines the redesign of the open positions page to adopt a modern Tailwind CSS aesthetic. The redesign will transform the current layout into a clean, utility-first design system that emphasizes visual hierarchy, responsive design, and smooth user interactions while maintaining all existing functionality.

The design will leverage Tailwind's design principles including consistent spacing scales, modern color palettes, subtle shadows, and responsive grid systems to create a professional, contemporary user experience.

## Architecture

### Current Architecture Analysis

The current implementation uses:
- `CareersShortcodes::careers_list_shortcode()` method for rendering positions
- Inline CSS styles within the shortcode output
- Grid-based layout with sidebar filters
- Custom CSS classes with traditional naming conventions

### New Architecture Approach

The redesigned system will:
- Maintain the existing PHP shortcode structure for compatibility
- Replace inline styles with Tailwind-inspired utility classes
- Implement a modular CSS architecture using CSS custom properties
- Preserve all existing functionality while enhancing visual presentation

### Design System Foundation

**Color Palette (Tailwind-inspired):**
- Primary: `#111827` (gray-900) - Main text
- Secondary: `#6b7280` (gray-500) - Muted text
- Accent: `#BF1E2D` (brand red) - CTAs and highlights
- Background: `#ffffff` (white) - Card backgrounds
- Surface: `#f9fafb` (gray-50) - Page background
- Border: `#e5e7eb` (gray-200) - Subtle borders

**Typography Scale:**
- Hero: `text-3xl` (30px) - Page title
- Heading: `text-xl` (20px) - Section headers
- Body: `text-base` (16px) - Main content
- Caption: `text-sm` (14px) - Meta information
- Label: `text-xs` (12px) - Form labels

**Spacing Scale:**
- xs: `0.25rem` (4px)
- sm: `0.5rem` (8px)
- md: `1rem` (16px)
- lg: `1.5rem` (24px)
- xl: `2rem` (32px)
- 2xl: `3rem` (48px)

## Components and Interfaces

### 1. Page Container Component

**Structure:**
```html
<div class="positions-page-container">
  <header class="page-header">
  <main class="page-content">
    <aside class="filters-sidebar">
    <section class="positions-grid">
</div>
```

**Styling Approach:**
- Container: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`
- Responsive padding with Tailwind breakpoints
- Clean typography hierarchy

### 2. Header Component

**Features:**
- Large, bold title with proper contrast
- Descriptive subtitle with muted color
- Consistent spacing using Tailwind scale

**Implementation:**
- Title: `text-3xl font-bold text-gray-900 mb-2`
- Subtitle: `text-lg text-gray-600 max-w-3xl`
- Container: `text-center mb-12 pb-8 border-b border-gray-200`

### 3. Filters Sidebar Component

**Design Principles:**
- Compact, sticky sidebar design
- Clean form controls with focus states
- Subtle background and borders
- Mobile-responsive collapse

**Key Features:**
- Sticky positioning: `sticky top-6`
- Card styling: `bg-white rounded-lg shadow-sm border border-gray-200`
- Form controls: Custom-styled selects with brand accent colors
- Clear filters link with hover states

### 4. Position Card Component

**Modern Card Design:**
- Clean white background with subtle shadow
- Rounded corners: `rounded-lg`
- Hover effects: `hover:shadow-md transition-shadow duration-200`
- Proper spacing and typography hierarchy

**Card Structure:**
```html
<div class="position-card">
  <header class="card-header">
    <h3 class="position-title">
    <div class="position-badges">
  </header>
  <div class="position-meta">
  <div class="position-description">
  <footer class="card-footer">
</div>
```

**Badge System:**
- Job type badges with color coding
- Certification badges with distinct styling
- Consistent pill design: `px-3 py-1 rounded-full text-xs font-medium`

### 5. Responsive Grid System

**Breakpoint Strategy:**
- Mobile: Single column grid (optimal for mobile readability)
- Tablet: 2-column grid (`md:grid-cols-2`)
- Desktop: 2-column grid (`lg:grid-cols-2`) - optimized for content density and readability
- Large screens: 2-column grid (`xl:grid-cols-2`) - maintains consistent layout

**Grid Implementation:**
```css
.positions-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: 1fr;
}

@media (min-width: 768px) {
  .positions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .positions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
```

## Data Models

### Position Card Data Structure

The existing position object structure will be maintained:

```php
$position = {
  id: integer,
  position_name: string,
  location: string,
  job_type: string,
  certification_required: string,
  position_overview: string,
  created_at: datetime,
  status: string
}
```

### Filter Data Structure

```php
$filters = {
  locations: array,
  modalities: array,
  certifications: array,
  active_filters: {
    location: string,
    modality: string,
    certification: string
  }
}
```

## Error Handling

### Empty States

**No Positions Found:**
- Centered layout with icon
- Clear messaging
- Suggestion to adjust filters
- Call-to-action to clear filters

**Implementation:**
```html
<div class="empty-state text-center py-16">
  <div class="w-16 h-16 mx-auto mb-4 text-gray-300">
    <!-- Icon SVG -->
  </div>
  <h3 class="text-lg font-medium text-gray-900 mb-2">No positions found</h3>
  <p class="text-gray-600 mb-6">Try adjusting your filters or check back later.</p>
  <button class="btn-secondary">Clear Filters</button>
</div>
```

### Loading States

**Filter Application:**
- Subtle loading indicators
- Disabled state styling for form controls
- Smooth transitions

### Error States

**Network Errors:**
- Toast notifications for filter failures
- Graceful degradation
- Retry mechanisms

## Testing Strategy

### Visual Regression Testing

**Key Test Cases:**
1. Card layout consistency across different content lengths
2. Responsive breakpoint behavior
3. Filter interaction states
4. Hover and focus states
5. Empty state presentation

### Cross-Browser Testing

**Target Browsers:**
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

### Accessibility Testing

**Requirements:**
- WCAG 2.1 AA compliance
- Keyboard navigation support
- Screen reader compatibility
- Color contrast validation (minimum 4.5:1 ratio)
- Focus indicators on all interactive elements

### Performance Testing

**Metrics:**
- Page load time under 3 seconds
- Smooth animations (60fps)
- Efficient CSS delivery
- Minimal layout shifts

### Responsive Testing

**Breakpoints:**
- Mobile: 320px - 767px
- Tablet: 768px - 1023px
- Desktop: 1024px - 1439px
- Large: 1440px+

**Test Scenarios:**
- Grid layout adaptation
- Filter sidebar behavior
- Typography scaling
- Touch interaction areas (minimum 44px)

## Implementation Approach

### Phase 1: CSS Architecture Setup
- Create utility class system
- Establish design tokens
- Set up responsive breakpoints

### Phase 2: Component Redesign
- Update position card styling
- Redesign filter sidebar
- Implement responsive grid

### Phase 3: Interaction Enhancement
- Add smooth transitions
- Implement hover states
- Enhance focus indicators

### Phase 4: Testing & Optimization
- Cross-browser testing
- Performance optimization
- Accessibility audit

## Integration Considerations

### WordPress Theme Compatibility
- Namespace all custom classes to avoid conflicts
- Use CSS custom properties for easy theme integration
- Maintain existing shortcode parameters

### Plugin Architecture
- Preserve existing PHP class structure
- Maintain backward compatibility
- Keep database queries unchanged

### Performance Impact
- Minimize CSS bundle size
- Use efficient selectors
- Implement critical CSS loading

The design maintains full compatibility with the existing WordPress plugin architecture while providing a modern, Tailwind-inspired user experience that will significantly improve the visual appeal and usability of the open positions page.