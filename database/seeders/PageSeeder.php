<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => Page::SLUG_ABOUT,
                'title' => 'About Us',
                'footer_group' => null,
                'sort_order' => 0,
                'body' => <<<'TEXT'
SACYSHOES started with a simple idea: make premium footwear accessible to everyone in Ghana without compromising on quality or style.

What began as a passion for well-made shoes has grown into a curated collection spanning sneakers, formal wear, sandals, and boots — chosen for comfort, durability, and everyday versatility.

## Our mission

We believe great shoes should feel as good as they look. That is why we work with trusted suppliers, keep our range thoughtfully curated, and focus on a smooth shopping experience from browse to delivery.

## What we stand for

- **Quality first** — durable materials and careful selection
- **Customer care** — responsive support before and after your order
- **Honest pricing** — clear value with seasonal offers where it matters
- **Local focus** — built for Ghanaian customers, delivered nationwide

Whether you are dressing for work, a night out, or a casual weekend, SACYSHOES is here to help you find the right pair.
TEXT,
            ],
            [
                'slug' => Page::SLUG_DELIVERY,
                'title' => 'Delivery Info',
                'footer_group' => Page::FOOTER_CUSTOMER_CARE,
                'sort_order' => 1,
                'body' => <<<'TEXT'
We deliver across Ghana through trusted courier partners.

Standard delivery: 2–5 business days within Accra and major cities.
Regional delivery: 3–7 business days depending on location.

You will receive SMS or email updates once your order is dispatched. Please ensure your phone number and delivery address are correct at checkout.

For urgent delivery requests, contact our support team before placing your order.
TEXT,
            ],
            [
                'slug' => Page::SLUG_RETURNS,
                'title' => 'Returns Policy',
                'footer_group' => null,
                'sort_order' => 2,
                'body' => <<<'TEXT'
We want you to love your purchase. If something is not right, you may request a return within 7 days of delivery.

Items must be unused, in original packaging, and with proof of purchase. Sale items may have limited return eligibility.

To start a return, contact us with your order number. Once approved, we will guide you through the return or exchange process.

Refunds are processed to the original payment method within 5–10 business days after we receive and inspect the returned item.
TEXT,
            ],
            [
                'slug' => Page::SLUG_CONTACT,
                'title' => 'Contact Us',
                'footer_group' => Page::FOOTER_CUSTOMER_CARE,
                'sort_order' => 3,
                'body' => <<<'TEXT'
We're here to help with orders, sizing, delivery questions, and anything else you need.

Send us a message and our team will respond as soon as possible — typically within one business day.
TEXT,
            ],
            [
                'slug' => Page::SLUG_PRIVACY,
                'title' => 'Privacy Policy',
                'footer_group' => Page::FOOTER_LEGAL,
                'sort_order' => 1,
                'body' => <<<'TEXT'
At **Sacy's Shoes**, we respect your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard the information you provide when shopping with us.

## Information We Collect

When you place an order or contact us, we may collect the following information:

- **Full name**
- **Phone number**
- **Delivery address**
- **Email address** (if provided)
- **Payment information** necessary to process your order

## How We Use Your Information

We use your personal information to:

- **Process and confirm** your orders.
- **Arrange and complete** deliveries.
- **Contact you** regarding your order, delivery, or customer support requests.
- **Improve** our products and services.
- **Send promotional offers or updates**, only if you have agreed to receive them.

## Sharing Your Information

**We value your trust and do not sell, rent, or trade your personal information.**

Your information may only be shared with:

- **Delivery partners** for the purpose of completing your order.
- **Payment service providers** to securely process payments.
- **Authorities** where required by law.

## Data Security

We take reasonable administrative and technical measures to protect your personal information against **unauthorized access, loss, misuse, or disclosure**.

While we strive to keep your information secure, no method of electronic storage or transmission over the internet is completely secure.

## Data Retention

We keep your personal information only for as long as necessary to:

- Process your orders
- Provide customer support
- Comply with legal obligations
- Resolve disputes

## Your Rights

You have the right to:

- **Request access** to the personal information we hold about you.
- **Request correction** of inaccurate or incomplete information.
- **Request deletion** of your personal information where applicable by law.

To make any of these requests, please contact us using the details below.

## Cookies and Online Services

If you visit our website or use our online services, we may use **cookies or similar technologies** to improve your browsing experience and understand how our services are used.

## Changes to This Privacy Policy

**Sacy's Shoes** may update this Privacy Policy from time to time. Any changes will be posted on our platforms with the updated effective date.

## Contact Us

If you have any questions about this Privacy Policy or how your personal information is handled, please contact us:

**Sacy's Shoes**

- **Phone:** +233 530 668 945
- **Email:** support@sacyshoes.com
- **Social media:** See the links in our website footer for our current Instagram, Facebook, and other profiles.
TEXT,
            ],
            [
                'slug' => Page::SLUG_TERMS,
                'title' => 'Terms & Conditions',
                'footer_group' => Page::FOOTER_LEGAL,
                'sort_order' => 2,
                'body' => <<<'TEXT'
Welcome to **Sacy's Shoes**. By shopping with us, you agree to the following **Terms & Conditions**. Please read them carefully before placing your order.

## General

**Sacy's Shoes** specializes in **women's shoes, bags, and accessories**.

We reserve the right to update **prices, policies, and availability** without prior notice.

## Orders & Payment

- Orders are confirmed only after **successful payment** or agreement with our designated payment method.
- We reserve the right to **cancel any order** due to stock unavailability or payment issues.

## Shipping & Delivery

- **Accra deliveries:** 24–48 hours.
- **Outside Accra:** 48–72 hours.
- Delivery times may vary slightly during **peak seasons, public holidays**, or due to unforeseen circumstances.
- Customers are required to provide **accurate delivery details**. Sacy's Shoes will not be liable for failed deliveries caused by incorrect information.

## Returns & Exchanges

We value customer satisfaction and allow exchanges under the following conditions:

### Shoes

- Exchange is only possible within **48 hours after purchase**, and items must be in their **original condition with tags intact**.
- **Accepted reasons:** size too small, size too big, or product fault.
- **Design changes are not allowed.**

### Bags

- Exchanges are only accepted if the item is delivered in a **damaged condition**.
- Customers are encouraged to **inspect bags carefully** before the dispatch rider leaves.

### Accessories

- Accessories **cannot be exchanged or refunded** unless proven faulty on delivery.

## Refunds

- Refunds are **not guaranteed** and will only be considered if an exchange cannot be provided.
- **Shipping and delivery fees are non-refundable.**

## Liability

**Sacy's Shoes** will not be held responsible for damages caused by mishandling after delivery. Our responsibility ends once the product is delivered in good condition and acknowledged by the customer.
TEXT,
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'body' => $page['body'],
                    'footer_group' => $page['footer_group'],
                    'sort_order' => $page['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
