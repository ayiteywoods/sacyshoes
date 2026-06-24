<?php

return [

    /*
  |--------------------------------------------------------------------------
  | Store Currency
  |--------------------------------------------------------------------------
  |
  | Default currency for product prices across the storefront.
  |
  */

    'currency' => env('SHOP_CURRENCY', 'GHS'),

    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', '₵'),

    'delivery_fee' => (float) env('SHOP_DELIVERY_FEE', 30),

    'free_delivery_threshold' => (float) env('SHOP_FREE_DELIVERY_THRESHOLD', 500),

    'tax_rate' => (float) env('SHOP_TAX_RATE', 0),

    'default_country' => env('SHOP_DEFAULT_COUNTRY', 'Ghana'),

    'cart_reservation_minutes' => (int) env('SHOP_CART_RESERVATION_MINUTES', 60),

    'order_payment_timeout_hours' => (int) env('SHOP_ORDER_PAYMENT_TIMEOUT_HOURS', 24),

    'store_name' => env('SHOP_STORE_NAME', "Sacy's"),

    'contact_email' => env('SHOP_CONTACT_EMAIL', 'hello@sacyshoes.com'),

    'contact_page_email' => env('SHOP_CONTACT_PAGE_EMAIL', 'support@sacyshoes.com'),

    'contact_phone' => env('SHOP_CONTACT_PHONE', '+233 530 668 945'),

    'contact_phone_alt' => env('SHOP_CONTACT_PHONE_ALT', '233 530 668 945'),

    'contact_address' => env('SHOP_CONTACT_ADDRESS', 'Dansoman, Dansoman, Greater Accra, Ghana.'),

    'contact_website' => env('SHOP_CONTACT_WEBSITE', env('APP_URL', 'http://localhost')),

    'order_number_padding' => (int) env('SHOP_ORDER_NUMBER_PADDING', 4),

    'order_number_start' => (int) env('SHOP_ORDER_NUMBER_START', 1000),

    'payment_method_label' => env('SHOP_PAYMENT_METHOD_LABEL', 'Mobile Money Or Debit/Credit Cards'),

    'invoice_accra_shipping_note' => 'DELIVERY WITHIN ACCRA, PAY RIDER ON DELIVERY.',

    'maintenance_mode' => false,

    'maintenance_message' => 'We are currently performing scheduled maintenance. Please check back soon.',

    'product_sizes' => ['36', '37', '38', '39', '40', '41', '42'],

    'product_colors' => ['Black', 'Brown', 'Nude', 'White', 'Red', 'Beige', 'Gold'],

    'product_color_swatches' => [
        'Black' => '#1a1a1a',
        'Brown' => '#6b4423',
        'Nude' => '#e3bc9a',
        'White' => '#ffffff',
        'Red' => '#c41e3a',
        'Beige' => '#d4b896',
        'Gold' => '#d4af37',
        'Default' => '#9ca3af',
    ],

    'product_heel_lengths' => ['Flat', '1in', '2in', '3in', '4in'],

    'delivery_info' => [
        'shipping_note' => 'Shipping calculated at checkout.',
        'items' => [
            [
                'icon' => 'truck',
                'text' => 'Delivery within 48 hours in Accra, excluding Sundays. Payment is made directly to the delivery person upon arrival.',
            ],
        ],
    ],
];
