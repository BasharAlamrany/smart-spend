# Alamrani Real Estate - Design System & UI Specifications

## 🎨 Design System Overview

This document outlines the complete design system for Alamrani Real Estate website, including colors, typography, components, spacing, and responsive specifications.

## 🎯 Design Principles

- **Accessibility First**: WCAG 2.1 AA compliance
- **Mobile First**: Responsive design starting from mobile
- **RTL/LTR Support**: Full bidirectional language support
- **Performance**: Optimized for fast loading
- **Consistency**: Unified visual language across all pages

## 🌈 Color Palette

### Primary Colors
```css
:root {
  --primary: #4FC3F7;           /* Light Blue - Main brand color */
  --primary-dark: #0B6E79;      /* Teal - Dark variant */
  --accent: #0B6E79;            /* Accent color for highlights */
}
```

### Semantic Colors
```css
:root {
  --success: #4CAF50;           /* Green - Success states */
  --warning: #FF9800;           /* Orange - Warning states */
  --error: #F44336;             /* Red - Error states */
  --info: #2196F3;              /* Blue - Information */
}
```

### Neutral Colors
```css
:root {
  --white: #FFFFFF;
  --gray-50: #FAFAFA;           /* Lightest gray */
  --gray-100: #F5F5F5;          /* Very light gray */
  --gray-200: #EEEEEE;          /* Light gray */
  --gray-300: #E0E0E0;          /* Medium light gray */
  --gray-400: #BDBDBD;          /* Medium gray */
  --gray-500: #9E9E9E;          /* Medium gray */
  --gray-600: #757575;          /* Medium dark gray */
  --gray-700: #616161;          /* Dark gray */
  --gray-800: #424242;          /* Very dark gray */
  --gray-900: #212121;          /* Darkest gray */
  --black: #000000;
}
```

### Text Colors
```css
:root {
  --text-primary: var(--gray-900);     /* Main text */
  --text-secondary: var(--gray-600);   /* Secondary text */
  --text-disabled: var(--gray-400);    /* Disabled text */
  --text-on-primary: var(--white);     /* Text on primary bg */
  --text-on-dark: var(--white);        /* Text on dark bg */
}
```

### Usage Guidelines
- **Primary**: Use for main CTAs, links, and brand elements
- **Primary Dark**: Use for hover states and emphasis
- **Gray Scale**: Use for text hierarchy and backgrounds
- **Semantic Colors**: Use for status indicators and feedback

## 🔤 Typography System

### Font Families
```css
:root {
  --font-arabic: 'Cairo', 'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-english: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-heading: 'Playfair Display', 'Cairo', serif;
  --font-mono: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
}
```

### Font Sizes (Responsive Scale)
```css
:root {
  --font-size-xs: 0.75rem;     /* 12px */
  --font-size-sm: 0.875rem;    /* 14px */
  --font-size-base: 1rem;      /* 16px */
  --font-size-lg: 1.125rem;    /* 18px */
  --font-size-xl: 1.25rem;     /* 20px */
  --font-size-2xl: 1.5rem;     /* 24px */
  --font-size-3xl: 1.875rem;   /* 30px */
  --font-size-4xl: 2.25rem;    /* 36px */
  --font-size-5xl: 3rem;       /* 48px */
  --font-size-6xl: 4rem;       /* 64px */
}
```

### Responsive Typography
```css
/* Desktop (≥1200px) */
h1 { font-size: clamp(2rem, 5vw, 4rem); }      /* 32px - 64px */
h2 { font-size: clamp(1.5rem, 4vw, 2.5rem); }  /* 24px - 40px */
body { font-size: 18px; }

/* Tablet (768px - 1199px) */
h1 { font-size: clamp(1.5rem, 4vw, 3rem); }    /* 24px - 48px */
h2 { font-size: clamp(1.25rem, 3vw, 2rem); }   /* 20px - 32px */
body { font-size: 16px; }

/* Mobile (<768px) */
h1 { font-size: clamp(1.25rem, 6vw, 2rem); }   /* 20px - 32px */
h2 { font-size: clamp(1.125rem, 4vw, 1.5rem); } /* 18px - 24px */
body { font-size: 15px; }
```

### Font Weights
```css
:root {
  --font-weight-light: 300;
  --font-weight-normal: 400;
  --font-weight-medium: 500;
  --font-weight-semibold: 600;
  --font-weight-bold: 700;
}
```

### Line Heights
```css
:root {
  --line-height-tight: 1.1;    /* Headings */
  --line-height-normal: 1.5;   /* Body text */
  --line-height-relaxed: 1.6;  /* Long form content */
}
```

## 📐 Spacing System

### Base Scale (4px Grid)
```css
:root {
  --space-1: 0.25rem;   /* 4px */
  --space-2: 0.5rem;    /* 8px */
  --space-3: 0.75rem;   /* 12px */
  --space-4: 1rem;      /* 16px */
  --space-5: 1.25rem;   /* 20px */
  --space-6: 1.5rem;    /* 24px */
  --space-8: 2rem;      /* 32px */
  --space-10: 2.5rem;   /* 40px */
  --space-12: 3rem;     /* 48px */
  --space-16: 4rem;     /* 64px */
  --space-20: 5rem;     /* 80px */
  --space-24: 6rem;     /* 96px */
}
```

### Usage Guidelines
- **4px/8px**: Small gaps, padding for compact elements
- **16px/24px**: Standard component padding and margins
- **32px/48px**: Section spacing and large component gaps
- **64px+**: Major section breaks and page-level spacing

## 🔘 Border Radius
```css
:root {
  --radius-sm: 0.25rem;   /* 4px - Small elements */
  --radius: 0.5rem;       /* 8px - Standard buttons, cards */
  --radius-lg: 0.75rem;   /* 12px - Large cards */
  --radius-xl: 1rem;      /* 16px - Hero sections */
  --radius-full: 9999px;  /* Circular elements */
}
```

## 🌑 Shadows & Elevation
```css
:root {
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
```

## 🎬 Animation & Transitions
```css
:root {
  --transition-fast: 150ms ease-in-out;
  --transition: 200ms ease-in-out;
  --transition-slow: 300ms ease-in-out;
}
```

## 📱 Breakpoints & Grid System

### Breakpoints
```css
:root {
  --breakpoint-sm: 576px;   /* Small devices */
  --breakpoint-md: 768px;   /* Tablets */
  --breakpoint-lg: 992px;   /* Small laptops */
  --breakpoint-xl: 1200px;  /* Desktops */
  --breakpoint-xxl: 1400px; /* Large screens */
}
```

### Container Widths
```css
:root {
  --container-sm: 540px;
  --container-md: 720px;
  --container-lg: 960px;
  --container-xl: 1140px;
  --container-xxl: 1320px;
}
```

### Grid System
- **12-column grid** with flexible column sizes
- **Responsive breakpoints** for all device sizes
- **CSS Grid and Flexbox** for modern layouts

## 🧩 Component Library

### 1. Buttons

#### Primary Button
```css
.btn-primary {
  background: var(--primary);
  color: var(--text-on-primary);
  padding: var(--space-3) var(--space-6);
  border-radius: var(--radius);
  font-weight: var(--font-weight-medium);
  min-height: 44px; /* Accessibility */
  transition: var(--transition);
}

.btn-primary:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}
```

#### Button Sizes
- **Small**: `min-height: 36px`, `padding: 8px 16px`
- **Default**: `min-height: 44px`, `padding: 12px 24px`
- **Large**: `min-height: 52px`, `padding: 16px 32px`

### 2. Form Elements

#### Input Fields
```css
.form-control {
  padding: var(--space-3) var(--space-4);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  font-size: var(--font-size-base);
  min-height: 44px;
  transition: border-color var(--transition);
}

.form-control:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(79, 195, 247, 0.25);
}
```

### 3. Cards

#### Property Card
```css
.property-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  overflow: hidden;
  transition: var(--transition);
}

.property-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}
```

#### Card Anatomy
- **Header**: Image with overlay badges
- **Body**: Title, location, features, price
- **Actions**: Favorite, quick view buttons

### 4. Navigation

#### Header
- **Height**: 80px on desktop, 60px on mobile
- **Background**: Semi-transparent white with backdrop blur
- **Logo**: Left-aligned (right-aligned for RTL)
- **Navigation**: Center-aligned horizontal menu
- **Actions**: Right-aligned (left-aligned for RTL)

### 5. Property Grid
- **Desktop**: 3-4 columns (1140px+ container)
- **Tablet**: 2-3 columns (768px - 1139px)
- **Mobile**: 1 column (< 768px)
- **Gap**: 32px between items

## 🌐 RTL/LTR Support

### Layout Considerations
```css
/* Automatic direction switching */
.ar {
  direction: rtl;
  text-align: right;
}

/* Icon and spacing adjustments */
.ar .icon-before {
  margin-left: var(--space-2);
  margin-right: 0;
}
```

### Typography for Arabic
```css
.ar {
  font-family: var(--font-arabic);
  font-feature-settings: "liga" 1, "kern" 1;
  text-rendering: optimizeLegibility;
}
```

## 📐 Component Specifications

### Hero Section
- **Height**: 100vh (desktop), 80vh (mobile)
- **Background**: Image with 45% dark overlay
- **Content**: Centered vertically and horizontally
- **Search Panel**: Positioned at bottom with backdrop blur

### Property Card Dimensions
```css
.property-card {
  aspect-ratio: 1.2; /* Width:Height ratio */
  min-height: 400px;
}

.property-image {
  aspect-ratio: 4/3; /* Standard property image ratio */
  height: 240px;
}
```

### Form Layout
- **Label spacing**: 8px below label
- **Input height**: 44px minimum (accessibility)
- **Field spacing**: 24px between form groups
- **Button spacing**: 32px above submit buttons

## 🎨 Visual Hierarchy

### Headings Scale
1. **H1**: Hero titles, main page headings
2. **H2**: Section headings, major subsections
3. **H3**: Card titles, minor section headings
4. **H4**: Subsection headings, form labels
5. **H5**: Small headings, metadata labels
6. **H6**: Fine print, captions

### Text Hierarchy
1. **Primary Text**: Main content, descriptions
2. **Secondary Text**: Supporting information, metadata
3. **Disabled Text**: Inactive states, placeholders

## 🔧 Accessibility Specifications

### Color Contrast
- **Normal Text**: 4.5:1 minimum contrast ratio
- **Large Text**: 3:1 minimum contrast ratio
- **UI Components**: 3:1 minimum contrast ratio

### Interactive Elements
- **Minimum Size**: 44x44px touch targets
- **Focus States**: 2px solid outline with 2px offset
- **Keyboard Navigation**: Logical tab order

### ARIA Labels
```html
<!-- Example implementations -->
<button aria-label="Add to favorites" class="favorite-btn">
<input aria-describedby="email-help" type="email">
<div role="alert" aria-live="polite">Error message</div>
```

## 📱 Responsive Design Specifications

### Mobile First Approach
1. **Base styles**: Mobile (< 768px)
2. **Progressive enhancement**: Tablet and desktop
3. **Touch-friendly**: 44px minimum touch targets
4. **Readable text**: 16px minimum font size

### Breakpoint Strategy
```css
/* Mobile First */
.component { /* Mobile styles */ }

@media (min-width: 768px) {
  .component { /* Tablet styles */ }
}

@media (min-width: 1200px) {
  .component { /* Desktop styles */ }
}
```

## 🎯 Performance Guidelines

### Image Optimization
- **Format**: WebP with JPEG fallback
- **Lazy Loading**: Implement for all images below fold
- **Responsive Images**: Use srcset for different screen sizes
- **Compression**: 80% quality for photos, PNG for graphics

### CSS Performance
- **Critical CSS**: Inline above-fold styles
- **Font Loading**: Use font-display: swap
- **Minification**: Minify CSS for production
- **Unused CSS**: Remove unused styles

## 🧪 Testing Specifications

### Browser Support
- **Modern Browsers**: Chrome 88+, Firefox 85+, Safari 14+, Edge 88+
- **Mobile**: iOS Safari 14+, Chrome Mobile 88+
- **Fallbacks**: Graceful degradation for older browsers

### Device Testing
- **Mobile**: iPhone 12, Samsung Galaxy S21, Pixel 5
- **Tablet**: iPad Pro, Samsung Tab S7
- **Desktop**: 1920x1080, 1366x768, 2560x1440

### Accessibility Testing
- **Screen Readers**: NVDA, JAWS, VoiceOver
- **Keyboard Navigation**: Tab order and focus management
- **Color Blindness**: Deuteranopia, Protanopia, Tritanopia

## 📊 Design Tokens (JSON Format)

```json
{
  "colors": {
    "primary": "#4FC3F7",
    "primary-dark": "#0B6E79",
    "success": "#4CAF50",
    "warning": "#FF9800",
    "error": "#F44336"
  },
  "spacing": {
    "xs": "4px",
    "sm": "8px",
    "md": "16px",
    "lg": "24px",
    "xl": "32px",
    "2xl": "48px",
    "3xl": "64px"
  },
  "typography": {
    "font-size": {
      "xs": "12px",
      "sm": "14px",
      "base": "16px",
      "lg": "18px",
      "xl": "20px",
      "2xl": "24px"
    }
  }
}
```

## 🎨 Figma Integration

### Design System Components
If using Figma, create these components:
1. **Buttons**: All variants and states
2. **Form Elements**: Inputs, selects, textareas
3. **Cards**: Property cards, info cards
4. **Navigation**: Header, mobile menu, breadcrumbs
5. **Icons**: Custom SVG icon set
6. **Typography**: Text styles for all headings and body text

### Auto-Layout Guidelines
- **Flexible containers**: Use auto-layout for responsive components
- **Consistent spacing**: Apply spacing tokens consistently
- **Component variants**: Create variants for different states
- **Responsive frames**: Design for mobile, tablet, and desktop

---

This design system ensures consistency, accessibility, and maintainability across the entire Alamrani Real Estate website. All components follow these specifications for a cohesive user experience.