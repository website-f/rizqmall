<?php

return [
    'tax_rate' => env('RIZQMALL_TAX_RATE', 0.06),
    'shipping' => [
        'standard' => env('RIZQMALL_SHIPPING_STANDARD', 5.00),
        'express' => env('RIZQMALL_SHIPPING_EXPRESS', 15.00),
        'pickup' => env('RIZQMALL_SHIPPING_PICKUP', 0.00),
    ],
];
