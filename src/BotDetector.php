<?php
declare(strict_types=1);

namespace Softlixx\Tracking;

class BotDetector
{
    private static array $knownScannerUAPatterns = [
        'googleimageproxy'      => 'Google Image Proxy',
        'apple mail'            => 'Apple Mail Privacy Proxy',
        'yahoomailproxy'        => 'Yahoo Mail Proxy',
        'safe links'            => 'Microsoft Defender Safe Links',
        'barracuda'             => 'Barracuda Security Scanner',
        'proofpoint'            => 'Proofpoint Email Protection',
        'mimecast'              => 'Mimecast Security Scanner',
        'bingpreview'           => 'Bing Preview',
        'headlesschrome'        => 'Headless Chrome Automation',
        'curl/'                 => 'cURL CLI tool',
        'python-requests'       => 'Python Requests Library',
        'wget/'                 => 'Wget CLI tool',
        'duckduckgo-faviconer'  => 'DuckDuckGo Proxy',
        'postmanruntime'        => 'Postman API Client',
        'urlfilter'             => 'Corporate URL Filter',
        'trendmicro'            => 'Trend Micro Email Scanner',
        'symantec'              => 'Symantec Mail Security',
        'sophos'                => 'Sophos Mail Scanner',
        'kaspersky'             => 'Kaspersky Lab Scanner',
        'avast'                 => 'Avast Email Shield',
        'facebookexternalhit'   => 'Facebook External Linkbot',
        'twitterbot'            => 'Twitter Linkbot',
        'linkedinbot'           => 'LinkedIn Linkbot',
        'slackbot'              => 'Slack Linkbot',
        'discordbot'            => 'Discord Linkbot',
        'telegrambot'           => 'Telegram Linkbot'
    ];

    /**
     * Inspects incoming request headers, user agent, and IP to detect
     * automated scanners, pre-fetching crawlers, and privacy proxies.
     *
     * @return array [ 'is_automated' => bool, 'reason' => string, 'type' => string ]
     */
    public static function evaluateRequest(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $uaLower = strtolower($userAgent);

        // 1. Detect HEAD or OPTIONS requests (commonly used by link checkers)
        if (in_array(strtoupper($method), ['HEAD', 'OPTIONS'])) {
            return [
                'is_automated' => true,
                'reason' => "Non-GET request method: {$method}",
                'type' => 'automated_scanner'
            ];
        }

        // 2. Check for explicit prefetch / preview headers
        $purpose = strtolower($_SERVER['HTTP_PURPOSE'] ?? $_SERVER['HTTP_X_PURPOSE'] ?? $_SERVER['HTTP_SEC_PURPOSE'] ?? '');
        if (in_array($purpose, ['prefetch', 'preview'])) {
            return [
                'is_automated' => true,
                'reason' => "Browser prefetch header detected: {$purpose}",
                'type' => 'prefetch_scanner'
            ];
        }

        // 3. Match against known security scanners and privacy proxies
        foreach (self::$knownScannerUAPatterns as $needle => $label) {
            if (str_contains($uaLower, $needle)) {
                $isPrivacyProxy = str_contains($needle, 'proxy');
                return [
                    'is_automated' => true,
                    'reason' => "Matched signature: {$label}",
                    'type' => $isPrivacyProxy ? 'privacy_proxy' : 'automated_scanner'
                ];
            }
        }

        // 4. Missing or excessively short User-Agent
        if (empty($userAgent) || strlen($userAgent) < 15) {
            return [
                'is_automated' => true,
                'reason' => 'Missing or unidentifiable User-Agent header',
                'type' => 'automated_scanner'
            ];
        }

        // 5. Apple Mail Privacy Protection (MPP) detection
        // Apple fetches all images asynchronously via their cache servers with generic macOS/iOS Safari UA
        if (str_contains($uaLower, 'mac os x 10_15_7') && str_contains($uaLower, 'version/') && !str_contains($uaLower, 'chrome')) {
            // Check if IP is in Apple's known proxy blocks or ASN
            $ip = self::getClientIp();
            if (self::isAppleRelayIp($ip)) {
                return [
                    'is_automated' => true,
                    'reason' => 'Apple Private Relay / Mail Privacy Protection IP',
                    'type' => 'privacy_proxy'
                ];
            }
        }

        // 6. Classified as Probable Human
        return [
            'is_automated' => false,
            'reason' => 'Standard browser interaction observed',
            'type' => 'human_engagement'
        ];
    }

    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Standard reverse proxy
            'HTTP_X_REAL_IP',        // Nginx
            'REMOTE_ADDR'            // Standard socket
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ipList = explode(',', $_SERVER[$header]);
                $ip = trim($ipList[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private static function isAppleRelayIp(string $ip): bool
    {
        // Apple known iCloud Private Relay / Mail Privacy Proxy ranges (IPv4 CIDR samples)
        $appleSubnets = [
            '17.0.0.0/8',
            '172.224.0.0/12',
            '172.225.0.0/16'
        ];

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false;
        }

        foreach ($appleSubnets as $subnet) {
            [$range, $netmask] = explode('/', $subnet, 2);
            $rangeLong = ip2long($range);
            $mask = ~((1 << (32 - (int)$netmask)) - 1);
            if (($ipLong & $mask) === ($rangeLong & $mask)) {
                return true;
            }
        }

        return false;
    }
}
