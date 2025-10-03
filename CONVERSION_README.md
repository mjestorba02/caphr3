# Bootstrap Template to Laravel Conversion

This project contains a Laravel application converted from a Bootstrap HTML template (Tiny Dashboard).

## What Was Done

### 1. Layout Structure

-   Created `resources/views/layouts/app.blade.php` - Main layout for dashboard pages
-   Created `resources/views/layouts/auth.blade.php` - Layout for authentication and error pages
-   Extracted common header, navigation, and footer elements

### 2. Blade Views

-   Converted all HTML files to Blade views that extend the appropriate layout
-   Updated all asset paths to use Laravel's `asset()` helper
-   Wrapped unique page content in `@section('content')` blocks
-   Added proper titles and scripts sections

### 3. Routes

-   Created comprehensive routes in `routes/web.php` for all pages
-   Used Laravel's named routes for navigation links
-   Organized routes by category (dashboard, UI elements, forms, etc.)

### 4. Asset Organization

-   All CSS, JS, images, and fonts are properly organized in the `public/` folder
-   Asset paths updated to use Laravel's asset helper
-   Navigation links updated to use Laravel's route helper

## Available Pages

### Dashboard Pages

-   `/` - Main Dashboard
-   `/dashboard-analytics` - Analytics Dashboard
-   `/dashboard-sales` - Sales Dashboard
-   `/dashboard-saas` - SaaS Dashboard
-   `/dashboard-system` - System Dashboard

### Layout Variations

-   `/index-horizontal` - Horizontal Layout
-   `/index-boxed` - Boxed Layout

### UI Elements

-   `/ui-color` - Colors
-   `/ui-typograpy` - Typography
-   `/ui-icons` - Icons
-   `/ui-buttons` - Buttons
-   `/ui-notification` - Notifications
-   `/ui-modals` - Modals
-   `/ui-tabs-accordion` - Tabs & Accordion
-   `/ui-progress` - Progress

### Forms

-   `/form-elements` - Basic Form Elements
-   `/form-advanced` - Advanced Form Elements
-   `/form-validation` - Form Validation
-   `/form-wizard` - Form Wizard
-   `/form-layouts` - Form Layouts
-   `/form-upload` - File Upload

### Tables

-   `/table-basic` - Basic Tables
-   `/table-advanced` - Advanced Tables
-   `/table-datatables` - Data Tables

### Charts

-   `/chart-inline` - Inline Charts
-   `/chart-chartjs` - Chart.js
-   `/chart-apexcharts` - ApexCharts
-   `/datamaps` - Data Maps

### Apps

-   `/calendar` - Calendar
-   `/contacts-list` - Contact List
-   `/contacts-grid` - Contact Grid
-   `/contacts-new` - New Contact
-   `/profile` - User Profile
-   `/profile-settings` - Profile Settings
-   `/profile-security` - Profile Security
-   `/profile-notification` - Profile Notifications
-   `/profile-posts` - Profile Posts
-   `/files-list` - Files List
-   `/files-grid` - Files Grid
-   `/support-center` - Support Center
-   `/support-tickets` - Support Tickets
-   `/support-ticket-detail` - Ticket Detail
-   `/support-faqs` - FAQs

### Pages

-   `/page-orders` - Orders
-   `/page-timeline` - Timeline
-   `/page-invoice` - Invoice
-   `/page-404` - 404 Error
-   `/page-500` - 500 Error
-   `/page-blank` - Blank Page
-   `/page-faqs` - FAQs

### Authentication

-   `/auth-login` - Login
-   `/auth-login-half` - Login (Half Layout)
-   `/auth-register` - Register
-   `/auth-resetpw` - Reset Password
-   `/auth-confirm` - Confirm Password

### Other

-   `/widgets` - Widgets
-   `/favicon` - Favicon

## How to Run

1. Make sure you have PHP and Composer installed
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure your database
4. Generate application key: `php artisan key:generate`
5. Start the development server: `php artisan serve`
6. Visit `http://localhost:8000` in your browser

## Features

-   ✅ Responsive design
-   ✅ Dark/Light theme switching
-   ✅ Interactive navigation
-   ✅ All original template functionality preserved
-   ✅ Laravel Blade templating
-   ✅ Proper asset management
-   ✅ Named routes for navigation

## Notes

-   All original HTML files have been converted to Blade views
-   Asset paths have been updated to use Laravel's asset helper
-   Navigation links use Laravel's route helper
-   The template maintains all its original functionality
-   Some pages that were missing from the original template have been created with placeholder content

## File Structure

```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php      # Main dashboard layout
│   │   └── auth.blade.php     # Auth/error pages layout
│   ├── dashboard.blade.php    # Main dashboard
│   ├── dashboard-analytics.blade.php
│   ├── dashboard-sales.blade.php
│   ├── dashboard-saas.blade.php
│   ├── dashboard-system.blade.php
│   ├── ui-*.blade.php         # UI element pages
│   ├── form-*.blade.php       # Form pages
│   ├── table-*.blade.php      # Table pages
│   ├── chart-*.blade.php      # Chart pages
│   ├── auth-*.blade.php       # Authentication pages
│   └── page-*.blade.php       # Other pages
routes/
└── web.php                    # All routes defined here
public/
├── css/                       # All CSS files
├── js/                        # All JavaScript files
├── assets/                    # Images and other assets
└── fonts/                     # Font files
```
