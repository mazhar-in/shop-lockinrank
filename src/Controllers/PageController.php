<?php

namespace App\Controllers;

use Flight;

class PageController
{
    public static function terms()
    {
        Flight::render('pages/terms');
    }

    public static function privacy()
    {
        Flight::render('pages/privacy');
    }

    public static function shipping()
    {
        Flight::render('pages/shipping');
    }

    public static function refunds()
    {
        Flight::render('pages/refunds');
    }

    public static function contact()
    {
        Flight::render('pages/contact', [
            'successMessage' => false
        ]);
    }

    public static function submitContact()
    {
        $name = trim(Flight::request()->data['name'] ?? '');
        $email = trim(Flight::request()->data['email'] ?? '');
        $phone = trim(Flight::request()->data['phone'] ?? '');
        $subject = trim(Flight::request()->data['subject'] ?? '');
        $message = trim(Flight::request()->data['message'] ?? '');

        // In production, send email to support@teachshare.in or store inquiry in DB
        Flight::render('pages/contact', [
            'successMessage' => true
        ]);
    }
}
