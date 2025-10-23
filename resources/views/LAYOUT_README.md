# Livewire Layout System

This project includes a comprehensive Livewire layout system designed for modern web applications with Tailwind CSS and dark mode support.

## Layout Files

### 1. Main App Layout (`layouts/app.blade.php`)
The main layout file that includes navigation, header, main content area, and footer.

**Usage:**
```php
@extends('layouts.app')

@section('title', 'Page Title')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Page Header
    </h2>
@endsection

@section('content')
    <!-- Your page content here -->
@endsection
```

### 2. Authenticated Layout (`layouts/authenticated.blade.php`)
A layout specifically designed for authenticated users with navigation, user dropdown, and responsive design.

**Usage:**
```php
@extends('layouts.authenticated')

@section('title', 'Dashboard')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Dashboard
    </h2>
@endsection

@section('content')
    <!-- Your authenticated content here -->
@endsection
```

### 3. Guest Layout (`layouts/guest.blade.php`)
A clean layout for public pages like login, register, and landing pages.

**Usage:**
```php
@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <!-- Your guest content here -->
@endsection
```

## Reusable Components

### Navigation Components
- `nav-link.blade.php` - Navigation link with active state
- `responsive-nav-link.blade.php` - Mobile-responsive navigation link
- `dropdown.blade.php` - Dropdown menu component
- `dropdown-link.blade.php` - Dropdown menu item

### UI Components
- `button.blade.php` - Styled button with variants and sizes
- `input.blade.php` - Form input with validation states
- `alert.blade.php` - Alert messages with different variants
- `card.blade.php` - Card container component
- `section.blade.php` - Section wrapper component

## Component Usage Examples

### Button Component
```html
<!-- Primary button -->
<x-button variant="primary" size="md">Save</x-button>

<!-- Danger button -->
<x-button variant="danger" size="sm">Delete</x-button>

<!-- Outline button -->
<x-button variant="outline" size="lg">Cancel</x-button>
```

### Input Component
```html
<!-- Basic input -->
<x-input type="text" placeholder="Enter your name" />

<!-- Input with error -->
<x-input type="email" placeholder="Email" error="Email is required" />
```

### Alert Component
```html
<!-- Success alert -->
<x-alert variant="success">
    Data saved successfully!
</x-alert>

<!-- Error alert -->
<x-alert variant="error">
    Something went wrong!
</x-alert>
```

### Card Component
```html
<x-card title="Card Title" description="Card description">
    <!-- Card content -->
</x-card>
```

### Section Component
```html
<x-section title="Section Title" description="Section description">
    <!-- Section content -->
</x-section>
```

## Livewire Component Example

Here's how to create a Livewire component using these layouts:

### 1. Create the Component Class
```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class WaterLevelDashboard extends Component
{
    public $waterLevel = 75;
    public $status = 'Good';
    public $lastUpdated;
    public $alertsEnabled = true;
    public $showAlert = false;
    public $recentReadings = [];

    public function mount()
    {
        $this->lastUpdated = now()->format('M j, Y g:i A');
        $this->recentReadings = [
            ['time' => '2 min ago', 'level' => 75, 'status' => 'Good'],
            ['time' => '5 min ago', 'level' => 78, 'status' => 'Good'],
            ['time' => '10 min ago', 'level' => 72, 'status' => 'Good'],
        ];
    }

    public function refreshData()
    {
        // Simulate data refresh
        $this->waterLevel = rand(60, 90);
        $this->lastUpdated = now()->format('M j, Y g:i A');
        
        if ($this->waterLevel < 70) {
            $this->status = 'Low';
            $this->showAlert = true;
        } else {
            $this->status = 'Good';
            $this->showAlert = false;
        }
    }

    public function toggleAlerts()
    {
        $this->alertsEnabled = !$this->alertsEnabled;
    }

    public function render()
    {
        return view('livewire.water-level-dashboard')
            ->layout('layouts.authenticated');
    }
}
```

### 2. Use in Routes
```php
Route::get('/dashboard', WaterLevelDashboard::class)->name('dashboard');
```

## Features

- **Responsive Design**: All layouts are mobile-first and responsive
- **Dark Mode Support**: Built-in dark mode support with Tailwind CSS
- **Accessibility**: Components follow accessibility best practices
- **Consistent Styling**: Unified design system across all components
- **Livewire Integration**: Optimized for Livewire components
- **Modern UI**: Clean, modern design with smooth transitions

## Customization

### Adding New Variants
To add new button variants, edit `resources/views/components/button.blade.php`:

```php
$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500',
    'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
    'custom' => 'bg-purple-600 hover:bg-purple-700 text-white focus:ring-purple-500', // Add your custom variant
];
```

### Customizing Colors
All colors are defined using Tailwind CSS classes. You can customize them by:
1. Modifying the Tailwind config
2. Adding custom CSS classes
3. Using CSS variables for dynamic theming

## Best Practices

1. **Always use the layout system** for consistency
2. **Use semantic HTML** in your components
3. **Test responsive behavior** on different screen sizes
4. **Follow accessibility guidelines** when creating new components
5. **Use the provided component variants** instead of custom styling
6. **Keep components focused** on a single responsibility

This layout system provides a solid foundation for building modern, responsive web applications with Livewire and Tailwind CSS.
