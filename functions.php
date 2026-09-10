<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF Token for Secure Forms
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Fetch EC2 Instance Metadata (IMDSv2 Compatible with IMDSv1 Fallback)
 */
function get_ec2_metadata(): array {
    $context = stream_context_create([
        'http' => [
            'timeout' => 1,
            'ignore_errors' => true
        ]
    ]);
    
    // Attempt IMDSv2 Token
    $tokenHeader = @file_get_contents('http://169.254.169.254/latest/api/token', false, stream_context_create([
        'http' => [
            'method' => 'PUT', 
            'header' => "X-aws-ec2-metadata-token-ttl-seconds: 60\r\n", 
            'timeout' => 1
        ]
    ]));

    if ($tokenHeader !== false && !empty($tokenHeader)) {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "X-aws-ec2-metadata-token: {$tokenHeader}\r\n", 
                'timeout' => 1
            ]
        ];
        $ctx = stream_context_create($opts);
        $instance_id = @file_get_contents('http://169.254.169.254/latest/meta-data/instance-id', false, $ctx);
        $az = @file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone', false, $ctx);
    } else {
        // Fallback IMDSv1
        $instance_id = @file_get_contents('http://169.254.169.254/latest/meta-data/instance-id', false, $context);
        $az = @file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone', false, $context);
    }

    return [
        'instance_id' => $instance_id !== false && !empty($instance_id) ? $instance_id : 'LocalHostNode',
        'az'          => $az !== false && !empty($az) ? $az : 'local-az-1'
    ];
}

/**
 * Helper to escape output safely for strings, integers, floats, and null
 */
function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
