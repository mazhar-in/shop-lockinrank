<?php

namespace App\Services;

class RazorpayService
{
    private static ?array $config = null;

    public static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/razorpay.php';
        }
        return self::$config;
    }

    public static function getKeyId(): string
    {
        return self::getConfig()['key_id'];
    }

    public static function getKeySecret(): string
    {
        return self::getConfig()['key_secret'];
    }

    /**
     * Create an order on Razorpay servers
     *
     * @param float $amountInRupees Total order amount in INR (e.g. 149.00)
     * @param string $receiptId Unique internal receipt/order identifier
     * @param array $notes Custom metadata notes
     * @return array Razorpay order payload (including order 'id', 'amount', etc.)
     */
    public static function createOrder(float $amountInRupees, string $receiptId, array $notes = []): array
    {
        $config = self::getConfig();
        $amountInPaise = (int)round($amountInRupees * 100);

        $payload = [
            'amount' => $amountInPaise,
            'currency' => $config['currency'] ?? 'INR',
            'receipt' => $receiptId,
            'notes' => $notes
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_USERPWD, $config['key_id'] . ':' . $config['key_secret']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception("Razorpay cURL connection error: " . $curlError);
        }

        $result = json_decode($response, true);
        if ($httpCode !== 200 || !isset($result['id'])) {
            $msg = $result['error']['description'] ?? 'Failed to initialize Razorpay order';
            throw new \Exception("Razorpay API error ($httpCode): " . $msg);
        }

        return $result;
    }

    /**
     * Verify the HMAC SHA256 signature returned by Razorpay Checkout
     *
     * @param string $razorpayOrderId
     * @param string $razorpayPaymentId
     * @param string $razorpaySignature
     * @return bool True if authentic payment verified, false otherwise
     */
    public static function verifyPayment(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        $keySecret = self::getKeySecret();
        $payload = $razorpayOrderId . '|' . $razorpayPaymentId;
        $expectedSignature = hash_hmac('sha256', $payload, $keySecret);

        return hash_equals($expectedSignature, $razorpaySignature);
    }
}
