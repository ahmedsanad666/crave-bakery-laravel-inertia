<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Permission Scopes
    |--------------------------------------------------------------------------
    |
    | Single source of truth for all admin permissions.
    | Stored on users.permissions as JSON: scope => action => true/false
    |
    | super_admin bypasses all checks in User::hasPermission().
    | Scopes marked super_admin_only cannot be granted to regular admins.
    |
    */

    'scopes' => [

        'products' => [
            'label' => 'Products',
            'super_admin_only' => false,
            'actions' => [
                'view'         => 'View products',
                'create'       => 'Create products',
                'edit'         => 'Edit products',
                'delete'       => 'Delete products',
                'manage_stock' => 'Manage stock',
            ],
        ],

        'categories' => [
            'label' => 'Categories',
            'super_admin_only' => false,
            'actions' => [
                'view'   => 'View categories',
                'create' => 'Create categories',
                'edit'   => 'Edit categories',
                'delete' => 'Delete categories',
            ],
        ],

        'attributes' => [
            'label' => 'Attributes',
            'super_admin_only' => false,
            'actions' => [
                'view'   => 'View attributes',
                'create' => 'Create attributes',
                'edit'   => 'Edit attributes',
                'delete' => 'Delete attributes',
            ],
        ],

        'orders' => [
            'label' => 'Orders',
            'super_admin_only' => false,
            'actions' => [
                'view'          => 'View orders',
                'update_status' => 'Update order status',
                'refund'        => 'Process refunds',
            ],
        ],

        'reviews' => [
            'label' => 'Reviews',
            'super_admin_only' => false,
            'actions' => [
                'view'    => 'View reviews',
                'approve' => 'Approve reviews',
                'delete'  => 'Delete reviews',
                'respond' => 'Respond to reviews',
            ],
        ],

        'customers' => [
            'label' => 'Customers',
            'super_admin_only' => false,
            'actions' => [
                'view'   => 'View customers',
                'edit'   => 'Edit customers',
                'export' => 'Export customers',
            ],
        ],

        'promo_codes' => [
            'label' => 'Promo Codes',
            'super_admin_only' => false,
            'actions' => [
                'view'   => 'View promo codes',
                'create' => 'Create promo codes',
                'edit'   => 'Edit promo codes',
                'delete' => 'Delete promo codes',
            ],
        ],

        'site_settings' => [
            'label' => 'Site Settings',
            'super_admin_only' => true,
            'actions' => [
                'view' => 'View site settings',
                'edit' => 'Edit site settings',
            ],
        ],

        'admin_users' => [
            'label' => 'Admin Users',
            'super_admin_only' => true,
            'actions' => [
                'view'       => 'View admin users',
                'invite'     => 'Invite admins',
                'edit'       => 'Edit admin permissions',
                'deactivate' => 'Deactivate admins',
                'delete'     => 'Remove admins',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Templates
    |--------------------------------------------------------------------------
    |
    | Pre-built matrices for seeding and the invite-admin flow.
    | Use AdminPermissions helper to build these at runtime.
    |
    */

    'templates' => [

        // Store manager — full access except super_admin-only scopes
        'full_admin' => [
            'products'    => ['view', 'create', 'edit', 'delete', 'manage_stock'],
            'categories'  => ['view', 'create', 'edit', 'delete'],
            'attributes'  => ['view', 'create', 'edit', 'delete'],
            'orders'      => ['view', 'update_status', 'refund'],
            'reviews'     => ['view', 'approve', 'delete', 'respond'],
            'customers'   => ['view', 'edit', 'export'],
            'promo_codes' => ['view', 'create', 'edit', 'delete'],
        ],

        // Read-only admin — view only, no mutations
        'read_only' => [
            'products'    => ['view'],
            'categories'  => ['view'],
            'attributes'  => ['view'],
            'orders'      => ['view'],
            'reviews'     => ['view'],
            'customers'   => ['view'],
            'promo_codes' => ['view'],
        ],

        // Catalogue manager — products + categories only
        'catalogue_manager' => [
            'products'   => ['view', 'create', 'edit', 'delete', 'manage_stock'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'attributes' => ['view', 'create', 'edit', 'delete'],
        ],

        // Orders & support — orders, reviews, customers
        'orders_support' => [
            'orders'    => ['view', 'update_status', 'refund'],
            'reviews'   => ['view', 'approve', 'delete', 'respond'],
            'customers' => ['view', 'edit', 'export'],
        ],

    ],

];