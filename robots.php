<?php
header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "Disallow: /auth/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /firebase/\n";
echo "Disallow: /payment.php\n";
echo "Disallow: /orders.php\n";
echo "Disallow: /order-details.php\n";
echo "Disallow: /profile.php\n";
echo "Disallow: /wishlist.php\n";
echo "Disallow: /notifications.php\n";
