<?php

namespace App\Controllers;

use Flight;
use App\Database;

class StoreController
{
    public static function show($slug)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM stores WHERE slug = ?");
        $stmt->execute([$slug]);
        $store = $stmt->fetch();

        if (!$store) {
            Flight::notFound();
            return;
        }

        // Get this store's products
        $prodStmt = $db->prepare("
            SELECT p.*, s.name as store_name, s.avatar as store_avatar, s.slug as store_slug
            FROM products p
            JOIN stores s ON p.store_id = s.id
            WHERE p.store_id = ?
            ORDER BY p.sales_count DESC
        ");
        $prodStmt->execute([$store['id']]);
        $products = $prodStmt->fetchAll();

        Flight::render('store', [
            'store' => $store,
            'products' => $products
        ]);
    }
}
