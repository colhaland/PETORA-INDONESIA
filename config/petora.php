<?php

declare(strict_types=1);

return [
    'tax' => [
        'enabled' => false,
        'rate' => 11,
        'price_mode' => 'exclusive',
    ],
    'rounding' => ['mode' => 'rupiah'],
    'receipt' => ['number_prefix' => 'INV'],
    'loyalty' => [
        'earn_per_amount' => 10000,
        'redeem_points' => 100,
        'redeem_value' => 10000,
    ],
    'stock' => [
        'allow_negative' => false,
        'issue_method_default' => 'fifo',
        'low_threshold_default' => 5,
    ],
    'grooming' => [
        'slot_minutes' => 30,
        'buffer_minutes' => 0,
    ],
    'hotel' => [
        'checkout_time' => '12:00',
        'late_fee_per_hour' => 0,
    ],
    'refund' => ['requires_approval' => true],
    'void' => ['requires_approval' => true],
    'cash' => ['variance_tolerance' => 0],
    'security' => [
        'lockout_attempts' => 5,
        'lockout_minutes' => 15,
        'idle_timeout_minutes' => 120,
        'password_history' => 5,
    ],
    'upload' => ['max_mb' => 5],
    'audit' => ['retention_years' => 5],
];
