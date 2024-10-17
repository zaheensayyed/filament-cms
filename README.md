**Filament CMS: Accelerate CMS Development with Laravel and Filament**

Filament CMS is a powerful package designed to streamline content management system (CMS) development using Laravel and Filament. It offers a fast and flexible solution for building dynamic, user-friendly interfaces for managing content, empowering developers to create and deploy CMS applications with ease.

Key features include:

*   **Page Management**: Effortlessly create, edit, and organize pages, with the ability to easily attach them to multilevel menus.
    
*   **Multilevel Menus**: Design and manage hierarchical menus that enhance navigation and improve user experience.
    
*   **Gallery Integration**: Incorporate galleries seamlessly into your CMS, enabling rich media management with minimal effort.
    

Filament CMS is your go-to package for building robust, scalable, and feature-rich CMS applications using Laravel and Filament.

**Installation**
----------------

`composer require zaheensayyed/filament-cms`

After installation, you may need to publish the configuration file:

`php artisan vendor:publish --tag=filament-cms-config`

Usage
-----

### Menu Example

To retrieve and display a menu, use the following method:

`FilamentCms::getMenu('Main menu');`

This method returns the specified menu (e.g., "Main menu") with its hierarchical structure, allowing you to use it in your views or layouts.

### Create and Attach Pages to Menus

Once a page is created through the CMS interface, it can be attached to a multilevel menu for easy navigation across your website or application.

### Gallery Feature

Integrate galleries effortlessly by creating image galleries in the admin panel and attaching them to pages or posts, enhancing the visual richness of your CMS.

Configuration
-------------

You can configure various aspects of Filament CMS by modifying the published configuration file.

License
-------

Filament CMS is open-sourced software licensed under the [MIT license](LICENSE.md).