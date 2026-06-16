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

  'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'GH₵'),

  'delivery_fee' => (float) env('SHOP_DELIVERY_FEE', 30),

  'free_delivery_threshold' => (float) env('SHOP_FREE_DELIVERY_THRESHOLD', 500),

  'tax_rate' => (float) env('SHOP_TAX_RATE', 0),

  'default_country' => env('SHOP_DEFAULT_COUNTRY', 'Ghana'),

  'store_name' => env('SHOP_STORE_NAME', 'SACYSHOES'),

  'contact_email' => env('SHOP_CONTACT_EMAIL', 'admin@sacyshoes.com'),

  'contact_phone' => env('SHOP_CONTACT_PHONE', '+233 30 000 0000'),

  'contact_address' => env('SHOP_CONTACT_ADDRESS', 'Accra, Ghana'),

  'contact_website' => env('SHOP_CONTACT_WEBSITE', env('APP_URL', 'http://localhost')),
];
