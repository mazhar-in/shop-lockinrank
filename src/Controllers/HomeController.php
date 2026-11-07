<?php

namespace App\Controllers;

use Flight;
use App\Database;

class HomeController
{
    public static function index()
    {
        $db = Database::getConnection();

        $classes = ['All', 'JEE Main & Advanced', 'NEET (Medical)', 'AP Physics (College Board)', 'CBSE Class 11-12', 'CBSE Class 9-10', 'Olympiad & Competitive'];
        $boards = ['All', 'JEE / NTA Pattern', 'NEET / NTA Pattern', 'College Board (AP)', 'CBSE', 'International Baccalaureate (IB)'];
        $subjects = ['All', 'Physics', 'Mathematics', 'Biology', 'Chemistry'];
        $types = ['All', 'Unit Plan', 'Worksheet', 'Printable'];

        $query = trim(Flight::request()->query['q'] ?? '');
        $selectedClass = Flight::request()->query['class'] ?? 'All';
        $selectedBoard = Flight::request()->query['board'] ?? 'All';
        $selectedSubject = Flight::request()->query['subject'] ?? 'All';
        $selectedType = Flight::request()->query['type'] ?? 'All';
        $selectedPrice = Flight::request()->query['price'] ?? 'all';
        $sort = Flight::request()->query['sort'] ?? 'popular';

        $sql = "
            SELECT p.*, s.name as store_name, s.avatar as store_avatar, s.slug as store_slug
            FROM products p
            JOIN stores s ON p.store_id = s.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.standards LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        if ($selectedClass !== 'All' && !empty($selectedClass)) {
            $sql .= " AND (p.grade = ? OR p.grade = 'All Classes')";
            $params[] = $selectedClass;
        }

        if ($selectedBoard !== 'All' && !empty($selectedBoard)) {
            $sql .= " AND (p.board = ? OR p.board = 'All Boards')";
            $params[] = $selectedBoard;
        }

        if ($selectedSubject !== 'All' && !empty($selectedSubject)) {
            $sql .= " AND p.subject = ?";
            $params[] = $selectedSubject;
        }

        if ($selectedType !== 'All' && !empty($selectedType)) {
            $sql .= " AND p.resource_type = ?";
            $params[] = $selectedType;
        }

        if ($selectedPrice === 'free') {
            $sql .= " AND p.is_free = 1";
        } elseif ($selectedPrice === 'under99') {
            $sql .= " AND p.price <= 99.00 AND p.is_free = 0";
        } elseif ($selectedPrice === 'under199') {
            $sql .= " AND p.price > 99.00 AND p.price <= 199.00";
        } elseif ($selectedPrice === 'above199') {
            $sql .= " AND p.price > 199.00";
        }

        switch ($sort) {
            case 'rating':
                $sql .= " ORDER BY p.rating DESC, p.sales_count DESC";
                break;
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY p.id DESC";
                break;
            default: // popular
                $sql .= " ORDER BY p.sales_count DESC, p.rating DESC";
                break;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        Flight::render('home', [
            'classes' => $classes,
            'boards' => $boards,
            'subjects' => $subjects,
            'types' => $types,
            'products' => $products,
            'selectedClass' => $selectedClass,
            'selectedBoard' => $selectedBoard,
            'selectedSubject' => $selectedSubject,
            'selectedType' => $selectedType,
            'selectedPrice' => $selectedPrice,
            'searchQuery' => $query,
            'sort' => $sort
        ]);
    }

    public static function search()
    {
        $db = Database::getConnection();

        $query = Flight::request()->query['q'] ?? '';
        $class = Flight::request()->query['class'] ?? 'All';
        $board = Flight::request()->query['board'] ?? 'All';
        $subject = Flight::request()->query['subject'] ?? 'All';
        $type = Flight::request()->query['type'] ?? 'All';
        $priceFilter = Flight::request()->query['price'] ?? 'all';
        $inkFriendly = Flight::request()->query['ink_friendly'] ?? '';
        $sort = Flight::request()->query['sort'] ?? 'popular';

        $sql = "
            SELECT p.*, s.name as store_name, s.avatar as store_avatar, s.slug as store_slug
            FROM products p
            JOIN stores s ON p.store_id = s.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.standards LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        if ($class !== 'All' && !empty($class)) {
            $sql .= " AND (p.grade = ? OR p.grade = 'All Classes')";
            $params[] = $class;
        }

        if ($board !== 'All' && !empty($board)) {
            $sql .= " AND (p.board = ? OR p.board = 'All Boards')";
            $params[] = $board;
        }

        if ($subject !== 'All' && !empty($subject)) {
            $sql .= " AND p.subject = ?";
            $params[] = $subject;
        }

        if ($type !== 'All' && !empty($type)) {
            $sql .= " AND p.resource_type = ?";
            $params[] = $type;
        }

        if (!empty($inkFriendly)) {
            $sql .= " AND p.is_ink_friendly = 1";
        }

        if ($priceFilter === 'free') {
            $sql .= " AND p.is_free = 1";
        } elseif ($priceFilter === 'under99') {
            $sql .= " AND p.price <= 99.00 AND p.is_free = 0";
        } elseif ($priceFilter === 'under199') {
            $sql .= " AND p.price > 99.00 AND p.price <= 199.00";
        } elseif ($priceFilter === 'above199') {
            $sql .= " AND p.price > 199.00";
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY p.rating DESC, p.review_count DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY p.created_at DESC";
                break;
            case 'popular':
            default:
                $sql .= " ORDER BY p.sales_count DESC";
                break;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        Flight::render('partials/product-grid', [
            'products' => $products
        ]);
    }
}
