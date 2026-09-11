<?php
$slug = isset($_GET['slug']) ? preg_replace('/[^a-z-]/', '', strtolower($_GET['slug'])) : '';
$pages = [
    'terms' => [
        'title' => 'Terms & Conditions',
        'body' => "Welcome to BLACK X MARKET. By using this website you agree to these terms.\n\n1. All products sold are digital goods and are delivered electronically after payment verification.\n2. You must be authorized to purchase and use any product you buy.\n3. Unauthorized accounts, stolen credentials, payment-card data and illegally obtained goods are strictly prohibited.\n4. Payments are verified manually. Orders may be approved or rejected with a reason.\n5. Delivery details are accessible only to the purchasing account.\n6. Prices, discounts and coupons are validated by the platform and may change without notice.",
    ],
    'privacy' => [
        'title' => 'Privacy Policy',
        'body' => "BLACK X MARKET respects your privacy.\n\n1. We collect only the information required to operate your account and process orders, such as name, email and order history.\n2. Authentication is handled by Firebase Authentication. Payment screenshots are private order information.\n3. We do not sell your personal data.\n4. Delivery details are shown only to the purchasing user and authorized administrators.\n5. You may request account support through the Customer Support page.",
    ],
    'refund' => [
        'title' => 'Refund Policy',
        'body' => "Because digital products are delivered electronically, refunds are evaluated case by case.\n\n1. If a product could not be delivered as described, contact support with your Order ID.\n2. Rejected payments are not charged; if funds were sent, contact support for assistance.\n3. Once delivery details have been accessed, refunds are generally not available.\n4. All refund decisions are made by the BLACK X MARKET team after reviewing the order.",
    ],
];
if (!isset($pages[$slug])) {
    $slug = 'terms';
}
$pageData = $pages[$slug];
$pageTitle = $pageData['title'];
$pageDescription = $pageData['title'] . ' for BLACK X MARKET.';
require_once __DIR__ . '/includes/header.php';
?>
<section class="bxm-section">
  <div class="container">
    <div class="bxm-card p-4 p-md-5" style="max-width:900px;margin:0 auto">
      <h1 class="bxm-section-title mb-4"><?= bxm_e($pageData['title']) ?></h1>
      <div class="text-secondary" style="white-space:pre-line;line-height:1.8"><?= bxm_e($pageData['body']) ?></div>
      <p class="text-muted small mt-4 mb-0">Last updated <?= bxm_e(bxm_site('year')) ?>.</p>
    </div>
  </div>
</section>
<?php
include __DIR__ . '/includes/footer.php';
?>
