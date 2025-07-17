# Careers Manager Design System v2.0

## Overview

The Careers Manager plugin has been updated with a modern Tailwind-inspired design system that provides consistent styling, improved accessibility, and responsive design patterns.

## Design System Architecture

### Version History
- **v1.0**: Original design system with basic color palette and component styles
- **v2.0**: Tailwind-inspired design system with comprehensive utility classes and design tokens

### Core Principles
- **Utility-First**: Modular CSS classes for rapid development
- **Mobile-First**: Responsive design starting from mobile breakpoints
- **Accessibility**: WCAG 2.1 AA compliant color contrasts and interactions
- **Consistency**: Systematic approach to spacing, typography, and colors

## Design Tokens

### Color Palette
The design system uses a comprehensive gray scale with brand accent colors:

```css
/* Brand Colors */
--brand-red: #BF1E2D;
--brand-black: #000000;

/* Gray Scale (Tailwind-inspired) */
--gray-50: #f9fafb;   /* Lightest backgrounds */
--gray-100: #f3f4f6;  /* Light backgrounds */
--gray-200: #e5e7eb;  /* Borders */
--gray-300: #d1d5db;  /* Disabled states */
--gray-400: #9ca3af;  /* Placeholders */
--gray-500: #6b7280;  /* Muted text */
--gray-600: #4b5563;  /* Secondary text */
--gray-700: #374151;  /* Body text */
--gray-800: #1f2937;  /* Headings */
--gray-900: #111827;  /* Primary text */
```

### Typography Scale
Consistent font sizes following Tailwind conventions:

```css
--text-xs: 0.75rem;    /* 12px - Labels, captions */
--text-sm: 0.875rem;   /* 14px - Small text */
--text-base: 1rem;     /* 16px - Body text */
--text-lg: 1.125rem;   /* 18px - Large body */
--text-xl: 1.25rem;    /* 20px - Small headings */
--text-2xl: 1.5rem;    /* 24px - Section headings */
--text-3xl: 1.875rem;  /* 30px - Page titles */
--text-4xl: 2.25rem;   /* 36px - Hero text */
```

### Spacing Scale
Systematic spacing using rem units:

```css
--space-1: 0.25rem;    /* 4px */
--space-2: 0.5rem;     /* 8px */
--space-3: 0.75rem;    /* 12px */
--space-4: 1rem;       /* 16px */
--space-5: 1.25rem;    /* 20px */
--space-6: 1.5rem;     /* 24px */
--space-8: 2rem;       /* 32px */
--space-10: 2.5rem;    /* 40px */
--space-12: 3rem;      /* 48px */
```

### Border Radius
Consistent rounded corners:

```css
--radius-sm: 0.125rem;  /* 2px */
--radius: 0.25rem;      /* 4px */
--radius-md: 0.375rem;  /* 6px */
--radius-lg: 0.5rem;    /* 8px */
--radius-xl: 0.75rem;   /* 12px */
--radius-full: 9999px;  /* Fully rounded */
```

### Shadow System
Subtle depth using layered shadows:

```css
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
```

## Responsive Breakpoints

Mobile-first responsive design with standard breakpoints:

```css
--breakpoint-sm: 640px;   /* Small tablets */
--breakpoint-md: 768px;   /* Tablets */
--breakpoint-lg: 1024px;  /* Small desktops */
--breakpoint-xl: 1280px;  /* Large desktops */
--breakpoint-2xl: 1536px; /* Extra large screens */
```

## Utility Classes

### Color Utilities
```css
.text-gray-900    /* Primary text */
.text-gray-600    /* Secondary text */
.text-gray-500    /* Muted text */
.text-brand-red   /* Brand accent */

.bg-white         /* Card backgrounds */
.bg-gray-50       /* Page backgrounds */
.bg-brand-red     /* Brand backgrounds */

.border-gray-200  /* Subtle borders */
.border-gray-300  /* Prominent borders */
```

### Typography Utilities
```css
.text-xs, .text-sm, .text-base, .text-lg, .text-xl, .text-2xl, .text-3xl
.font-normal, .font-medium, .font-semibold, .font-bold
```

### Spacing Utilities
```css
.p-1, .p-2, .p-3, .p-4, .p-6, .p-8    /* Padding */
.m-1, .m-2, .m-3, .m-4, .m-6, .m-8    /* Margin */
.space-x-2, .space-y-4                /* Gap utilities */
```

## Implementation Status

### ✅ Completed (Task 1)
- [x] CSS custom properties for color palette, typography, and spacing
- [x] Tailwind-inspired utility class system
- [x] Responsive breakpoint system
- [x] Shadow and border radius systems
- [x] Component specification updates

### 🚧 In Progress
The following tasks are planned for the positions page redesign:
- Position card component redesign
- Badge system implementation
- ✅ Responsive grid system (Dashboard grid updated to mobile-first approach)
- Filter sidebar redesign
- Page header updates
- Interaction enhancements

## Usage Guidelines

### Component Development
When creating new components, use the design system tokens:

```css
.my-component {
  padding: var(--space-4);
  background: var(--background);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
}
```

### Responsive Design
Follow mobile-first approach:

```css
.responsive-component {
  /* Mobile styles first */
  grid-template-columns: 1fr;
}

@media (min-width: 768px) {
  .responsive-component {
    /* Tablet styles */
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .responsive-component {
    /* Desktop styles */
    grid-template-columns: repeat(3, 1fr);
  }
}
```

### Accessibility
- Maintain minimum 4.5:1 color contrast ratios
- Use semantic HTML structure
- Provide focus indicators for interactive elements
- Include appropriate ARIA labels

## File Structure

```
assets/css/
├── frontend.css     # Main design system and utility classes
├── admin.css        # Admin interface styles
└── widgets.css      # Widget-specific styles
```

## Browser Support

The design system supports:
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

## Migration Notes

### From v1.0 to v2.0
- Color variables have been updated to use Tailwind-inspired naming
- Typography scale now uses consistent rem units
- Spacing system follows Tailwind's 4px base unit
- Component specifications updated to use design tokens
- New utility classes available for rapid development

### Backward Compatibility
The v2.0 design system maintains backward compatibility with existing components while providing new utility classes for enhanced development.