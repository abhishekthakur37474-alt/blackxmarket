# BLACK X MARKET — Digital Products Website
## Complete Design, Features & Development Structure

> **Project Type:** Digital Products / Gaming Stuff Marketplace  
> **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript  
> **Backend:** PHP  
> **Database / Auth:** Firebase (Realtime Database + Email/Password Auth)
> **Image Upload / Storage:** ImgBB API (Firebase Storage not used)  
> **Admin Panel:** PHP + Bootstrap + Firebase  
> **Design Direction:** Premium, dark, modern gaming marketplace  
> **Brand:** BLACK X MARKET

---

# 1. Project Overview

BLACK X MARKET ek premium digital-products marketplace hoga jahan users gaming-related digital products/stuffs browse karke purchase kar sakenge.

Website ka core flow:

1. User website par aayega.
2. Register/Login karega.
3. Products browse karega.
4. Product details dekhega.
5. Product Cart/Wishlist me add karega.
6. Checkout karega.
7. Digital product hone ki wajah se **shipping/address fields nahi honge**.
8. User payment complete karke:
   - Payment screenshot upload karega
   - Transaction ID enter karega
9. Payment submit hone ke baad order **Pending** rahega.
10. Admin payment verify karega.
11. Admin:
    - Approve karega, ya
    - Reject karke reason dega.
12. Approval ke case me admin product ki delivery/access details add karega.
13. User ke **My Orders** me order status update hoga.
14. Approved order me **View Card** button se delivery details show hongi.

---

# 2. Important Product Safety / Business Rule

Website ko normal, lawful digital-goods marketplace ke roop me implement karein.

Products sirf legal gaming/digital goods hone chahiye, jaise:
- Game-related digital items
- Legitimate game keys/codes
- Digital guides
- Presets/assets
- Gaming software/licenses where legally permitted
- Other authorized digital products

Unauthorized accounts, stolen credentials, payment-card data, compromised accounts, or illegally obtained goods ko marketplace par allow nahi kiya jana chahiye.

---

# 3. Firebase Configuration

Use the supplied Firebase project configuration in the project's frontend configuration file.

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyCEce3d6dfhK9vZg...",
  authDomain: "blackxmarket-e227b.firebaseapp.com",
  databaseURL: "https://blackxmarket-e227b-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "blackxmarket-e227b",
  storageBucket: "blackxmarket-e227b.firebasestorage.app", // present in config but unused — images go via ImgBB API
  messagingSenderId: "1002862140509",
  appId: "1:1002862140509:web:813d12df2a0183625641b4",
  measurementId: "G-3JQJNQYBGF"
};
```

**Security:** Production code me Firebase configuration ko secret/password na treat karein. Actual security Firebase Authentication, Database Rules aur server-side validation se enforce honi chahiye. Images ImgBB API se upload honge (section 36 dekhein), Firebase Storage is project me use nahi ho raha.

## 3.1 Enabled Firebase Services

Firebase Console me already enable:

- **Authentication → Sign-in method → Email/Password** ✅ Enabled
- **Realtime Database** ✅ Enabled (region: `asia-southeast1`)

Sara app data (users, products, cart, wishlist, orders, coupons, reviews, support tickets, carousel, settings) **Realtime Database** me hi save hoga. Node structure section 65 me diya hai, aur security rules section 66 me.

---

# 4. Overall Website Structure

```text
BLACK X MARKET
│
├── USER WEBSITE
│   ├── Home
│   ├── About Us
│   ├── Reviews
│   ├── Customer Support
│   ├── Products
│   ├── Product Details
│   ├── Search
│   ├── Cart
│   ├── Wishlist
│   ├── Checkout
│   ├── Payment Submission
│   ├── My Orders
│   ├── Order Details
│   ├── View Card / Delivery Details
│   ├── My Profile
│   ├── Login
│   └── Register
│
├── ADMIN PANEL
│   ├── Dashboard
│   ├── Manage Users
│   ├── Manage Products
│   ├── Manage Orders / Payments
│   ├── Manage Carousels
│   ├── Manage Coupons
│   ├── Manage Reviews
│   ├── Manage Support
│   └── Settings
│
└── SHARED
    ├── Header
    ├── Footer
    ├── Alerts / Toasts
    ├── Modals
    └── Loading States
```

---

# 5. Design System

## Responsive for All Devices

The complete website and admin panel must be **fully responsive across all common screen sizes**.

Required support:

```text
Small Mobile      320px+
Mobile            375px / 390px / 414px
Tablet            768px+
Laptop            1024px+
Desktop           1280px+
Large Desktop     1440px / 1920px+
```

Requirements:

- Mobile-first Bootstrap layout
- No horizontal scrolling
- Responsive navbar with mobile menu
- Responsive product grids
- Responsive product details
- Responsive cart and checkout
- Responsive payment page
- Responsive order cards
- Responsive profile pages
- Responsive support pages
- Responsive admin sidebar
- Admin sidebar collapses on smaller screens
- Admin tables become horizontally scrollable or transform into responsive cards on mobile
- Images maintain correct aspect ratio
- Buttons and form controls remain touch-friendly
- Modals fit mobile screens
- Payment QR code scales correctly on mobile
- Typography and spacing adapt to screen size
- Test both portrait and landscape layouts

Every page must be usable without zooming on mobile.


## Theme

Primary visual style:

- Premium
- Dark
- Gaming-focused
- Minimal
- Modern
- High contrast
- Glassmorphism used lightly
- Smooth hover animations
- Rounded cards
- Clean spacing

## Color Palette

```text
Background:       #050505
Secondary BG:     #0B0B0B
Card BG:          #111111
Border:           #242424
Primary Text:     #FFFFFF
Secondary Text:   #A1A1A1
Muted Text:       #707070
Accent:           #FFFFFF
Success:          #22C55E
Warning:          #F59E0B
Danger:           #EF4444
```

Optional accent color can be used sparingly for CTAs, badges and active states.

## Typography

Use:

- Inter
- Poppins

Recommended:

```text
Headings: Poppins / Inter 600–700
Body: Inter 400–500
Buttons: Inter 500–600
```

---

# 6. Header

Desktop header:

```text
[BLACK X MARKET]

Home
Products
About
Reviews
Support

[Search] [Wishlist] [Cart] [Profile]
```

Mobile:

```text
[Logo]                         [Menu]
```

Features:

- Sticky header
- Transparent/dark background
- Search
- Wishlist count
- Cart count
- User profile menu
- Login/Register if logged out
- Logout if logged in

---

# 7. Footer

Footer sections:

### Brand

BLACK X MARKET

Short description.

### Quick Links

- Home
- Products
- About Us
- Reviews
- Customer Support

### Account

- My Profile
- My Orders
- Wishlist
- Cart

### Support

- Customer Support
- Terms & Conditions
- Privacy Policy
- Refund Policy

### Copyright

```text
© 2026 BLACK X MARKET. All Rights Reserved.
```

---

# 8. Home Page

## Hero Section

Large premium hero:

```text
DIGITAL GAMING
STUFF, DELIVERED.

Discover premium digital products
at BLACK X MARKET.

[Shop Now] [Explore Products]
```

Hero can use an admin-managed carousel/banner.

---

## Featured Carousel

Admin controls:

- Title
- Thumbnail
- Link / Product
- Active/Inactive
- Sort Order

Frontend:

```text
[Banner Image]
Title
Short Description

[Explore Now]
```

---

## Featured Products

Grid:

```text
[Product] [Product] [Product] [Product]
[Product] [Product] [Product] [Product]
```

Product card contains:

- Thumbnail
- Product title
- Original price
- Discounted price
- Discount badge
- Wishlist icon
- Add to Cart
- View Details

---

## Why Choose Us

Cards:

- Fast Digital Delivery
- Secure Checkout
- Verified Products
- Customer Support

---

## Reviews Preview

Show selected/latest approved reviews.

---

## CTA

```text
Ready to get started?

Browse our digital collection.

[Shop Products]
```

---

# 9. Products Page

URL concept:

```text
/products
```

Features:

- Product grid
- Search
- Category filter
- Price filter
- Sort
- Discount filter
- Pagination / Load More

Sort options:

```text
Newest
Price: Low to High
Price: High to Low
Most Popular
Highest Discount
```

---

# 10. Product Details Page

Display:

```text
[Large Thumbnail]

TITLE

★★★★★
Category

Original Price: ₹XXXX
Discounted Price: ₹XXX

[Add to Cart]
[Buy Now]
[Wishlist]
```

Then:

## Product Description

Admin-managed product details.

## Additional Details

Dynamic key/value fields.

Example:

```text
Platform       : PC
Type           : Digital
Delivery       : After Payment Approval
Compatibility  : ...
```

## Related Products

Show similar products.

---

# 11. Admin Product Creation

Admin navigates:

```text
Admin Panel
→ Manage Products
→ Add Product
```

Fields:

### Basic Information

```text
Title
Category
Thumbnail Upload
Description
```

### Pricing

```text
Original Price
Discounted Price
```

Automatically calculate:

```text
Discount % = ((Original - Discounted) / Original) × 100
```

### Additional Details

Admin can dynamically add fields:

```text
[Field Name] [Field Value] [+ Add]
```

Example:

```text
Platform       PC
Delivery Type  Digital
Region         Global
Type            Game Item
```

Admin can:

- Add field
- Edit field
- Delete field
- Reorder field

### Product Status

```text
Active
Inactive
Out of Stock
```

### Product Actions

```text
Create
Edit
Delete
Duplicate
Activate
Deactivate
```

---

# 12. Cart

Cart should contain:

```text
Product
Price
Quantity
Subtotal
Remove
```

Because products are digital:

- No shipping calculation
- No delivery address
- No physical shipping section

Order summary:

```text
Subtotal
Discount
Coupon Discount
Final Total

[Proceed to Checkout]
```

---

# 13. Wishlist

User can:

- Add product
- Remove product
- Move to Cart
- View product

Wishlist should be stored per user.

---

# 14. Coupons

## Admin — Manage Coupons

Admin can create coupons with:

```text
Coupon Code
Coupon Type
Discount %
Minimum Cart Amount
Maximum Discount Amount
Start Date
Expiry Date
Usage Limit
Per User Limit
Active / Inactive
```

Example:

```text
Code: GAMING20
Minimum Purchase: ₹500
Discount: 20%
Maximum Discount: ₹200
```

Frontend checkout:

```text
[Enter Coupon Code] [Apply]
```

If valid:

```text
Coupon Applied ✓
You saved ₹XXX
```

Validation:

- Coupon exists
- Coupon active
- Date valid
- Minimum cart value reached
- Usage limit not exceeded
- User usage limit not exceeded

---

# 15. Checkout

Digital product checkout should be simple.

DO NOT ask for:

- House number
- Street address
- City
- State
- Postal code
- Shipping address

Checkout contains:

```text
Order Items

Subtotal
Coupon Discount
Final Amount

Payment Instructions

Payment Screenshot
[Upload Screenshot]

Transaction ID
[Enter Transaction ID]

[Submit Payment]
```

---

# 16. Payment Submission Flow

User completes payment externally according to the marketplace's displayed payment instructions.

Then submits:

```text
Payment Screenshot
Transaction ID
```

After submission:

```text
Payment Submitted Successfully

Status: Pending Verification
```

User should not receive product delivery details until admin approval.

---

# 17. Payment / Order Status

Recommended statuses:

```text
Pending Payment
Payment Submitted
Under Review
Approved
Rejected
Completed
Cancelled
```

User-facing simplified statuses:

### Pending

```text
Payment is under verification.
```

### Approved

```text
Payment approved.
Your digital product is ready.
[View Card]
```

### Rejected

```text
Payment rejected.

Reason:
<Admin reason>

[Contact Support]
```

---

# 18. Admin — Manage Payments / Orders

Admin table:

```text
Order ID
User
Product
Amount
Transaction ID
Payment Screenshot
Date
Status
Actions
```

Actions:

```text
View
Approve
Reject
```

When clicking **View**:

```text
Customer Information
Order Information
Product Information
Amount
Transaction ID
Payment Screenshot
Order Timeline
```

---

# 19. Admin Approval Flow

When admin clicks:

```text
Approve
```

Open modal/page:

```text
Order Approved

Delivery Details
[Dynamic Input Fields]

[+ Add Detail]

Example:

Name: BLACK X MARKET
Product Key: XXXXX-XXXXX
Username: XXXXX
Password: XXXXX
Instructions: XXXXX

[Approve & Deliver]
```

Admin can add unlimited delivery/detail fields.

Example:

```text
Name
Value

Name       BLACK X MARKET
Username   example
License    XXXX-XXXX
Instructions  ...
```

Then:

```text
[Approve & Deliver]
```

Order becomes:

```text
Approved
```

User can now access the delivery card.

---

# 20. View Card

User's approved order contains:

```text
[View Card]
```

Opening it shows a premium digital delivery card.

Example:

```text
--------------------------------
       BLACK X MARKET

Product Name
-------------------------------

Name:
BLACK X MARKET

Username:
XXXXXXXX

License:
XXXXXXXX

Instructions:
XXXXXXXX

Order ID:
#BXM-000001

Status:
APPROVED
--------------------------------
```

Important:

- Delivery details hidden before approval
- Only the purchasing user can access them
- Admin can update delivery information
- User can copy individual values
- Sensitive delivery information should not appear in public URLs

---

# 21. My Orders

Page:

```text
My Orders
```

Order cards:

```text
Product Thumbnail
Product Name
Order ID
Amount
Date
Status

[View Order]
```

Statuses:

### Pending

Yellow badge.

### Approved

Green badge.

### Rejected

Red badge + reason.

### Completed

Green badge.

---

# 22. Order Details

Show:

```text
Order ID
Product
Amount
Coupon
Payment Status
Transaction ID
Created Date
Updated Date
```

Timeline:

```text
Order Created
     ↓
Payment Submitted
     ↓
Under Review
     ↓
Approved / Rejected
     ↓
Delivery Available
```

Approved:

```text
[View Card]
```

Rejected:

```text
Reason:
<Admin reason>

[Contact Support]
```

---

# 23. My Profile

Fields:

```text
Name
Email
Profile Picture
Account Created
```

Actions:

```text
Edit Profile
Change Password
Logout
```

Optional:

```text
Total Orders
Approved Orders
Wishlist Items
```

---

# 24. Login

Fields:

```text
Email
Password
```

Buttons:

```text
[Login]
```

Links:

```text
Forgot Password?
Create Account
```

Firebase Authentication:

```text
Email + Password
```

---

# 25. Register

Fields:

```text
Full Name
Email
Password
Confirm Password
```

Validation:

- Valid email
- Password minimum length
- Confirm password match
- Email uniqueness

After registration:

```text
Account Created Successfully
```

---

# 26. Customer Support

Page contains:

```text
Customer Support

How can we help?

[Order ID]
[Subject]
[Message]

[Submit Ticket]
```

Ticket statuses:

```text
Open
In Progress
Resolved
Closed
```

User can see previous tickets.

Admin can:

- View tickets
- Reply
- Change status
- Close ticket

---

# 27. Reviews

Users can review purchased products.

Review fields:

```text
Rating: 1–5 Stars
Review Text
```

Only users who purchased the product should be allowed to review it.

Admin moderation:

```text
Pending
Approved
Rejected
```

Only approved reviews appear publicly.

---

# 28. Admin Dashboard

Dashboard should provide quick statistics.

Cards:

```text
Total Users
Total Products
Total Orders
Pending Payments
Approved Orders
Rejected Orders
Total Revenue
Active Coupons
```

Recent Orders:

```text
Order ID
Customer
Product
Amount
Status
Date
```

Pending Payments section:

```text
5 payments waiting for verification
[Review Payments]
```

---

# 29. Admin — Manage Users

Table:

```text
User
Email
Registered Date
Orders
Status
Actions
```

Actions:

```text
View User
Disable User
Enable User
View Orders
```

User detail:

```text
Name
Email
Registration Date
Order Count
Wishlist Count
Recent Orders
```

Never expose passwords.

---

# 30. Admin — Manage Products

Table:

```text
Thumbnail
Title
Category
Original Price
Discounted Price
Discount
Status
Created Date
Actions
```

Actions:

```text
Edit
Delete
Activate
Deactivate
Duplicate
```

Search and filters required.

---

# 31. Admin — Manage Carousels

Fields:

```text
Title
Subtitle
Thumbnail Upload
Button Text
Button Link
Sort Order
Active / Inactive
```

Admin can:

```text
Create
Edit
Delete
Activate
Deactivate
Reorder
```

Homepage automatically displays active carousel items.

---

# 32. Admin — Manage Coupons

Table:

```text
Code
Discount
Minimum Amount
Maximum Discount
Usage
Expiry
Status
Actions
```

Actions:

```text
Create
Edit
Delete
Activate
Deactivate
```

---

# 33. Admin — Manage Reviews

Table:

```text
User
Product
Rating
Review
Date
Status
```

Actions:

```text
Approve
Reject
Delete
```

---

# 34. Admin — Manage Support

Table:

```text
Ticket ID
User
Subject
Date
Status
```

Actions:

```text
View
Reply
Change Status
Close
```

---

# 35. Firebase Data Structure

Recommended Firebase Realtime Database structure:

```text
/
├── users/
│   └── UID/
│       ├── name
│       ├── email
│       ├── photoURL
│       ├── role
│       ├── status
│       └── createdAt
│
├── products/
│   └── PRODUCT_ID/
│       ├── title
│       ├── category
│       ├── thumbnail
│       ├── description
│       ├── originalPrice
│       ├── discountedPrice
│       ├── discountPercent
│       ├── additionalDetails/
│       ├── status
│       ├── createdAt
│       └── updatedAt
│
├── categories/
│
├── orders/
│   └── ORDER_ID/
│       ├── userId
│       ├── productId
│       ├── productTitle
│       ├── amount
│       ├── couponCode
│       ├── couponDiscount
│       ├── paymentScreenshot
│       ├── transactionId
│       ├── paymentSnapshot/
│       │   ├── upiId
│       │   ├── qrCodeUrl
│       │   └── paymentDisplayName
│       ├── status
│       ├── rejectionReason
│       ├── deliveryDetails/
│       ├── createdAt
│       └── updatedAt
│
├── carts/
│   └── UID/
│
├── wishlists/
│   └── UID/
│
├── coupons/
│   └── COUPON_ID/
│
├── carousels/
│   └── CAROUSEL_ID/
│
├── reviews/
│   └── REVIEW_ID/
│
├── supportTickets/
│   └── TICKET_ID/
│
└── settings/
```

---

# 36. Image Upload — ImgBB API (Firebase Storage NOT used)

Sabhi image uploads (product thumbnail, carousel banner, payment screenshot, profile image) **ImgBB API** ke through hongi. Firebase Storage is project me use nahi hoga — sirf Auth + Realtime Database use ho rahe hain.

## API Details

```text
Endpoint : https://api.imgbb.com/1/upload
Method   : POST
Key      : ce94b9f4039dd440aafebf96f1282e39
```

Request (multipart/form-data or base64):

```text
POST https://api.imgbb.com/1/upload?key=ce94b9f4039dd440aafebf96f1282e39
Body: image = <file / base64>
```

Response me `data.url` (direct image link) milega — yehi URL RTDB me save hoga (e.g. `products/{productId}/thumbnailUrl`, `orders/{orderId}/paymentScreenshotUrl`).

## Where to call it from

- **Server-side (PHP) se call karna recommended hai**, browser se directly nahi — taaki key client JS bundle me expose na ho.
- Flow: Browser file input → PHP upload endpoint (multipart) → PHP `curl`/`file_get_contents` se ImgBB ko forward → response ka `data.url` wapas frontend ko → wahi URL RTDB me save.

```text
User → PHP (/api/upload-image.php) → ImgBB API → {url} → RTDB field
```

## Used for

```text
Product thumbnail        → products/{productId}.thumbnailUrl
Carousel banner           → carousels/{carouselId}.imageUrl
Payment screenshot        → orders/{orderId}.paymentScreenshotUrl
Profile image (optional)  → users/{uid}.photoUrl
```

Validate before upload (client + server side):

- File type (jpg/png/webp only)
- File size limit (e.g. max 5MB)
- Upload permissions (only logged-in user for payment screenshot/profile; only admin for product/carousel)

**Security note:** Ye ImgBB key currently plain text me shared hai. Production me isko `.env` / PHP server config me rakhein, client-side JS me kabhi hardcode na karein, aur agar ye key kahin publicly commit/share ho chuki hai to ImgBB dashboard se naya key generate kar lein.

---

# 37. PHP Structure

Suggested structure:

```text
black-x-market/
│
├── index.php
├── about.php
├── products.php
├── product-details.php
├── reviews.php
├── support.php
├── cart.php
├── wishlist.php
├── checkout.php
├── payment.php
├── orders.php
├── order-details.php
├── profile.php
│
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── forgot-password.php
│
├── admin/
│   ├── index.php
│   ├── users.php
│   ├── products.php
│   ├── orders.php
│   ├── payments.php
│   ├── carousels.php
│   ├── coupons.php
│   ├── reviews.php
│   ├── support.php
│   └── settings.php
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── fonts/
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── auth-check.php
│   ├── admin-check.php
│   └── helpers.php
│
└── firebase/
    ├── firebase-config.js
    └── firebase-init.js
```

---

# 38. Recommended PHP Responsibilities

PHP should handle:

- Page rendering
- Admin panel
- Server-side validation
- Protected admin routes
- Secure operations
- Firebase REST/Admin SDK integration where required
- Order workflow
- Coupon validation
- Review moderation
- Support management

Never trust only JavaScript for:

- Prices
- Discounts
- Coupon validation
- User roles
- Order ownership
- Admin authorization
- Payment status

---

# 39. Authentication Architecture

Use Firebase Authentication for:

```text
Email/Password Login
Registration
Password Reset
```

After login:

```text
Firebase Auth UID
        ↓
users/{UID}
        ↓
role
```

Roles:

```text
user
admin
```

Admin pages must verify admin authorization.

Do not determine admin access merely from a client-side variable.

---

# 40. Order Security

Each order must have:

```text
orderId
userId
productId
amount
status
createdAt
```

User can only read orders where:

```text
order.userId == authenticatedUser.uid
```

User must NOT be able to:

- Change price
- Change order status
- Approve payment
- Add delivery details
- Change another user's order
- Access another user's delivery details

Only authorized admin can perform approval/delivery actions.

---

# 41. Payment Security

Payment screenshot should be treated as private order information.

Rules:

- User uploads only for their own order.
- Admin can review it.
- Other users cannot access it.
- File type and size must be validated.
- Transaction ID should be stored with the order.
- Duplicate/invalid transaction IDs should be flagged for manual review where applicable.

Do not store card numbers, CVV, passwords, or banking credentials.

---

# 42. UI Components

Create reusable components:

```text
ProductCard
CarouselCard
OrderCard
StatusBadge
PriceDisplay
CouponBox
ReviewCard
Modal
Toast
Loader
EmptyState
Pagination
SearchBox
FilterDropdown
AdminTable
StatsCard
```

---

# 43. Responsive Design

Must work properly on:

```text
Mobile
Tablet
Laptop
Desktop
Large Desktop
```

Bootstrap breakpoints:

```text
xs
sm
md
lg
xl
xxl
```

Mobile-first implementation recommended.

---

# 44. UX Requirements

Add:

- Skeleton loaders
- Loading indicators
- Toast notifications
- Confirmation modals
- Empty states
- Error states
- Success states
- Form validation
- Disabled button states
- Image previews
- Copy-to-clipboard
- Responsive navigation

Example:

```text
Payment submitted ✓
```

```text
Coupon applied ✓
```

```text
Payment rejected
```

---

# 45. Search

Global search should search:

```text
Product Title
Category
Description
```

Search UI:

```text
[ Search digital products... ]
```

Show:

```text
Search Results
```

with empty state:

```text
No products found.
Try another search.
```

---

# 46. Product Pricing Rules

Never calculate final payment only from the frontend.

Server-side calculation:

```text
Original Price
        ↓
Discounted Price
        ↓
Coupon Validation
        ↓
Coupon Discount
        ↓
Final Payable Amount
```

Example:

```text
Original Price:     ₹1,000
Discounted Price:  ₹700
Coupon Discount:   ₹100

Final:              ₹600
```

---

# 47. Coupon Calculation

For percentage coupon:

```text
Coupon Discount =
Cart Amount × Discount Percentage / 100
```

Then:

```text
Applied Discount =
MIN(Calculated Discount, Maximum Discount)
```

Then verify:

```text
Cart Amount >= Minimum Amount
```

---

# 48. Recommended Order ID

Readable format:

```text
BXM-2026-000001
```

or:

```text
BXM-XXXXXXXX
```

Never rely on an easily guessable ID alone for authorization.

Authorization should always verify the authenticated user's UID.

---

# 49. Admin Sidebar

```text
BLACK X MARKET
ADMIN PANEL

Dashboard

Management
├── Users
├── Products
├── Orders
├── Payments
├── Carousels
├── Coupons
├── Reviews
└── Support

Settings

Logout
```

---

# 49A. Admin Payment Settings — QR & UPI

Admin should have a dedicated **Settings → Payment Settings** section.

The admin can manage the payment information shown to users during checkout.

## Admin Fields

```text
UPI ID
QR Code
Payment Display Name
Payment Instructions
Active / Inactive
```

### QR Code

Admin can:

- Upload QR code
- Preview QR code
- Replace QR code
- Remove QR code

Recommended storage:

```text
settings/payment/qr-code
```

### UPI ID

Admin can enter/edit:

```text
example@upi
```

The UPI ID should be displayed to users and have a **Copy** button.

### Payment Instructions

Admin can add instructions such as:

```text
Scan the QR code or use the UPI ID below.
Complete the payment for the exact order amount.
After payment, upload your payment screenshot and enter the transaction ID.
```

## User Payment Screen

During checkout/payment submission, the user sees the currently active payment settings configured by the admin:

```text
----------------------------------
          PAYMENT

Amount Payable: ₹699

          [ QR CODE ]

UPI ID
example@upi                 [Copy]

Payment Instructions
<Admin configured instructions>

----------------------------------

Payment Screenshot
[ Upload Screenshot ]

Transaction ID
[________________________]

[ Submit Payment ]
----------------------------------
```

The page must **not hardcode** the QR code or UPI ID.

It should always load the latest active values from:

```text
settings/payment
```

## Payment Flow

```text
User Checkout
      ↓
Payment Page
      ↓
Load Admin QR + UPI ID
      ↓
User Scans QR / Uses UPI ID
      ↓
User Completes Payment
      ↓
Upload Payment Screenshot
      ↓
Enter Transaction ID
      ↓
Submit Payment
      ↓
Order Status = Payment Submitted / Under Review
      ↓
Admin Reviews
      ↓
Approve OR Reject
```

If admin changes the QR/UPI later, **new payment sessions should show the updated payment information**.

For audit purposes, the order can also store a snapshot of the payment destination shown when the order was submitted:

```text
paymentSnapshot/
    upiId
    qrCodeUrl
    paymentDisplayName
```

This makes it clear which payment information was presented for that particular order.

# 50. Admin Dashboard Layout

Top:

```text
Welcome back, Admin
```

Stats:

```text
[Users] [Products] [Orders] [Pending Payments]
```

Second row:

```text
[Revenue] [Approved] [Rejected] [Coupons]
```

Bottom:

```text
Recent Orders
Pending Payments
Recent Users
```

---

# 51. Product Card UI

Recommended structure:

```text
┌─────────────────────┐
│                     │
│     THUMBNAIL       │
│                     │
│  -30%        ♡      │
├─────────────────────┤
│ Product Title       │
│ Category            │
│                     │
│ ₹699  ₹999          │
│                     │
│ [Add to Cart]       │
└─────────────────────┘
```

---

# 52. Order Card UI

```text
┌────────────────────────────┐
│ Product Name               │
│ Order: BXM-2026-000001     │
│ Amount: ₹699               │
│                            │
│ Status: APPROVED           │
│                            │
│ [View Order] [View Card]   │
└────────────────────────────┘
```

---

# 53. Rejected Order UX

```text
Payment Rejected

Reason:
Transaction could not be verified.

Order ID:
BXM-2026-000001

[Contact Support]
```

Optionally allow a **Resubmit Payment** flow if the business wants it.

---

# 54. Admin Approval UX

Payment review page:

```text
Payment Review

Customer
Product
Amount
Transaction ID

Payment Screenshot
[View Image]

Status
[Pending]

--------------------------------

[Reject]
[Approve & Add Delivery]
```

Reject modal:

```text
Reason for rejection
[________________________]

[Reject Payment]
```

Approve flow:

```text
Add Delivery Details

Name
[________________]

Value
[________________]

[+ Add Another Detail]

[Approve & Deliver]
```

---

# 55. Notifications

Use in-site notifications.

Examples:

```text
Your payment has been submitted.
```

```text
Your order has been approved.
```

```text
Your payment was rejected.
```

```text
Coupon applied successfully.
```

Optional email notifications can be added later.

---

# 56. Performance

Optimize:

- Product thumbnails
- Carousel images
- Lazy loading
- Firebase reads
- Pagination
- Cached product data
- Minified CSS/JS
- WebP/AVIF images where supported

Avoid downloading every product on every page.

---

# 57. SEO

Public pages should include:

```text
Unique title
Meta description
Open Graph tags
Canonical URL
Proper H1/H2 hierarchy
Alt text
Clean URLs
```

Example:

```text
/ 
/products
/products/product-name
/about
/reviews
/support
```

Do not index:

```text
/admin
/my-orders
/profile
/checkout
```

---

# 58. Error Handling

Create friendly error states.

Examples:

```text
Something went wrong.
Please try again.
```

```text
Product not found.
```

```text
You are not authorized to access this page.
```

```text
Your session has expired.
Please login again.
```

---

# 59. Empty States

Cart:

```text
Your cart is empty.

Browse our digital products.

[Shop Now]
```

Wishlist:

```text
No wishlist items yet.
```

Orders:

```text
You haven't placed any orders yet.

[Browse Products]
```

---

# 60. Development Phases

## Step 1 — Project Setup

- Create PHP project
- Install Bootstrap
- Create Firebase project integration
- Configure Firebase Authentication
- Configure Realtime Database
- Configure ImgBB API upload endpoint (PHP proxy)
- Create common includes
- Create responsive layout

**Step 1 Complete**

---

## Step 2 — Authentication

Build:

- Register
- Login
- Logout
- Forgot Password
- Firebase Auth integration
- User profile record

**Step 2 Complete**

---

## Step 3 — Admin Authentication

Build:

- Admin login protection
- Admin role
- Admin dashboard
- Admin sidebar
- Admin route protection

**Step 3 Complete**

---

## Step 4 — Product Management

Build:

- Create product
- Thumbnail upload
- Edit product
- Delete product
- Product status
- Dynamic additional details
- Product listing

**Step 4 Complete**

---

## Step 5 — User Product Experience

Build:

- Home
- Products
- Search
- Filters
- Product details
- Wishlist
- Cart

**Step 5 Complete**

---

## Step 6 — Coupon System

Build:

- Admin coupon creation
- Coupon validation
- Minimum amount
- Percentage discount
- Maximum discount
- Expiry
- Usage limits
- Checkout integration

**Step 6 Complete**

---

## Step 7 — Checkout & Payment Submission

Build:

- Checkout
- No shipping address
- Order creation
- Payment instructions
- Screenshot upload
- Transaction ID
- Pending status

**Step 7 Complete**

---

## Step 8 — Admin Payment Review

Build:

- Payment list
- Payment screenshot viewer
- Transaction ID
- Approve
- Reject
- Rejection reason
- Delivery details form

**Step 8 Complete**

---

## Step 9 — Digital Delivery

Build:

- My Orders
- Order details
- Status
- View Card
- Delivery details
- Copy buttons
- Secure access

**Step 9 Complete**

---

## Step 10 — Carousel / Reviews / Support

Build:

- Carousel management
- Homepage carousel
- Reviews
- Review moderation
- Customer support
- Ticket system

**Step 10 Complete**

---

## Step 11 — Final UI Polish

- Responsive testing
- Loading states
- Empty states
- Toasts
- Animations
- Image optimization
- Accessibility
- Error handling

**Step 11 Complete**

---

## Step 12 — Security & Production

Check:

- Firebase Database Rules
- ImgBB key kept server-side only (not exposed in client JS)
- Admin authorization
- User ownership
- Server-side price validation
- Coupon validation
- File validation
- XSS protection
- CSRF protection where applicable
- Input sanitization
- Error logging
- Production environment configuration

**Step 12 Complete**

---

# 61. Final User Journey

```text
HOME
 ↓
PRODUCTS
 ↓
PRODUCT DETAILS
 ↓
ADD TO CART
 ↓
CART
 ↓
CHECKOUT
 ↓
PAYMENT
 ↓
UPLOAD SCREENSHOT
 ↓
ENTER TRANSACTION ID
 ↓
SUBMIT
 ↓
PENDING
 ↓
ADMIN REVIEWS
 ↓
 ┌───────────────┐
 │               │
APPROVED       REJECTED
 │               │
 ↓               ↓
ADD DELIVERY    SHOW REASON
DETAILS          │
 │               ↓
 ↓            SUPPORT
MY ORDERS
 │
 ↓
VIEW CARD
 │
 ↓
DIGITAL PRODUCT DETAILS
```

---

# 62. Final Admin Journey

```text
ADMIN LOGIN
 ↓
DASHBOARD
 ↓
MANAGE PRODUCTS
 ↓
CREATE PRODUCT
 ↓
USER PURCHASES
 ↓
MANAGE PAYMENTS
 ↓
OPEN PAYMENT
 ↓
CHECK SCREENSHOT
 ↓
CHECK TRANSACTION ID
 ↓
 ┌─────────────────┐
 │                 │
APPROVE          REJECT
 │                 │
 ↓                 ↓
ADD DELIVERY     ADD REASON
DETAILS
 │
 ↓
APPROVE & DELIVER
 │
 ↓
USER GETS ACCESS
```

---

# 63. Final Recommended Feature List

## User Side

- Home
- Product listing
- Product search
- Product filtering
- Product details
- Cart
- Wishlist
- Checkout
- Coupon
- Payment screenshot upload
- Transaction ID
- My Orders
- Order tracking/status
- View Card
- My Profile
- Reviews
- Customer Support
- Login
- Register
- Forgot Password
- Logout
- Notifications

## Admin Side

- Dashboard
- Manage Users
- Manage Products
- Manage Orders
- Manage Payments
- Payment Approval
- Payment Rejection
- Rejection Reason
- Delivery Details
- Manage Carousels
- Manage Coupons
- Manage Reviews
- Manage Support Tickets
- Site Settings
- Payment Settings
- QR Code Management
- UPI ID Management
- Admin authentication
- Search/filter/pagination

---

# 64. Important Implementation Principle

The frontend should never be the authority for sensitive business logic.

Use this architecture:

```text
USER
 ↓
PHP + Firebase Auth
 ↓
Authenticated UID
 ↓
Firebase Realtime Database (+ ImgBB for images)
 ↓
Server-side validation
 ↓
Admin approval
 ↓
Secure delivery access
```

The most important security rule is:

```text
User → Can access only their own data/orders.
Admin → Can manage authorized marketplace data.
Public → Can access only public product/content data.
```

This structure keeps BLACK X MARKET scalable and makes it easier to add more digital products, payment methods, coupons, admin features and notification systems later.

---

# 65. Realtime Database — Node Structure

Sara data Firebase RTDB me neeche diye node structure ke hisaab se save hoga. UID Firebase Auth se aayega.

```text
blackxmarket-e227b-default-rtdb
│
├── users
│   └── {uid}
│       ├── name
│       ├── email
│       ├── phone
│       ├── role            // "user" | "admin"
│       ├── createdAt
│       └── status          // "active" | "banned"
│
├── products
│   └── {productId}
│       ├── title
│       ├── category
│       ├── thumbnailUrl
│       ├── description
│       ├── originalPrice
│       ├── discountedPrice
│       ├── discountPercent
│       ├── status           // "active" | "inactive" | "out_of_stock"
│       ├── additionalDetails
│       │   └── {fieldId}
│       │       ├── name
│       │       ├── value
│       │       └── order
│       ├── createdAt
│       └── updatedAt
│
├── carts
│   └── {uid}
│       └── {productId}
│           ├── quantity
│           └── addedAt
│
├── wishlists
│   └── {uid}
│       └── {productId}: true
│
├── coupons
│   └── {couponId}
│       ├── code
│       ├── type              // "percentage" | "flat"
│       ├── value
│       ├── minAmount
│       ├── maxDiscount
│       ├── expiryDate
│       ├── usageLimit
│       ├── usedCount
│       └── status            // "active" | "inactive"
│
├── orders
│   └── {orderId}              // e.g. BXM-2026-000001
│       ├── uid
│       ├── productId
│       ├── productTitle
│       ├── amount
│       ├── couponCode
│       ├── transactionId
│       ├── paymentScreenshotUrl
│       ├── status             // "pending" | "approved" | "rejected"
│       ├── rejectionReason
│       ├── deliveryDetails
│       │   └── {fieldId}
│       │       ├── name
│       │       └── value
│       ├── createdAt
│       └── updatedAt
│
├── reviews
│   └── {reviewId}
│       ├── uid
│       ├── userName
│       ├── productId
│       ├── rating
│       ├── comment
│       ├── status            // "pending" | "approved" | "rejected"
│       └── createdAt
│
├── supportTickets
│   └── {ticketId}
│       ├── uid
│       ├── subject
│       ├── message
│       ├── status            // "open" | "replied" | "closed"
│       ├── reply
│       └── createdAt
│
├── carousels
│   └── {carouselId}
│       ├── title
│       ├── description
│       ├── imageUrl
│       ├── link
│       ├── status            // "active" | "inactive"
│       └── order
│
└── settings
    ├── upiId
    ├── qrCodeUrl
    └── siteInfo
```

Notes:

- `orders`, `carts`, `wishlists` root-level rakhe (uid ke andar nahi nested inside `users`) taaki rules simple aur ownership-check fast rahe.
- Admin role `users/{uid}/role == "admin"` se check hoga — sabhi admin-only writes isi field ko refer karenge.

---

# 66. Realtime Database Security Rules

Ye rules Firebase Console → Realtime Database → Rules me paste karein. In rules ka core principle: **user apna data khud read/write kare, admin sab manage kare, public sirf active products/reviews/carousel padhe.**

```json
{
  "rules": {

    "users": {
      "$uid": {
        ".read": "auth != null && (auth.uid === $uid || root.child('users').child(auth.uid).child('role').val() === 'admin')",
        ".write": "auth != null && (auth.uid === $uid || root.child('users').child(auth.uid).child('role').val() === 'admin')",
        "role": {
          ".write": "auth != null && root.child('users').child(auth.uid).child('role').val() === 'admin'"
        }
      }
    },

    "products": {
      ".read": true,
      "$productId": {
        ".write": "auth != null && root.child('users').child(auth.uid).child('role').val() === 'admin'"
      }
    },

    "carts": {
      "$uid": {
        ".read": "auth != null && auth.uid === $uid",
        ".write": "auth != null && auth.uid === $uid"
      }
    },

    "wishlists": {
      "$uid": {
        ".read": "auth != null && auth.uid === $uid",
        ".write": "auth != null && auth.uid === $uid"
      }
    },

    "coupons": {
      ".read": "auth != null",
      ".write": "auth != null && root.child('users').child(auth.uid).child('role').val() === 'admin'"
    },

    "orders": {
      ".read": "auth != null && root.child('users').child(auth.uid).child('role').val() === 'admin'",
      "$orderId": {
        ".read": "auth != null && (data.child('uid').val() === auth.uid || root.child('users').child(auth.uid).child('role').val() === 'admin')",
        ".write": "auth != null && (
          (!data.exists() && newData.child('uid').val() === auth.uid) ||
          root.child('users').child(auth.uid).child('role').val() === 'admin'
        )"
      }
    },

    "reviews": {
      ".read": true,
      "$reviewId": {
        ".write": "auth != null && (
          (!data.exists() && newData.child('uid').val() === auth.uid) ||
          data.child('uid').val() === auth.uid ||
          root.child('users').child(auth.uid).child('role').val() === 'admin'
        )"
      }
    },

    "supportTickets": {
      "$ticketId": {
        ".read": "auth != null && (data.child('uid').val() === auth.uid || root.child('users').child(auth.uid).child('role').val() === 'admin')",
        ".write": "auth != null && (
          (!data.exists() && newData.child('uid').val() === auth.uid) ||
          data.child('uid').val() === auth.uid ||
          root.child('users').child(auth.uid).child('role').val() === 'admin'
        )"
      }
    },

    "carousels": {
      ".read": true,
      ".write": "auth != null && root.child('users').child(auth.uid).child('role').val() === 'admin'"
    },

    "settings": {
      ".read": true,
      ".write": "auth != null && root.child('users').child(auth.uid).child('role').val() === 'admin'"
    }
  }
}
```

Important points:

- Ye rules **write karte time field edit** ko fully lock nahi karti (jaise order approve hone ke baad user status change na kar paaye) — usko production me `newData`/`data` field-level comparisons se aur tight karna chahiye (e.g. user sirf naya order create kare, existing order ka `status` field kabhi na chede).
- `orders`, `reviews`, `supportTickets` me "user apna record edit kare" wali line thodi loose hai — chahiye toh isko restrict kar sakte hain ki user sirf `status: pending` wale record edit kare, approved/rejected ke baad lock ho jaaye.
- PHP backend Firebase Admin SDK / REST API ke through likhte waqt bhi inhi rules ka respect rakhna chahiye — admin operations Firebase Admin credentials se hoti hain jo rules bypass karti hain, isliye **price/discount/coupon validation server-side (PHP) me bhi dobara check karein**, sirf frontend/rules par bharosa na karein.
