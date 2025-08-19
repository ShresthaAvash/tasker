<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    'title' => 'Tasker',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    */

    'logo' => '<b>Tasker</b>',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Tasker Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    */

    'classes_body' => 'sidebar-light-primary elevation-4',
    'classes_brand' => 'navbar-light',
    'classes_brand_text' => '',
    'classes_content_wrapper' => 'sidebar-light-primary elevation-4',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-light-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'use_route_url' => true,
    'dashboard_url' => 'landing',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    */

    'menu' =>
    [
        // User menu items located in the top-right navbar.
        [
            'text'        => 'Profile',
            'route'       => 'profile.edit',
            'icon'        => 'fas fa-fw fa-user',
            'topnav_user' => true,
        ],
        [
            'text'         => 'Activity Log',
            'route'        => 'profile.activity_log',
            'icon'         => 'fas fa-fw fa-list',
            'topnav_user'  => true,
        ],

        // --- Sidebar Menu Items ---
        ['header' => 'MAIN NAVIGATION'],

        // SUPER ADMIN MENU
        // SUPER ADMIN MENU
        [
            'text' => 'Dashboard',
            'route'  => 'superadmin.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can'  => 'is-superadmin',
        ],
        [
            'text'    => 'Organizations',
            'icon'    => 'fas fa-fw fa-building',
            'can'     => 'is-superadmin',
            'submenu' => [
                [ 'text' => 'List Organizations', 'route'  => 'superadmin.organizations.index', 'icon' => 'fas fa-fw fa-list', ],
                [ 'text' => 'Add Organization', 'route'  => 'superadmin.organizations.create', 'icon' => 'fas fa-fw fa-plus', ],
            ],
        ],
        [
            'text'    => 'Subscriptions',
            'icon'    => 'fas fa-fw fa-tags',
            'can'     => 'is-superadmin',
            'submenu' => [
                [
                    'text' => 'Activation Requests',
                    'route'  => 'superadmin.subscriptions.requests',
                    'icon' => 'fas fa-fw fa-hourglass-start',
                    'id' => 'subscription-requests-link'
                ],
                [
                    // --- THIS IS THE FIX ---
                    'text' => 'List Subscription Plans',
                    'route'  => 'superadmin.plans.index', // Use the new 'plans' route
                    'icon' => 'fas fa-fw fa-list',
                ],
                [
                    // --- THIS IS THE FIX ---
                    'text' => 'Add Subscription Plan',
                    'route'  => 'superadmin.plans.create', // Use the new 'plans' route
                    'icon' => 'fas fa-fw fa-plus',
                ],
            ],
        ],
        // ORGANIZATION MENU
        [
            'text' => 'Dashboard',
            'route'  => 'organization.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can'  => 'is-organization',
        ],
        [
            'text'  => 'Calendar',
            'route' => 'organization.calendar',
            'icon'  => 'fas fa-fw fa-calendar-alt',
            'can'   => 'is-organization',
        ],
        [
            'text'    => 'Clients',
            'icon'    => 'fas fa-fw fa-users',
            'can'     => 'is-organization',
            'submenu' => [
                [ 'text' => 'List Clients', 'route'  => 'clients.index', 'icon' => 'fas fa-fw fa-list', ],
                [ 'text' => 'Add Client', 'route'  => 'clients.create', 'icon' => 'fas fa-fw fa-plus', ],
            ],
        ],
        [
            'text'    => 'Staff',
            'icon'    => 'fas fa-fw fa-user-tie',
            'can'     => 'is-organization',
            'submenu' => [
                [ 'text' => 'List Staff', 'route'  => 'staff.index', 'icon' => 'fas fa-fw fa-users-cog', ],
                [ 'text' => 'Add Staff', 'route'  => 'staff.create', 'icon' => 'fas fa-fw fa-plus', ],
                [ 'text' => 'Staff Designations', 'route'  => 'staff-designations.index', 'icon' => 'fas fa-fw fa-id-badge', ],
            ],
        ],
        [
            'text'    => 'Services',
            'icon'    => 'fas fa-fw fa-concierge-bell',
            'can'     => 'is-organization',
            'submenu' => [
                [ 'text' => 'List Services', 'route'  => 'services.index', 'icon' => 'fas fa-fw fa-list', ],
                [ 'text' => 'Add Service', 'route'  => 'services.create', 'icon' => 'fas fa-fw fa-plus', ],
            ],
        ],

         // STAFF MENU
        [
            'text' => 'Dashboard',
            'route'  => 'staff.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can'  => 'is-staff',
            'active' => ['staff/dashboard*', 'notifications*']
        ],
        [
            'text'  => 'Calendar',
            'route' => 'organization.calendar',
            'icon'  => 'fas fa-fw fa-calendar-alt',
            'can'   => 'is-staff',
            'active' => ['staff/calendar*']
        ],
        [
            'text'    => 'Tasks',
            'icon'    => 'fas fa-fw fa-tasks',
            'can'     => 'is-staff',
            'submenu' => [
                [
                    'text'   => 'Task List',
                    'route'  => 'staff.tasks.index',
                    'icon'   => 'fas fa-fw fa-list-alt',
                ],
            ],
            'active' => ['staff/tasks*']
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    */

    'plugins' => [
        'Datatables' => ['active' => false,'files' => [['type' => 'js','asset' => false,'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',],['type' => 'js','asset' => false,'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',],['type' => 'css','asset' => false,'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',],],],
        'Select2' => ['active' => false,'files' => [['type' => 'js','asset' => false,'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',],['type' => 'css','asset' => false,'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',],],],
        'Chartjs' => ['active' => false,'files' => [['type' => 'js','asset' => false,'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',],],],
        'Sweetalert2' => ['active' => false,'files' => [['type' => 'js','asset' => false,'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',],],],
        'Pace' => ['active' => false,'files' => [['type' => 'css','asset' => false,'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',],['type' => 'js','asset' => false,'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',],],],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */

    'iframe' => [
        'default_tab' => ['url' => null,'title' => null,],
        'buttons' => ['close' => true,'close_all' => true,'close_all_other' => true,'scroll_left' => true,'scroll_right' => true,'fullscreen' => true,],
        'options' => ['loading_screen' => 1000,'auto_show_new_tab' => true,'use_navbar_items' => true,],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */

    'livewire' => false
];