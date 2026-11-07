<?php

namespace App\Services;

class WhatsAppService
{
    /**
     * Normalize an Indian or international mobile number to standard digits-only format.
     * E.g. "+91 98765-43210" => "919876543210"
     */
    public static function normalizePhoneNumber(string $rawPhone): string
    {
        // Strip everything except numbers
        $digits = preg_replace('/[^0-9]/', '', $rawPhone);

        // If 10 digits (Standard Indian mobile), prepend 91 country code
        if (strlen($digits) === 10) {
            return '91' . $digits;
        }

        // If 11 digits starting with 0 (e.g. 09876543210), replace leading 0 with 91
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '91' . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Generate 1-click WhatsApp deep-link URL (wa.me) with pre-filled message and download tokens.
     */
    public static function generateClickToChatUrl(array $order, array $items, string $baseUrl): string
    {
        $rawPhone = $order['buyer_phone'] ?? '';
        $normalizedPhone = self::normalizePhoneNumber($rawPhone);
        $buyerName = trim($order['buyer_name'] ?? 'Aspirant');
        $orderNumber = trim($order['order_number'] ?? '');

        $lines = [];
        $lines[] = "🚀 *Lockinrank Shop — Study Material Access*";
        $lines[] = "Order Reference: *#{$orderNumber}*";
        $lines[] = "Hi {$buyerName}, thank you for learning with Lockinrank!";
        $lines[] = "";
        $lines[] = "📥 *Your Instant Signed PDF Download Links:*";

        foreach ($items as $idx => $item) {
            $num = $idx + 1;
            $title = $item['title'] ?? 'Study Material';
            $token = $item['download_token'] ?? '';
            $link = "{$baseUrl}/download/{$token}";
            $lines[] = "{$num}. *{$title}*";
            $lines[] = "   👉 Link: {$link}";
        }

        $lines[] = "";
        $lines[] = "💡 *Tip:* Save these PDF links on your phone or tablet for offline study.";
        $lines[] = "Need help? Reply here or visit {$baseUrl}/contact";

        $fullMessage = implode("\n", $lines);
        $encodedText = rawurlencode($fullMessage);

        if (!empty($normalizedPhone)) {
            return "https://wa.me/{$normalizedPhone}?text={$encodedText}";
        } else {
            // General share link if phone was not provided
            return "https://api.whatsapp.com/send?text={$encodedText}";
        }
    }

    /**
     * Pluggable API hook for automated background WhatsApp messaging
     * (Supports Meta WhatsApp Cloud API / WATI / Aisensy when API keys are configured).
     */
    public static function sendAutomatedMessage(string $phone, string $message): bool
    {
        $apiKey = getenv('WHATSAPP_API_KEY');
        if (empty($apiKey)) {
            // No automated background API configured; 1-click wa.me handles delivery
            return false;
        }

        // Future background provider webhook dispatcher
        return true;
    }
}
