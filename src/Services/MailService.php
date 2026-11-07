<?php

namespace App\Services;

class MailService
{
    /**
     * Send automated order confirmation and PDF download links email.
     *
     * @param array $order Order record (order_number, buyer_name, buyer_email, total_amount, etc.)
     * @param array $items List of items with title, file_format, download_token, etc.
     * @return bool
     */
    public static function sendOrderReceipt(array $order, array $items): bool
    {
        $configPath = __DIR__ . '/../../config/mail.php';
        $config = file_exists($configPath) ? require $configPath : [];

        $recipientEmail = trim($order['buyer_email'] ?? '');
        $recipientName = trim($order['buyer_name'] ?? 'Aspirant');

        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("MailService: Invalid recipient email '{$recipientEmail}'");
            return false;
        }

        $orderNumber = $order['order_number'] ?? 'IN-ORD';
        $subject = "Your Study Materials Are Ready! — Order #{$orderNumber} (Lockinrank Shop)";

        $domain = getenv('STORE_DOMAIN') ?: (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'shop.lockinrank.com');
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        if (str_contains($domain, 'localhost')) {
            $baseUrl = "http://{$domain}";
        } else {
            $baseUrl = "https://{$domain}";
        }

        $htmlBody = self::renderReceiptHtml($order, $items, $baseUrl);

        // Attempt sending via Hostinger SMTP
        if (($config['driver'] ?? 'smtp') === 'smtp' && !empty($config['password'])) {
            try {
                return self::sendViaSmtp($config, $recipientEmail, $recipientName, $subject, $htmlBody);
            } catch (\Throwable $e) {
                error_log("MailService SMTP Error: " . $e->getMessage() . " — Falling back to PHP mail()");
            }
        }

        // Fallback to PHP mail()
        return self::sendViaPhpMail($config, $recipientEmail, $recipientName, $subject, $htmlBody);
    }

    /**
     * Send email via direct SSL socket to Hostinger SMTP.
     */
    private static function sendViaSmtp(array $config, string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $host = $config['host'] ?? 'smtp.hostinger.com';
        $port = (int)($config['port'] ?? 465);
        $encryption = $config['encryption'] ?? 'ssl';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $fromEmail = $config['from_address'] ?? 'orders@lockinrank.com';
        $fromName = $config['from_name'] ?? 'Lockinrank Academic Store';

        $transport = ($encryption === 'ssl') ? "ssl://{$host}" : "tcp://{$host}";
        $timeout = 4; // Fast timeout so checkout is never blocked

        $socket = @stream_socket_client("{$transport}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            throw new \Exception("Could not connect to SMTP host {$host}:{$port} ($errno: $errstr)");
        }

        stream_set_timeout($socket, $timeout);

        self::readResponse($socket); // Server greeting

        self::sendCommand($socket, "EHLO " . gethostname());

        if ($encryption === 'tls' && $port === 587) {
            self::sendCommand($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            self::sendCommand($socket, "EHLO " . gethostname());
        }

        self::sendCommand($socket, "AUTH LOGIN");
        self::sendCommand($socket, base64_encode($username));
        self::sendCommand($socket, base64_encode($password));

        self::sendCommand($socket, "MAIL FROM:<{$fromEmail}>");
        self::sendCommand($socket, "RCPT TO:<{$toEmail}>");
        self::sendCommand($socket, "DATA");

        // Compose MIME headers
        $boundary = "----=_Part_" . md5(uniqid());
        $headers = [];
        $headers[] = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>";
        $headers[] = "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "Date: " . date('r');
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        $headers[] = "X-Mailer: Lockinrank-Shop-Mailer/1.0";

        $message = implode("\r\n", $headers) . "\r\n\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $message .= strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody)) . "\r\n\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($htmlBody)) . "\r\n\r\n";
        $message .= "--{$boundary}--\r\n";
        $message .= ".";

        self::sendCommand($socket, $message);
        self::sendCommand($socket, "QUIT");

        fclose($socket);
        return true;
    }

    /**
     * Send email via PHP mail() fallback.
     */
    private static function sendViaPhpMail(array $config, string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $fromEmail = $config['from_address'] ?? 'orders@lockinrank.com';
        $fromName = $config['from_name'] ?? 'Lockinrank Academic Store';

        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "From: {$fromName} <{$fromEmail}>";
        $headers[] = "Reply-To: {$fromEmail}";
        $headers[] = "X-Mailer: PHP/" . phpversion();

        return @mail($toEmail, $subject, $htmlBody, implode("\r\n", $headers));
    }

    private static function sendCommand($socket, string $cmd): string
    {
        fwrite($socket, $cmd . "\r\n");
        return self::readResponse($socket);
    }

    private static function readResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * Generate branded HTML receipt template.
     */
    private static function renderReceiptHtml(array $order, array $items, string $baseUrl): string
    {
        $buyerName = htmlspecialchars($order['buyer_name'] ?? 'Aspirant');
        $orderNumber = htmlspecialchars($order['order_number'] ?? '');
        $paymentMethod = htmlspecialchars($order['payment_method'] ?? 'Online');
        $totalAmount = (float)($order['total_amount'] ?? 0);
        $totalDisplay = $totalAmount <= 0 ? 'FREE (₹0)' : '₹' . number_format($totalAmount, 0);
        $dateStr = date('d M Y, h:i A');

        $itemsHtml = '';
        foreach ($items as $item) {
            $title = htmlspecialchars($item['title'] ?? 'Study Material');
            $format = htmlspecialchars($item['file_format'] ?? 'PDF');
            $token = $item['download_token'] ?? '';
            $downloadUrl = "{$baseUrl}/download/{$token}";

            $itemsHtml .= "
            <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 12px;'>
                <div style='font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px;'>
                    {$title}
                </div>
                <div style='font-size: 13px; color: #64748b; margin-bottom: 12px;'>
                    Format: <strong>{$format} (Instant Signed PDF)</strong> &bull; Personal Study License
                </div>
                <a href='{$downloadUrl}' style='display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px; padding: 10px 18px; border-radius: 6px;'>
                    ⬇ Download Study Material (PDF)
                </a>
            </div>";
        }

        return "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Your Lockinrank Order Receipt</title>
        </head>
        <body style='margin: 0; padding: 20px; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06);'>
                
                <!-- Header Banner -->
                <div style='background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); padding: 28px 24px; text-align: center;'>
                    <div style='color: #6ee7b7; font-size: 12px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;'>
                        ✓ Digital Delivery Confirmed
                    </div>
                    <h1 style='color: #ffffff; font-size: 22px; margin: 0; font-weight: 800;'>
                        Lockinrank Shop
                    </h1>
                    <p style='color: #e0e7ff; font-size: 13px; margin: 6px 0 0 0;'>
                        Premier Competitive Exam Prep &bull; shop.lockinrank.com
                    </p>
                </div>

                <!-- Body Content -->
                <div style='padding: 24px;'>
                    <h2 style='font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px;'>
                        Hi {$buyerName},
                    </h2>
                    <p style='font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 20px;'>
                        Thank you for your study material order. Your downloads are signed, ready, and available for instant access below.
                    </p>

                    <!-- Order Summary Box -->
                    <div style='background: #eef2ff; border-left: 4px solid #4f46e5; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px;'>
                        <div style='color: #312e81; font-weight: 700; margin-bottom: 4px;'>
                            Order Reference: <strong>#{$orderNumber}</strong>
                        </div>
                        <div style='color: #4338ca;'>
                            Date: {$dateStr} &bull; Payment: {$paymentMethod} &bull; Amount: <strong>{$totalDisplay}</strong>
                        </div>
                    </div>

                    <!-- Purchased Resources -->
                    <div style='margin-bottom: 24px;'>
                        <div style='font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;'>
                            Your Downloadable Resources:
                        </div>
                        {$itemsHtml}
                    </div>

                    <!-- Usage Tips -->
                    <div style='border-top: 1px solid #e2e8f0; padding-top: 16px; margin-bottom: 20px; font-size: 12px; color: #64748b; line-height: 1.6;'>
                        <strong>💡 Printing Tip:</strong> These workbooks are formatted in high-contrast ink-friendly layout. You can print them on standard A4 monochrome or color printers with zero formatting loss.
                    </div>

                    <!-- Support Contact -->
                    <div style='background: #f8fafc; border-radius: 8px; padding: 14px; text-align: center; font-size: 13px; color: #475569;'>
                        Questions or need support? Reply to this email or contact us at <a href='mailto:support@lockinrank.com' style='color: #4f46e5; font-weight: 700;'>support@lockinrank.com</a>.
                    </div>
                </div>

                <!-- Footer -->
                <div style='background: #f1f5f9; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    &copy; " . date('Y') . " Lockinrank Shop (shop.lockinrank.com). All rights reserved.
                </div>
            </div>
        </body>
        </html>";
    }
}
