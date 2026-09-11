# BLACK X MARKET — Project Status

> Digital Products / Gaming Stuff Marketplace
> Frontend: HTML5, CSS3, Bootstrap 5, JavaScript
> Backend: PHP
> Database / Auth: Firebase (Realtime Database + Email/Password Auth)
> Image Storage: ImgBB API (Firebase Storage use nahi ho raha)
> Last updated: 2026-09-10

---

## 1. Overall Status

**Status: MVP COMPLETE + Production hardening in progress**

Website ka pura core flow ban chuka hai — user registration se lekar payment approval aur digital delivery tak, aur admin panel se pura management. Is session me bugs fix kiye aur production security/SEO/a11y items add kiye.

---

## 2. Yaha Tak Kya Ban Chuka Hai (Completed)

### 2.1 Setup / Foundation
- [x] PHP project structure
- [x] Bootstrap 5 + Bootstrap Icons
- [x] Dark premium gaming theme (custom `style.css`, `admin.css`)
- [x] Fully responsive layout (mobile-first, navbar collapse, responsive grids/tables/sidebar)
- [x] Firebase integration (`firebase/firebase-init.js`)
- [x] ImgBB image upload proxy (`api/upload-image.php`)
- [x] Common includes: `header`, `footer`, `navbar`, `admin-header`, `admin-footer`, `config`, `helpers`
- [x] Session auth bridge (`auth-check.php`, `admin-check.php`, `api/session.php`)

### 2.2 Authentication (User + Admin)
- [x] Register (`auth/register.php`)
- [x] Login (`auth/login.php`)
- [x] Logout (`auth/logout.php`)
- [x] Forgot Password (`auth/forgot-password.php`)
- [x] Firebase Auth (Email/Password) integration
- [x] User profile record auto-create in Realtime DB
- [x] Admin role check + admin route protection
- [x] Banned user handling

### 2.3 Home / Catalogue
- [x] Home page with hero, stats, features, CTA (`index.php`)
- [x] Featured carousel (Firebase driven)
- [x] Featured products
- [x] Reviews preview
- [x] Products listing with search, category, price filter, sort, discount filter, "Load More" (`products.php`)
- [x] Product details with additional details + related products (`product-details.php`)
- [x] Wishlist (`wishlist.php`)
- [x] Cart with quantity update / remove (`cart.php`)
- [x] About page (`about.php`)
- [x] Static policy pages: terms / privacy / refund (`page.php`)

### 2.4 Coupons
- [x] Admin coupon create / edit / delete (`admin/coupons.php`)
- [x] Coupon validation API (`api/validate-coupon.php`)
- [x] Min amount, percentage / flat, max discount, expiry, usage limit
- [x] Server-side coupon re-validation in order creation

### 2.5 Checkout & Payment Submission
- [x] Checkout with order items + summary (`checkout.php`)
- [x] No shipping/address fields (digital products)
- [x] Payment instructions + QR + UPI ID from admin settings
- [x] Payment screenshot upload (ImgBB)
- [x] Transaction ID input
- [x] Order creation with pending / payment_submitted status (`api/create-order.php`)
- [x] Server-side price + coupon validation
- [x] Cart cleared after order

### 2.6 Admin Payment Review & Delivery
- [x] Payment verification list with filters (`admin/payments.php`)
- [x] Payment screenshot viewer + transaction ID
- [x] Approve order with dynamic delivery details
- [x] Reject order with reason
- [x] Delivery details saved on order

### 2.7 Orders & Digital Delivery (User)
- [x] My Orders with tabs/filters (`orders.php`)
- [x] Order details + timeline (`order-details.php`)
- [x] View Card / delivery details modal
- [x] Copy buttons for delivery values
- [x] Secure access (sirf owner apna order dekh sakta hai)

### 2.8 Admin Management
- [x] Dashboard with stats + recent orders/users (`admin/index.php`)
- [x] Manage Users (`admin/users.php`)
- [x] Manage Products with thumbnail upload, edit, duplicate, status toggle, delete (`admin/products.php`)
- [x] Manage Orders (`admin/orders.php`)
- [x] Manage Carousels (`admin/carousels.php`)
- [x] Manage Coupons (`admin/coupons.php`)
- [x] Manage Reviews with moderation (`admin/reviews.php`)
- [x] Manage Support tickets (`admin/support.php`)
- [x] Settings: Payment (UPI / QR / instructions / active) + Site info (`admin/settings.php`)

### 2.9 Reviews & Support (User)
- [x] Reviews page with verified-buyer restriction + rating modal (`reviews.php`)
- [x] Support ticket submit + my tickets + admin reply (`support.php`)
- [x] User profile edit + password change (`profile.php`)

### 2.10 SEO / UX / Error handling
- [x] Unique title, meta description, Open Graph, canonical (`includes/header.php`)
- [x] `noindex` on private pages (orders, profile, checkout, etc.)
- [x] Empty states (cart, wishlist, orders, products, reviews)
- [x] Loading skeletons + global loader
- [x] Toast notifications
- [x] Confirm dialogs
- [x] Friendly error/authorization states
- [x] XSS-safe output escaping (`bxm_e`, `escapeHtml`)

### 2.11 Added In This Session (New)
- [x] **Notifications system** (spec section 55)
  - [x] `notifications/{uid}` Realtime DB node
  - [x] Navbar bell icon + unread count badge + dropdown (`includes/navbar.php`)
  - [x] Full notifications page (`notifications.php`)
  - [x] Mark one / mark all as read
  - [x] Auto-notify on: payment submitted, order approved, payment rejected, support reply, review approved/rejected
  - [x] Notification styles added in `assets/css/style.css`
- [x] **Firebase Realtime Database security rules file** (`firebase/database.rules.json`)
  - [x] Owner-only access, admin management, public read for products/reviews/carousels
  - [x] Fixed root-level admin read for users / support tickets listing
  - [x] Indexes (`createdAt`, `status`, `category`, `uid`) added
- [x] **Admin pagination** added to Orders, Users, Products, Payments, Reviews, Support
  - [x] Shared helpers `pageSlice()` + `renderPager()` in `assets/js/admin.js`
- [x] **Server-side notification write** on order creation (`api/create-order.php`)

### 2.12 Added In This Session (Production Hardening)

- [x] **Admin panel bugfix** — `BXMAdmin.ready()` missing thi, saari admin pages broken thi (`assets/js/admin.js`)
- [x] **Product details bugfix** — `__PRODUCT_ID__` placeholder replace nahi ho raha tha, ab `window.BXM_PRODUCT_ID` se pass hota hai
- [x] **CSRF protection** on `api/session.php`, `api/create-order.php`, `api/validate-coupon.php`, `api/upload-image.php`
  - [x] Token generate (`bxm_csrf_token`) + header verify (`X-CSRF-TOKEN`)
  - [x] Token injected in `includes/header.php` and `includes/admin-header.php`
  - [x] JS `apiFetch` / upload / session / login / register / logout sab CSRF header bhejte hain
- [x] **Coupon usedCount increment** after successful order (`api/create-order.php`)
- [x] **Out-of-stock / inactive product block** at checkout (server-side)
- [x] **Banned user block** at order creation
- [x] **Register now writes `role: user`** so rules + admin checks consistent hain
- [x] **Firebase rules tightened**
  - [x] Users cannot self-promote to admin or unban themselves
  - [x] Users can only create orders with `status: payment_submitted`
  - [x] Users cannot edit existing orders
  - [x] Reviews create only as `pending`; approved/rejected reviews user-edit lock
  - [x] Coupon `usedCount` increment-only write allowed
- [x] **404 page** (`404.php`) + Apache `.htaccess` + PHP router (`router.php`)
- [x] **robots.php** private pages/admin/api ke liye Disallow
- [x] **Policy pages indexable** (terms / privacy / refund)
- [x] **Accessibility**
  - [x] Skip-to-content link
  - [x] Focus-visible outlines
  - [x] Navbar `aria-current`, `aria-controls`
  - [x] Out-of-stock Add to Cart actually `disabled`
- [x] **Production logging**
  - [x] `display_errors` off, `log_errors` on (`includes/config.php`)
  - [x] `bxm_log()` for order/coupon/upload failures
- [x] **HttpOnly + SameSite session cookies**
- [x] **OG image / twitter card** tags
- [x] **Admin coupons pagination**
- [x] **Local vendor assets** — Bootstrap, Bootstrap Icons, Firebase JS ab CDN par nahi, `assets/vendor/` se load hote hain (preview CDN block fix)
- [x] **Subdirectory base path auto-detect** — XAMPP/WAMP par `http://localhost/blackxmarket/` se CSS/JS 404 fix (`includes/config.php`)

---

## 3. File Map

```
/workspace
├── index.php                  # Home
├── products.php               # Product listing + filters
├── product-details.php        # Product details
├── cart.php                   # Cart
├── wishlist.php               # Wishlist
├── checkout.php               # Checkout + payment submission
├── orders.php                 # My Orders
├── order-details.php          # Order details + delivery card
├── notifications.php          # (NEW) User notifications
├── profile.php                # My profile
├── reviews.php                # Public reviews + write review
├── support.php                # Support tickets
├── about.php                  # About us
├── page.php                   # Terms / Privacy / Refund
├── payment.php                # Legacy redirect -> checkout.php
├── 404.php                    # Friendly not-found page
├── robots.php                 # robots.txt content
├── router.php                 # PHP built-in server 404/robots router
├── .htaccess                  # Apache ErrorDocument + robots rewrite
│
├── auth/                      # register, login, logout, forgot-password
├── admin/                     # Full admin panel (10 pages)
├── api/                       # create-order, session, upload-image, validate-coupon
├── includes/                  # config, helpers, header/footer, navbar, checks
├── assets/
│   ├── css/                   # style.css, admin.css
│   ├── js/                    # app.js, admin.js
│   └── images/logo/           # logo.jpeg
└── firebase/
    ├── firebase-init.js
    └── database.rules.json    # Tightened Realtime DB rules
```

---

## 4. Realtime Database Node Structure (Implemented)

```
users/{uid}
products/{productId}
carts/{uid}/{productId}
wishlists/{uid}/{productId}
coupons/{couponId}
orders/{orderId}
reviews/{reviewId}
supportTickets/{ticketId}
carousels/{carouselId}
notifications/{uid}/{notifId}   # NEW
settings/payment
settings/siteInfo
```

---

## 5. Setup / Run Kaise Karein

1. **Firebase Web API key** set karein
   `includes/config.php` me `firebase.apiKey` ki jagah apna real Firebase web API key daalein.
   (`REPLACE_WITH_FIREBASE_API_KEY` placeholder abhi set hai.)

2. **Firebase Realtime DB rules** lagayein
   Firebase Console -> Realtime Database -> Rules me `firebase/database.rules.json` ka content paste karein.

3. **Admin user banayein**
   Firebase Console me apne user ka `users/{uid}/role` value `admin` set karein.

4. **ImgBB key**
   `includes/config.php` me `imgbb.key` daalein (server-side only, client me expose na karein).

5. **Run**
   ```bash
   # PHP built-in server (project root se)
    php -S 0.0.0.0:8000 router.php
    ```
    Phir browser me `http://localhost:8000` kholein.

---

## 6. Pending / Future (Optional)

Ye cheezein spec me "optional / later" hain ya abhi pending hain:

- [ ] Email notifications (spec me "can be added later")
- [ ] README / deployment documentation
- [ ] WebP/AVIF image optimization + caching
- [ ] Dark/Light theme toggle (optional)
- [ ] Env-based config file (API keys ko env vars me shift karna)

---

## 7. Known Notes

- `includes/config.php` me Firebase `apiKey` placeholder hai — isse replace kiye bina live data load nahi hoga (UI me warning notice aayega).
- ImgBB API key server-side (`includes/config.php`) me hai, client JS me expose nahi hoti.
- Admin listing (users, support tickets) ke liye rules file me root-level admin read add kiya gaya hai.
- Updated `firebase/database.rules.json` Firebase Console me paste karna zaroori hai, warna coupon increment / stricter order writes fail ho sakte hain.
- JS files `node --check` se verified hain. PHP binary is environment me available nahi thi, isliye `php -l` skip hua.
