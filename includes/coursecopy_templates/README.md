# Course Copy Templates

This directory contains modular templates for the course copy functionality, breaking down the monolithic `coursecopylist.php` file into maintainable components.

## File Structure

### Main Files

- **`coursecopylist_refactored.php`** - The refactored main file that uses templates
- **`utilities.php`** - Common utility functions used across templates

### Template Files

- **`this_course.php`** - Template for the "This Course" option
- **`my_courses.php`** - Template for "My Courses" section
- **`my_group_courses.php`** - Template for "My Group's Courses" section
- **`others_courses.php`** - Template for "Other's Courses" section
- **`template_courses.php`** - Template for system and group template courses
- **`course_lookup.php`** - Template for course ID lookup functionality
- **`main_course_list.php`** - Main structure template that includes all sections
- **`load_others.php`** - Template for loading other groups via AJAX
- **`load_other_group.php`** - Template for loading a specific group's courses

## Usage

### To use the refactored version:

1. Replace the original `coursecopylist.php` with `coursecopylist_refactored.php`
2. Or update your existing file to include the templates

### To include individual templates:

```php
// Define the constant to allow template inclusion
define('INCLUDED_FROM_COURSECOPY', true);

// Include utility functions
require_once(__DIR__ . '/coursecopy_templates/utilities.php');

// Include specific templates as needed
include_once(__DIR__ . '/coursecopy_templates/my_courses.php');
```

## Benefits

1. **Maintainability** - Each section is now in its own file, making it easier to modify specific functionality
2. **Reusability** - Templates can be included in other parts of the system
3. **Readability** - Code is organized by functionality rather than mixed together
4. **Testing** - Individual templates can be tested in isolation
5. **Collaboration** - Multiple developers can work on different templates simultaneously

## Security

Each template file includes a security check to prevent direct access:

```php
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}
```

## Dependencies

All templates depend on:

- The `$DBH` database connection
- The `Sanitize` class for input sanitization
- Various global variables like `$userid`, `$groupid`, `$cid`
- The utility functions defined in `utilities.php`

## Migration Notes

When migrating from the original file:

1. Ensure all required variables are available in the scope where templates are included
2. The `$lastteacher` variable is used in multiple templates and should be properly initialized
3. All database queries remain the same, only the HTML output has been modularized
