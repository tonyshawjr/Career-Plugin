# National Mobile X-Ray Design System

## Brand Identity
**Company:** National Mobile X-Ray - Healthcare diagnostic services  
**Primary Brand Color:** #BF1E2D (Brand Red) - Medical/healthcare red  
**Secondary Brand Color:** #000000 (Brand Black)  
**Industry Focus:** Mobile imaging, flexible healthcare careers

## Color System

### Light Mode Palette
- **Primary:** `hsl(222.2 47.4% 11.2%)` - Dark navy blue
- **Background:** `hsl(0 0% 100%)` - Pure white  
- **Foreground:** `hsl(222.2 84% 4.9%)` - Very dark blue
- **Secondary:** `hsl(210 40% 96.1%)` - Light blue-gray
- **Muted:** `hsl(215.4 16.3% 46.9%)` - Medium gray
- **Border:** `hsl(214.3 31.8% 91.4%)` - Light gray border

### Dark Mode Palette
- **Primary:** `hsl(210 40% 98%)` - Off-white
- **Background:** `hsl(222.2 84% 4.9%)` - Dark navy
- **Secondary:** `hsl(217.2 32.6% 17.5%)` - Dark blue-gray
- **Accent:** Medical red (#BF1E2D) for CTAs and highlights

### Brand Colors (Direct Usage)
- **Brand Red:** `#BF1E2D` - Used for CTAs, highlights, hover states
- **Brand Black:** `#000000` - Used for emphasis and contrast

## Typography & Layout
- **Headings:** Bold, tracking-tight fonts (4xl-6xl for heroes)
- **Body Text:** Clean, readable sans-serif
- **Navigation:** Medium weight, gray-700 base with brand-red hover
- **Container:** Centered, responsive padding (px-4 sm:px-6 lg:px-8)

## Component Design Patterns

### Buttons
- **Primary CTA:** `bg-brand-red` with white text, `red-700` hover
- **Secondary:** White/10 background with borders, glassmorphism effect
- **Navigation:** Gray-700 text with brand-red hover transitions

### Cards & Surfaces
- **Card:** White background with subtle shadows
- **Border Radius:** 0.5rem (--radius variable)
- **Shadows:** Soft, minimal shadow-sm for cards

### Animations
- **Fade In:** 0.3s ease-out with translateY(10px)
- **Transitions:** Consistent transition class usage
- **Hover Effects:** Subtle color and background changes

## Layout Architecture
- **Header:** White background, gray-200 border
- **Hero:** Dark overlay on images (gray-900/80 to gray-900/60)
- **Navigation:** Responsive with mobile hamburger menu
- **Grid:** Flexible, container-based responsive design

## Accessibility & UX
- **Focus States:** Ring-offset-background with visible focus rings
- **Mobile First:** Responsive design with breakpoints
- **Screen Reader:** Proper aria-labels and sr-only classes
- **Color Contrast:** High contrast ratios maintained

## Design Philosophy
- **Professional Healthcare:** Clean, trustworthy medical aesthetic
- **Flexibility Theme:** Emphasizes career flexibility and mobility
- **Human-Centered:** Warm brand red balances professional navy/gray
- **Modern Minimalism:** Clean lines, generous whitespace, subtle effects

## Dashboard Specific Components

### Admin Dashboard Layout
- **Tab Navigation:** Clean tabs with active states using brand red
- **Stats Cards:** White cards with colored borders and icons
- **Data Table:** Clean rows with hover states and action buttons
- **Action Buttons:** Brand red primary buttons with white icons

### Color Usage in Dashboard
- **Active Tab:** Brand red background (#BF1E2D)
- **Stats Icons:** Color-coded (blue, green, yellow, red) for different metrics
- **Action Links:** Brand red text with hover states
- **Row Hover:** Light gray background (#f8f9fa)

### Typography Scale
- **Page Title:** text-2xl font-bold text-gray-900
- **Section Headers:** text-lg font-semibold text-gray-900
- **Body Text:** text-sm text-gray-600
- **Data Labels:** text-xs text-gray-500

### Component Specifications
- **Tab Height:** 40px with 16px horizontal padding
- **Card Padding:** 24px all around
- **Table Row Height:** 48px minimum
- **Button Height:** 32px for small, 40px for medium
- **Border Radius:** 6px for cards, 4px for buttons