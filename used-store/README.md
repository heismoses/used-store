# UsedStore Marketplace

A complete **Used Items Marketplace** web application similar to Facebook Marketplace, built with HTML, CSS, JavaScript, PHP, and MySQL. Suitable as a university final-year web development project.

## Features

- **User Authentication** — Registration, login, logout with PHP sessions; user and admin roles
- **User Dashboard** — Profile management, item management, messages, activity history
- **Item Posting** — Multi-image upload, categories, pricing, location, condition
- **Marketplace** — Responsive card-based browsing (Facebook Marketplace style)
- **Search & Filters** — Name, category, price range, location, sort options
- **Messaging** — Buyer–seller chat stored in database
- **Admin Panel** — User management, product approval, categories, reports
- **Extras** — Favorites/wishlist, view counter, dark mode, notifications, mobile menu

## Tech Stack

| Layer      | Technology        |
|-----------|-------------------|
| Frontend  | HTML5, CSS3, JavaScript |
| Backend   | PHP 7.4+          |
| Database  | MySQL 5.7+        |
| Server    | Apache (WampServer) |

## Quick Start (WampServer)

1. Install [WampServer](https://www.wampserver.com/)
2. Copy this folder to `C:\wamp64\www\used-store-marketplace`
3. Start WampServer (green icon)
4. Open phpMyAdmin → Import `database/schema.sql`
5. Edit `config/database.php` if needed (default: root, no password)
6. Visit `http://localhost/used-store-marketplace`

See [docs/INSTALLATION.md](docs/INSTALLATION.md) for detailed steps.

## Demo Accounts

| Role  | Email                 | Password  |
|-------|-----------------------|-----------|
| Admin | admin@usedstore.com   | password  |
| User  | john@example.com      | password  |
| User  | jane@example.com      | password  |

## Project Structure

```
used-store-marketplace/
├── admin/              # Admin panel pages
├── api/                # AJAX API endpoints
├── assets/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Default images
├── auth/               # Login, register, logout
├── config/             # Database configuration
├── dashboard/          # User dashboard
├── database/           # SQL schema file
├── docs/               # Project documentation
├── includes/           # Shared PHP (header, footer, functions)
├── pages/              # Marketplace, items, messages
├── uploads/            # User-uploaded files
│   ├── products/
│   └── profiles/
├── index.php           # Homepage
└── .htaccess           # Apache security rules
```

## Documentation

- [Installation Guide](docs/INSTALLATION.md)
- [Full Project Documentation](docs/PROJECT_DOCUMENTATION.md)

## License

Educational project — free to use for learning and academic purposes.
