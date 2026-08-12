#!/usr/bin/env python3
"""One-shot helper: build Laravel-accurate architecture.json for Crave Bakery."""

from __future__ import annotations

import json
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
ARCH = Path(__file__).resolve().parent
ROUTES_FILE = ARCH / "_routes_raw.json"
OUT = ARCH / "architecture.json"


def load_routes() -> list[dict]:
    raw = ROUTES_FILE.read_bytes()
    if raw[:2] in (b"\xff\xfe", b"\xfe\xff"):
        text = raw.decode("utf-16")
    else:
        text = raw.decode("utf-8-sig")
    start = text.find("[")
    if start < 0:
        raise ValueError(f"No JSON array found in {ROUTES_FILE}")
    return json.loads(text[start:])


def method_of(route: dict) -> str:
    methods = route.get("method") or route.get("methods") or "GET"
    if isinstance(methods, list):
        methods = "|".join(methods)
    # Prefer primary verb; drop HEAD/OPTIONS noise for docs
    parts = [m.strip() for m in str(methods).replace("|", " ").split() if m.strip() not in {"HEAD", "OPTIONS"}]
    return parts[0] if parts else "GET"


def path_of(route: dict) -> str:
    uri = route.get("uri") or "/"
    return uri if uri.startswith("/") else f"/{uri}"


def action_of(route: dict) -> str:
    return str(route.get("action") or route.get("controller") or "")


def middleware_of(route: dict) -> list[str]:
    mw = route.get("middleware") or []
    if isinstance(mw, str):
        return [m.strip() for m in mw.split(",") if m.strip()]
    return list(mw)


def is_auth(route: dict) -> bool:
    mw = middleware_of(route)
    joined = " ".join(mw).lower()
    return "auth" in joined or "admin" in joined or "super-admin" in joined or "super_admin" in joined


def permission_for(name: str | None, path: str) -> str | None:
    if not name:
        return None
    mapping = {
        "admin.products.": "products",
        "admin.categories.": "categories",
        "admin.attributes.": "attributes",
        "admin.orders.": "orders",
        "admin.reviews.": "reviews",
        "admin.customers.": "customers",
        "admin.promo-codes.": "promo_codes",
        "admin.settings.": "site_settings",
        "admin.users.": "admin_users",
    }
    for prefix, scope in mapping.items():
        if name.startswith(prefix) or name == prefix.rstrip("."):
            action = name.split(".")[-1]
            action_map = {
                "index": "view",
                "show": "view",
                "create": "create",
                "store": "create",
                "edit": "edit",
                "update": "edit",
                "destroy": "delete",
                "reorder": "edit",
                "refund": "refund",
                "invoice": "view",
                "pdf": "view",
                "respond": "respond",
                "invite": "invite",
                "resend": "invite",
                "revoke": "delete",
                "permissions": "edit",
                "deactivate": "deactivate",
                "payments": "edit",
                "toggle": "edit",
            }
            # payments are gateways — authorized by PaymentGatewayModelPolicy / site settings
            if "settings.payments" in name:
                return "site_settings:edit"
            return f"{scope}:{action_map.get(action, action)}"
    return None


MODULE_DEFS = [
    {
        "id": "public-catalogue",
        "name": "Public Catalogue",
        "basePath": "/",
        "description": "Homepage and public product browsing (Inertia).",
        "color": "#3D1A0E",
        "icon": "🧁",
        "match": lambda n, p, a: (n or "") in {"home", "products.index", "products.show", "test"}
        or (n or "").startswith("products.") and not (n or "").startswith("admin."),
        "files": [
            "app/Http/Controllers/HomeController.php",
            "app/Http/Controllers/ProductController.php",
            "app/Services/ProductService.php",
            "resources/js/Pages/Home.vue",
            "resources/js/Pages/Products/Index.vue",
            "resources/js/Pages/Products/Show.vue",
        ],
        "permissions": [],
    },
    {
        "id": "cart",
        "name": "Cart",
        "basePath": "/cart",
        "description": "Guest + authenticated cart operations.",
        "color": "#E8572A",
        "icon": "🛒",
        "match": lambda n, p, a: (n or "").startswith("cart.") or p.startswith("/cart"),
        "files": [
            "app/Http/Controllers/CartController.php",
            "app/Services/CartService.php",
            "resources/js/Pages/Cart/Index.vue",
        ],
        "permissions": [],
    },
    {
        "id": "checkout-orders",
        "name": "Checkout & Orders",
        "basePath": "/checkout",
        "description": "Authenticated checkout, order placement, and order history.",
        "color": "#185FA5",
        "icon": "📦",
        "match": lambda n, p, a: (n or "") in {"checkout", "checkout.payment", "checkout.confirmation", "orders.store", "orders.index", "orders.show"}
        or (n or "").startswith("orders.") and not (n or "").startswith("admin."),
        "files": [
            "app/Http/Controllers/OrderController.php",
            "app/Services/OrderService.php",
            "resources/js/Pages/Checkout/Index.vue",
            "resources/js/Pages/Orders/Index.vue",
            "resources/js/Pages/Orders/Show.vue",
        ],
        "permissions": [],
    },
    {
        "id": "payments",
        "name": "Customer Payments",
        "basePath": "/payment",
        "description": "Stripe payment intent creation and confirmation.",
        "color": "#635BFF",
        "icon": "💳",
        "match": lambda n, p, a: (n or "").startswith("payment.") or p.startswith("/payment") or p.startswith("/stripe"),
        "files": [
            "app/Http/Controllers/PaymentController.php",
            "app/Services/PaymentService.php",
            "app/Services/StripePaymentService.php",
        ],
        "permissions": [],
    },
    {
        "id": "profile-account",
        "name": "Customer Profile & Account",
        "basePath": "/profile",
        "description": "Profile, addresses, favourites, collections, and reviews.",
        "color": "#2E7D32",
        "icon": "👤",
        "match": lambda n, p, a: (n or "").startswith(("profile.", "addresses.", "favourites.", "collections.", "reviews."))
        and not (n or "").startswith("admin."),
        "files": [
            "app/Http/Controllers/ProfileController.php",
            "app/Http/Controllers/AddressController.php",
            "app/Http/Controllers/FavouriteController.php",
            "app/Http/Controllers/CollectionController.php",
            "app/Http/Controllers/ReviewController.php",
            "app/Services/AddressService.php",
            "app/Services/FavouriteService.php",
            "app/Services/CollectionService.php",
            "app/Services/ReviewService.php",
        ],
        "permissions": [],
    },
    {
        "id": "auth",
        "name": "Authentication",
        "basePath": "/",
        "description": "Laravel Breeze auth (login, register, password reset, verification).",
        "color": "#4A4641",
        "icon": "🔐",
        "match": lambda n, p, a: (n or "") in {
            "login",
            "register",
            "logout",
            "password.request",
            "password.email",
            "password.reset",
            "password.store",
            "password.confirm",
            "password.update",
            "verification.notice",
            "verification.verify",
            "verification.send",
        }
        or "Auth\\" in a
        or p in {"/login", "/register", "/logout", "/forgot-password", "/reset-password", "/verify-email", "/confirm-password", "/password"},
        "files": [
            "routes/auth.php",
            "app/Http/Controllers/Auth/",
            "resources/js/Pages/Auth/",
        ],
        "permissions": [],
    },
    {
        "id": "admin-invitations",
        "name": "Admin Invitations (Public Accept)",
        "basePath": "/admin-invitations",
        "description": "Guest acceptance of admin invitation tokens.",
        "color": "#EF9F27",
        "icon": "✉️",
        "match": lambda n, p, a: (n or "").startswith("admin-invitations.") or p.startswith("/admin-invitations"),
        "files": [
            "app/Http/Controllers/AcceptInvitationController.php",
            "app/Services/AdminInvitationService.php",
        ],
        "permissions": [],
    },
    {
        "id": "admin-dashboard",
        "name": "Admin Dashboard",
        "basePath": "/admin",
        "description": "Admin home dashboard and admin profile.",
        "color": "#3D1A0E",
        "icon": "📊",
        "match": lambda n, p, a: (n or "") in {"admin.dashboard", "admin.profile.edit", "admin.profile.update", "admin.profile.password"},
        "files": [
            "app/Http/Controllers/Admin/DashboardController.php",
            "app/Http/Controllers/Admin/ProfileController.php",
            "app/Services/AdminProfileService.php",
            "resources/js/Pages/Admin/Dashboard.vue",
        ],
        "permissions": [],
    },
    {
        "id": "admin-products",
        "name": "Admin Products",
        "basePath": "/admin/products",
        "description": "Admin product CRUD.",
        "color": "#825343",
        "icon": "🎂",
        "match": lambda n, p, a: (n or "").startswith("admin.products."),
        "files": [
            "app/Http/Controllers/Admin/ProductController.php",
            "app/Services/ProductService.php",
            "app/Policies/ProductPolicy.php",
            "resources/js/Pages/Admin/Products/",
        ],
        "permissions": ["products:view", "products:create", "products:edit", "products:delete", "products:manage_stock"],
    },
    {
        "id": "admin-categories",
        "name": "Admin Categories",
        "basePath": "/admin/categories",
        "description": "Admin category CRUD + tree reorder.",
        "color": "#663c2d",
        "icon": "📁",
        "match": lambda n, p, a: (n or "").startswith("admin.categories."),
        "files": [
            "app/Http/Controllers/Admin/CategoryController.php",
            "app/Services/CategoryService.php",
            "app/Policies/CategoryPolicy.php",
            "resources/js/Pages/Admin/Categories/",
        ],
        "permissions": ["categories:view", "categories:create", "categories:edit", "categories:delete"],
    },
    {
        "id": "admin-attributes",
        "name": "Admin Attributes",
        "basePath": "/admin/attributes",
        "description": "Product attribute definitions and values.",
        "color": "#84746f",
        "icon": "🏷️",
        "match": lambda n, p, a: (n or "").startswith("admin.attributes."),
        "files": [
            "app/Http/Controllers/Admin/AttributeController.php",
            "app/Services/AttributeService.php",
            "app/Policies/AttributePolicy.php",
            "resources/js/Pages/Admin/Attributes/",
        ],
        "permissions": ["attributes:view", "attributes:create", "attributes:edit", "attributes:delete"],
    },
    {
        "id": "admin-orders",
        "name": "Admin Orders",
        "basePath": "/admin/orders",
        "description": "Order management, refunds, and invoices.",
        "color": "#185FA5",
        "icon": "🧾",
        "match": lambda n, p, a: (n or "").startswith("admin.orders."),
        "files": [
            "app/Http/Controllers/Admin/OrderController.php",
            "app/Services/OrderService.php",
            "app/Policies/OrderPolicy.php",
            "resources/js/Pages/Admin/Orders/",
        ],
        "permissions": ["orders:view", "orders:update_status", "orders:refund"],
    },
    {
        "id": "admin-reviews",
        "name": "Admin Reviews",
        "basePath": "/admin/reviews",
        "description": "Review moderation and admin responses.",
        "color": "#EF9F27",
        "icon": "⭐",
        "match": lambda n, p, a: (n or "").startswith("admin.reviews."),
        "files": [
            "app/Http/Controllers/Admin/ReviewController.php",
            "app/Services/ReviewService.php",
            "app/Policies/ReviewPolicy.php",
            "resources/js/Pages/Admin/Reviews/",
        ],
        "permissions": ["reviews:view", "reviews:approve", "reviews:delete", "reviews:respond"],
    },
    {
        "id": "admin-customers",
        "name": "Admin Customers",
        "basePath": "/admin/customers",
        "description": "Customer list and detail (read-focused).",
        "color": "#2E7D32",
        "icon": "👥",
        "match": lambda n, p, a: (n or "").startswith("admin.customers."),
        "files": [
            "app/Http/Controllers/Admin/CustomerController.php",
            "app/Services/CustomerService.php",
            "app/Policies/CustomerPolicy.php",
            "resources/js/Pages/Admin/Customers/",
        ],
        "permissions": ["customers:view", "customers:edit", "customers:export"],
    },
    {
        "id": "admin-promo-codes",
        "name": "Admin Promo Codes",
        "basePath": "/admin/promo-codes",
        "description": "Admin promo code CRUD, active toggle, and usage tracking.",
        "color": "#E8572A",
        "icon": "🏷️",
        "match": lambda n, p, a: (n or "").startswith("admin.promo-codes."),
        "files": [
            "app/Http/Controllers/Admin/PromoCodeController.php",
            "app/Services/PromoCodeService.php",
            "app/Policies/PromoCodePolicy.php",
            "resources/js/Pages/Admin/PromoCodes/",
        ],
        "permissions": [
            "promo_codes:view",
            "promo_codes:create",
            "promo_codes:edit",
            "promo_codes:delete",
        ],
    },
    {
        "id": "admin-settings",
        "name": "Admin Site Settings & Payments",
        "basePath": "/admin/settings",
        "description": "Site settings and payment gateway configuration (super_admin scopes).",
        "color": "#C62828",
        "icon": "⚙️",
        "match": lambda n, p, a: (n or "").startswith("admin.settings."),
        "files": [
            "app/Http/Controllers/Admin/SettingController.php",
            "app/Http/Controllers/Admin/PaymentGatewayController.php",
            "app/Services/SiteSettingService.php",
            "app/Policies/SiteSettingPolicy.php",
            "app/Policies/PaymentGatewayModelPolicy.php",
            "resources/js/Pages/Admin/Settings/",
        ],
        "permissions": ["site_settings:view", "site_settings:edit"],
    },
    {
        "id": "admin-users",
        "name": "Admin Users (Super Admin)",
        "basePath": "/admin/users",
        "description": "Invite/manage admin users and permissions matrix.",
        "color": "#1A1A1A",
        "icon": "🛡️",
        "match": lambda n, p, a: (n or "").startswith("admin.users."),
        "files": [
            "app/Http/Controllers/Admin/UserController.php",
            "app/Services/AdminUserService.php",
            "app/Services/AdminInvitationService.php",
            "app/Policies/AdminUserPolicy.php",
            "resources/js/Pages/Admin/Users/",
        ],
        "permissions": [
            "admin_users:view",
            "admin_users:invite",
            "admin_users:edit",
            "admin_users:deactivate",
            "admin_users:delete",
        ],
    },
]


PERMISSION_CATALOG = [
    "products:view",
    "products:create",
    "products:edit",
    "products:delete",
    "products:manage_stock",
    "categories:view",
    "categories:create",
    "categories:edit",
    "categories:delete",
    "attributes:view",
    "attributes:create",
    "attributes:edit",
    "attributes:delete",
    "orders:view",
    "orders:update_status",
    "orders:refund",
    "reviews:view",
    "reviews:approve",
    "reviews:delete",
    "reviews:respond",
    "customers:view",
    "customers:edit",
    "customers:export",
    "promo_codes:view",
    "promo_codes:create",
    "promo_codes:edit",
    "promo_codes:delete",
    "site_settings:view",
    "site_settings:edit",
    "admin_users:view",
    "admin_users:invite",
    "admin_users:edit",
    "admin_users:deactivate",
    "admin_users:delete",
]


def build() -> dict:
    routes = load_routes()
    assigned: set[int] = set()
    modules = []

    for mod in MODULE_DEFS:
        endpoints = []
        for idx, route in enumerate(routes):
            if idx in assigned:
                continue
            name = route.get("name")
            path = path_of(route)
            action = action_of(route)
            if mod["match"](name, path, action):
                assigned.add(idx)
                endpoints.append(
                    {
                        "method": method_of(route),
                        "path": path,
                        "auth": is_auth(route),
                        "permission": permission_for(name, path),
                        "description": f"{name or 'unnamed'} → {action}",
                        "name": name,
                    }
                )
        modules.append(
            {
                "id": mod["id"],
                "name": mod["name"],
                "basePath": mod["basePath"],
                "description": mod["description"],
                "color": mod["color"],
                "icon": mod["icon"],
                "files": mod["files"],
                "permissions": mod["permissions"],
                "endpoints": endpoints,
            }
        )

    leftover = []
    for idx, route in enumerate(routes):
        if idx in assigned:
            continue
        path = path_of(route)
        # framework internals
        if path in {"/up", "/_ignition", "/storage/{path}"} or path.startswith("/_ignition") or path.startswith("/sanctum"):
            leftover.append(route)
            continue
        leftover.append(route)

    system_endpoints = [
        {
            "method": "GET",
            "path": "/up",
            "auth": False,
            "description": "Laravel health check (bootstrap/app.php withRouting health).",
        }
    ]
    for route in leftover:
        path = path_of(route)
        if path == "/up":
            continue
        system_endpoints.append(
            {
                "method": method_of(route),
                "path": path,
                "auth": is_auth(route),
                "description": f"{route.get('name') or 'system'} → {action_of(route)}",
            }
        )

    permission_details = []
    for slug in PERMISSION_CATALOG:
        scope, action = slug.split(":", 1)
        endpoints = []
        for mod in modules:
            for ep in mod["endpoints"]:
                if ep.get("permission") == slug:
                    endpoints.append({"method": ep["method"], "path": ep["path"]})
        admin_pages = []
        page_map = {
            "products": ["Admin Products"],
            "categories": ["Admin Categories"],
            "attributes": ["Admin Attributes"],
            "orders": ["Admin Orders"],
            "reviews": ["Admin Reviews"],
            "customers": ["Admin Customers"],
            "promo_codes": ["Admin Promo Codes"],
            "site_settings": ["Admin Settings", "Admin Payment Gateways"],
            "admin_users": ["Admin Users", "Admin Permissions"],
        }
        admin_pages = page_map.get(scope, [])
        permission_details.append(
            {
                "slug": slug,
                "module": scope,
                "action": action.upper(),
                "endpoints": endpoints,
                "adminPages": admin_pages,
            }
        )

    total_eps = sum(len(m["endpoints"]) for m in modules) + len([e for e in system_endpoints if e["path"] != "/up"]) + 1

    return {
        "meta": {
            "displayName": "Crave Bakery",
            "version": "1.0.0",
            "description": "Crave Bakery — Laravel 13 + Inertia Vue bakery e-commerce architecture map",
            "generatedAt": "2026-07-30",
            "techStack": {
                "language": "PHP 8.3+",
                "framework": "Laravel 13 + Inertia.js v2 + Vue 3",
                "database": "MySQL / MariaDB",
                "auth": "Laravel session auth (Breeze) + admin/super-admin middleware",
                "payments": "Laravel Cashier (Stripe)",
                "media": "Spatie MediaLibrary",
                "frontend": "Vite 8 + Tailwind CSS + Headless UI + Tabler Icons",
            },
        },
        "workspaces": [
            {
                "id": "laravel-app",
                "name": "pastaci-website",
                "type": "backend",
                "description": "Monolith: Laravel HTTP + Inertia SSR bridge + Vue pages",
                "port": 8000,
                "entrypoint": "public/index.php",
            },
            {
                "id": "vue-frontend",
                "name": "resources/js",
                "type": "frontend",
                "description": "Inertia Vue 3 pages, components, layouts, composables",
                "port": 5173,
                "entrypoint": "resources/js/app.js",
            },
        ],
        "infrastructure": [
            {
                "id": "mysql",
                "name": "MySQL / MariaDB",
                "type": "database",
                "image": "local / hosted MySQL",
                "port": 3306,
                "description": "Primary application database (Eloquent models).",
                "features": ["Eloquent ORM", "migrations", "soft deletes"],
            },
            {
                "id": "stripe",
                "name": "Stripe (Cashier)",
                "type": "monitoring",
                "image": "stripe.com API",
                "port": 443,
                "description": "Payment processing via Laravel Cashier / StripePaymentService.",
                "features": ["PaymentIntents", "webhooks (CSRF excepted stripe/*)"],
            },
        ],
        "dockerDiagram": {
            "description": "No docker-compose.yml in this project. Local/dev uses php artisan serve + Vite.",
            "nodes": [],
            "edges": [],
        },
        "systemArchitectureDiagram": {
            "description": "Crave Bakery layered architecture: browser → Laravel/Inertia → services → MySQL/Stripe.",
            "subgraphs": [
                {
                    "id": "client_layer",
                    "label": "Client Layer",
                    "nodes": [
                        {"id": "browser", "label": "Browser (Vue 3 + Inertia)", "type": "app"},
                    ],
                },
                {
                    "id": "app_layer",
                    "label": "Application Layer",
                    "nodes": [
                        {"id": "laravel", "label": "Laravel 13 HTTP Kernel", "type": "app"},
                        {"id": "inertia", "label": "Inertia Middleware", "type": "app"},
                        {"id": "controllers", "label": "Controllers + FormRequests", "type": "app"},
                        {"id": "services", "label": "Domain Services", "type": "app"},
                        {"id": "policies", "label": "Policies + Permissions", "type": "app"},
                    ],
                },
                {
                    "id": "data_layer",
                    "label": "Data & Integrations",
                    "nodes": [
                        {"id": "mysql", "label": "MySQL Database", "type": "database"},
                        {"id": "media", "label": "Media Disk (Spatie)", "type": "cache"},
                        {"id": "stripe", "label": "Stripe API", "type": "queue"},
                    ],
                },
            ],
            "edges": [
                {"from": "browser", "to": "laravel", "label": "HTTPS / XHR Inertia"},
                {"from": "laravel", "to": "inertia", "label": "web middleware"},
                {"from": "inertia", "to": "controllers", "label": "route match"},
                {"from": "controllers", "to": "policies", "label": "authorize"},
                {"from": "controllers", "to": "services", "label": "validated data"},
                {"from": "services", "to": "mysql", "label": "Eloquent"},
                {"from": "services", "to": "media", "label": "uploads"},
                {"from": "services", "to": "stripe", "label": "PaymentIntent"},
            ],
        },
        "swaggerSchemas": {
            "matchStatus": f"Inertia Web App — documented {total_eps} routes (no OpenAPI server)",
            "openapi": "3.0.0",
            "servedAt": "n/a (Inertia pages, not a public REST API)",
            "securityScheme": "session cookie (web) + CSRF; admin / super-admin middleware",
            "servers": [
                {"url": "http://localhost:8000", "description": "Local php artisan serve"},
                {"url": "http://127.0.0.1:8000", "description": "Local alternate"},
            ],
            "schemas": [
                {"name": "Product", "description": "Bakery product with pricing, stock, media, categories"},
                {"name": "Order", "description": "Customer order with items, payment and delivery status"},
                {"name": "User", "description": "Customer user (role=user)"},
                {"name": "AdminUser", "description": "Admin account with JSON permissions matrix"},
            ],
        },
        "modules": modules,
        "systemEndpoints": system_endpoints,
        "coreLayer": {
            "security": [
                {
                    "name": "AdminMiddleware",
                    "file": "app/Http/Middleware/AdminMiddleware.php",
                    "description": "Ensures authenticated user is admin or super_admin.",
                },
                {
                    "name": "SuperAdminMiddleware",
                    "file": "app/Http/Middleware/SuperAdminMiddleware.php",
                    "description": "Restricts routes to super_admin only.",
                },
                {
                    "name": "Policies + hasPermission",
                    "file": "config/permissions.php",
                    "description": "RBAC scopes/actions; policies call User::hasPermission().",
                },
            ],
            "middleware": [
                {
                    "name": "HandleInertiaRequests",
                    "file": "app/Http/Middleware/HandleInertiaRequests.php",
                    "description": "Shares auth, cart count, flash, and site settings with Vue.",
                    "guards": ["web"],
                },
                {
                    "name": "admin alias",
                    "file": "bootstrap/app.php",
                    "description": "Middleware alias admin → AdminMiddleware",
                    "guards": ["admin"],
                },
                {
                    "name": "super-admin alias",
                    "file": "bootstrap/app.php",
                    "description": "Middleware alias super-admin → SuperAdminMiddleware",
                    "guards": ["super-admin"],
                },
            ],
            "services": [
                {"name": "CartService", "file": "app/Services/CartService.php", "description": "Guest/auth cart operations", "exports": ["getCount", "add", "update", "remove"]},
                {"name": "OrderService", "file": "app/Services/OrderService.php", "description": "Checkout and order lifecycle", "exports": ["create", "updateStatus"]},
                {"name": "PaymentService", "file": "app/Services/PaymentService.php", "description": "Payment orchestration", "exports": ["createIntent", "confirm"]},
                {"name": "StripePaymentService", "file": "app/Services/StripePaymentService.php", "description": "Stripe Cashier integration", "exports": ["createIntent", "confirm"]},
                {"name": "ProductService", "file": "app/Services/ProductService.php", "description": "Product catalogue & admin mutations", "exports": ["*"]},
                {"name": "CategoryService", "file": "app/Services/CategoryService.php", "description": "Category tree, reorder, stats", "exports": ["tree", "reorder", "stats"]},
                {"name": "AttributeService", "file": "app/Services/AttributeService.php", "description": "Attributes & values", "exports": ["*"]},
                {"name": "ReviewService", "file": "app/Services/ReviewService.php", "description": "Customer reviews & moderation", "exports": ["*"]},
                {"name": "CustomerService", "file": "app/Services/CustomerService.php", "description": "Admin customer views", "exports": ["*"]},
                {"name": "SiteSettingService", "file": "app/Services/SiteSettingService.php", "description": "Key/value site settings helper", "exports": ["get", "set"]},
                {"name": "AdminUserService", "file": "app/Services/AdminUserService.php", "description": "Admin user lifecycle", "exports": ["*"]},
                {"name": "AdminInvitationService", "file": "app/Services/AdminInvitationService.php", "description": "Invite tokens & acceptance", "exports": ["*"]},
                {"name": "AdminProfileService", "file": "app/Services/AdminProfileService.php", "description": "Admin profile/password updates", "exports": ["*"]},
                {"name": "FavouriteService", "file": "app/Services/FavouriteService.php", "description": "Favourites toggle/clear", "exports": ["*"]},
                {"name": "CollectionService", "file": "app/Services/CollectionService.php", "description": "Favourite collections", "exports": ["*"]},
                {"name": "AddressService", "file": "app/Services/AddressService.php", "description": "Customer addresses", "exports": ["*"]},
                {"name": "PromoCodeService", "file": "app/Services/PromoCodeService.php", "description": "Promo validation at checkout + admin stats/CRUD/toggle", "exports": ["*"]},
            ],
        },
        "dataFlow": {
            "requestPipeline": [
                {
                    "step": "HTTP request hits public/index.php (PHP built-in / Apache / Nginx)",
                    "detail": "Laravel Application::configure bootstrap in bootstrap/app.php.",
                },
                {
                    "step": "web middleware group runs (session, cookies, CSRF)",
                    "detail": "CSRF excepted for stripe/* webhook paths.",
                    "coreRef": "middleware",
                },
                {
                    "step": "HandleInertiaRequests shares auth/cart/flash/site props",
                    "detail": "Makes $page.props available to all Vue pages.",
                    "coreRef": "middleware",
                },
                {
                    "step": "Route matched from web.php / auth.php / admin.php",
                    "detail": "admin.php loaded via withRouting(then:). Named routes via Ziggy on frontend.",
                },
                {
                    "step": "auth / admin / super-admin middleware gates access",
                    "detail": "AdminMiddleware checks role; SuperAdminMiddleware restricts user management.",
                    "coreRef": "security",
                },
                {
                    "step": "FormRequest validates input; Policy authorizes action",
                    "detail": "Controllers use $request->validated() and authorizeResource / authorize().",
                    "coreRef": "security",
                },
                {
                    "step": "Controller delegates to Service; Eloquent persists",
                    "detail": "Thin controllers — business logic lives in app/Services.",
                    "coreRef": "services",
                },
                {
                    "step": "Inertia::render or redirect()->route response",
                    "detail": "Vue page receives API Resources — never raw Eloquent models.",
                },
            ],
            "errorPipeline": [
                "ValidationException → 422 / Inertia error bag (form.errors)",
                "AuthorizationException → 403",
                "ModelNotFoundException → 404",
                "Unhandled exception → Laravel exception handler (JSON when request is api/*)",
            ],
            "tenantIsolation": "Customer data scoped by auth user_id; admin data gated by role + JSON permissions matrix. Custom route bindings: customer, adminUser.",
        },
        "permissions": {
            "description": "RBAC from config/permissions.php. super_admin bypasses checks. site_settings and admin_users are super_admin_only scopes.",
            "catalog": PERMISSION_CATALOG,
            "details": permission_details,
        },
        "sqlQueries": [
            {
                "id": "products-listing",
                "label": "Public product listing with filters",
                "module": "Public Catalogue",
                "file": "app/Services/ProductService.php",
                "function": "ProductService (index/search scopes)",
                "tables": ["products", "category_product", "categories", "media"],
                "purpose": "Paginated active products for /products with category/price/stock filters.",
                "sql": "SELECT products.* FROM products WHERE status = 'active' AND deleted_at IS NULL /* + dynamic filters */",
                "endpoints": [{"method": "GET", "path": "/products"}],
            },
            {
                "id": "category-tree",
                "label": "Admin category tree",
                "module": "Admin Categories",
                "file": "app/Services/CategoryService.php",
                "function": "CategoryService::tree()",
                "tables": ["categories"],
                "purpose": "Build nested category tree for admin Index view with optional search/status.",
                "sql": "SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY sort_order, name",
                "endpoints": [{"method": "GET", "path": "/admin/categories"}],
            },
            {
                "id": "cart-resolve",
                "label": "Resolve guest or user cart",
                "module": "Cart",
                "file": "app/Services/CartService.php",
                "function": "CartService",
                "tables": ["carts", "cart_items", "products"],
                "purpose": "Load cart by user_id or session_id and compute count for Inertia shared props.",
                "sql": "SELECT * FROM carts WHERE user_id = ? OR session_id = ? LIMIT 1",
                "endpoints": [{"method": "GET", "path": "/cart"}],
            },
            {
                "id": "order-create",
                "label": "Create order from cart",
                "module": "Checkout & Orders",
                "file": "app/Services/OrderService.php",
                "function": "OrderService",
                "tables": ["orders", "order_items", "carts", "cart_items", "products"],
                "purpose": "Transactional checkout: snapshot line items, totals, delivery, payment status.",
                "sql": "INSERT INTO orders (...) VALUES (...); INSERT INTO order_items (...); /* inside DB::transaction */",
                "endpoints": [{"method": "POST", "path": "/orders"}],
            },
            {
                "id": "admin-permissions",
                "label": "Load admin permissions matrix",
                "module": "Admin Users (Super Admin)",
                "file": "app/Services/AdminUserService.php",
                "function": "AdminUserService",
                "tables": ["users"],
                "purpose": "Read/update users.permissions JSON for admin role accounts.",
                "sql": "SELECT id, name, email, role, permissions FROM users WHERE role IN ('admin','super_admin')",
                "endpoints": [{"method": "GET", "path": "/admin/users"}],
            },
        ],
        "_meta": {
            "routeCount": len(routes),
            "assignedEndpoints": sum(len(m["endpoints"]) for m in modules),
            "unassignedRoutes": len(leftover),
            "generator": "docs/architecture/_build_manifest.py",
            "note": "Upstream arch-wiki scanner mis-detects FastAPI on Laravel; this manifest is Laravel-adapted. Re-run _build_manifest.py after major route changes, then python build_html.py.",
        },
    }


if __name__ == "__main__":
    data = build()
    OUT.write_text(json.dumps(data, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(f"Wrote {OUT}")
    print(f"modules={len(data['modules'])} endpoints={sum(len(m['endpoints']) for m in data['modules'])} routes={data['_meta']['routeCount']}")
