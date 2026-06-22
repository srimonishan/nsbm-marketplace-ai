# GreenLink Market

> A premium AI-powered e-commerce marketplace built exclusively for NSBM Green University students and staff.

## Overview

GreenLink Market is a full-stack web application featuring:
- **Customer Panel** - Browse, search, filter, cart, checkout, and purchase simulation
- **Admin Panel** - Dashboard, CRUD operations, order management, analytics
- **AI Integration** - Gemini-powered shopping assistant, gift recommender, and dynamic content generator
- **Premium UI** - Dark glassmorphism theme with smooth animations

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Bootstrap 5, Vanilla JavaScript |
| Backend | PHP 8+ |
| Database | MySQL 8+ |
| AI | Google Gemini API (3.5 Flash) |
| Security | PDO Prepared Statements, bcrypt, CSRF protection |

## Features

### Customer Features
- Homepage with AI-generated hero content
- Products page with filters, search, sorting, and pagination
- Product detail pages with reviews and related products
- Categories browsing
- Shopping cart (localStorage-based)
- Checkout with purchase simulation
- Order history
- Contact page
- About page
- AI Shopping Assistant chatbot
- AI Gift Recommendation

### Admin Features
- Secure login (bcrypt authentication)
- Dashboard with statistics and charts
- Product CRUD (Create, Read, Update, Delete)
- Category CRUD
- Order management with status updates
- Customer management
- Revenue analytics with Chart.js
- AI settings configuration
- Contact messages management

### AI Features (Gemini Integration)
1. **Shopping Assistant** - Chat-based product recommendations from database
2. **Gift Recommender** - Analyze requests and suggest matching products
3. **Dynamic Hero Generator** - Generate marketing headlines and CTAs

## Installation

### Requirements
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- cURL extension enabled
- PDO MySQL extension enabled

### Quick Install

1. **Upload files** to your web server document root:
   ```bash
   # Clone or copy the project
   cp -r nsbm-marketplace-ai /var/www/html/
   ```

2. **Run the installer** by navigating to:
   ```
   http://your-domain.com/install.php
   ```

3. **Enter database credentials** and optionally your Gemini API key

4. **Delete install.php** after successful installation

### Persistent Local Development

From the project directory, run:

```bash
./run-dev.sh
```

This starts PHP and a project-local MariaDB database. Database records are kept in
`.data/`, while uploaded product images are kept in `assets/uploads/products/`.
The seed data is loaded only on the first run, so products and uploaded images
remain available after stopping and restarting the development server.

### Manual Install

1. **Create the database:**
   ```bash
   mysql -u root -p < sql/database.sql
   ```

2. **Configure database connection** in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'nsbm_marketplace');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **Configure Gemini API** with server environment variables:
   ```bash
   export GEMINI_API_KEY='your-api-key-here'
   export GEMINI_MODEL='gemini-3.5-flash'
   ```

4. **Set permissions:**
   ```bash
   chmod 755 -R /var/www/html/nsbm-marketplace-ai
   chmod 777 assets/uploads
   ```

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@nsbm.ac.lk | admin123 |
| Sri Monishan | sri.monishan@students.nsbm.ac.lk | student123 |
| Test User NSBM | test.user@students.nsbm.ac.lk | student123 |

## Project Structure

```
nsbm-marketplace-ai/
├── admin/                  # Admin panel
│   ├── assets/css/         # Admin-specific styles
│   ├── includes/           # Admin header/footer
│   ├── pages/              # Admin pages (products, orders, etc.)
│   ├── index.php           # Admin dashboard
│   └── login.php           # Admin login
├── api/                    # REST API endpoints
│   ├── admin.php           # Admin API
│   ├── ai.php              # Gemini AI API
│   ├── auth.php            # Authentication API
│   ├── cart.php            # Cart API
│   ├── categories.php      # Categories API
│   ├── contact.php         # Contact form API
│   ├── orders.php          # Orders API
│   └── products.php        # Products API
├── assets/                 # Frontend assets
│   ├── css/style.css       # Main stylesheet
│   ├── js/app.js           # Main JavaScript
│   └── uploads/            # Product images
├── config/                 # Configuration files
│   ├── app.php             # Application config
│   ├── database.php        # Database connection
│   └── init.php            # Bootstrap/initialization
├── includes/               # Shared components
│   ├── ai-chat.php         # AI chat widget
│   ├── header.php          # Page header
│   └── footer.php          # Page footer
├── models/                 # Data models (PDO)
│   ├── Category.php
│   ├── Order.php
│   ├── Product.php
│   ├── Review.php
│   └── User.php
├── pages/                  # Customer pages
│   ├── about.html
│   ├── cart.php
│   ├── categories.php
│   ├── checkout.php
│   ├── contact.php
│   ├── orders.php
│   ├── product.php
│   └── products.php
├── sql/                    # Database schema
│   └── database.sql
├── .htaccess               # Apache config
├── index.php               # Homepage
├── install.php             # Installer (delete after use)
└── README.md               # This file
```

## Gemini AI Setup

1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Create a new API key
3. Set the `GEMINI_API_KEY` environment variable for the PHP/web-server process
4. The AI features will work with fallback responses if no key is configured

## Security Features

- **PDO Prepared Statements** - SQL injection prevention
- **Password Hashing** - bcrypt with proper salt
- **Input Sanitization** - XSS prevention via htmlspecialchars
- **CORS Headers** - API access control
- **Session Security** - HTTP-only cookies, strict mode
- **File Access Protection** - .htaccess rules for sensitive directories
- **CSRF Protection** - Token-based form validation

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/auth.php?action=login | User login |
| POST | /api/auth.php?action=register | User registration |
| GET | /api/products.php | List products |
| POST | /api/products.php | Create product (admin) |
| PUT | /api/products.php?id=X | Update product (admin) |
| DELETE | /api/products.php?id=X | Delete product (admin) |
| GET | /api/categories.php | List categories |
| POST | /api/orders.php | Create order |
| GET | /api/orders.php | List user orders |
| POST | /api/ai.php?action=chat | AI chat |
| POST | /api/ai.php?action=gift | Gift recommendations |
| POST | /api/ai.php?action=hero | Generate hero content |

## Deployment Options

### Shared Hosting (cPanel)
1. Upload all files to `public_html`
2. Create MySQL database via cPanel
3. Run `install.php`
4. Delete `install.php`

### VPS (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install apache2 php8.1 php8.1-mysql php8.1-curl mysql-server
sudo a2enmod rewrite headers expires deflate
sudo systemctl restart apache2

# Copy project files
sudo cp -r nsbm-marketplace-ai /var/www/html/
sudo chown -R www-data:www-data /var/www/html/nsbm-marketplace-ai
```

### XAMPP (Local Development)
1. Copy project to `C:\xampp\htdocs\nsbm-marketplace-ai`
2. Start Apache and MySQL
3. Navigate to `http://localhost/nsbm-marketplace-ai/install.php`

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Android)

## License

This project is developed for educational purposes at NSBM Green University.

## Credits

- **UI Framework:** Bootstrap 5
- **Icons:** Bootstrap Icons
- **Charts:** Chart.js
- **AI:** Google Gemini API
- **Font:** Inter (Google Fonts)

---

Built with ❤️ for NSBM Green University students.
