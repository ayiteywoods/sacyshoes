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
                'footer_group' => Page::FOOTER_CUSTOMER_CARE,
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
We are here to help with orders, sizing, and delivery questions.

Email: support@sacyshoes.com
Phone: 020 000 0000
Hours: Monday – Saturday, 9:00 AM – 6:00 PM

Visit our store or send us a message and we will respond as soon as possible.
TEXT,
            ],
            [
                'slug' => Page::SLUG_PRIVACY,
                'title' => 'Privacy Policy',
                'footer_group' => Page::FOOTER_LEGAL,
                'sort_order' => 1,
                'body' => <<<'TEXT'
SACYSHOES respects your privacy. This policy explains how we collect, use, and protect your personal information.

Information we collect
We collect information you provide when creating an account, placing an order, or contacting us. This may include your name, email address, phone number, delivery address, and payment-related details processed securely through our payment partners.

How we use your information
We use your information to process orders, deliver products, provide customer support, improve our services, and send important updates about your purchases.

Data sharing
We do not sell your personal data. We may share limited information with delivery partners and payment processors only as needed to fulfil your order.

Data security
We use reasonable technical and organisational measures to protect your information. However, no online system is completely secure.

Your rights
You may request access to, correction of, or deletion of your personal data by contacting us.

Contact
For privacy-related questions, email support@sacyshoes.com.

Last updated: June 2026.
TEXT,
            ],
            [
                'slug' => Page::SLUG_TERMS,
                'title' => 'Terms & Conditions',
                'footer_group' => Page::FOOTER_LEGAL,
                'sort_order' => 2,
                'body' => <<<'TEXT'
By using the SACYSHOES website and placing an order, you agree to these Terms & Conditions.

Use of the website
You agree to use this website lawfully and not to misuse our services, attempt unauthorised access, or interfere with site operations.

Orders and pricing
All prices are listed in Ghana Cedis unless stated otherwise. We reserve the right to correct pricing errors and to refuse or cancel orders affected by such errors.

Payment
Orders are confirmed after successful payment through our approved payment providers. We are not responsible for delays caused by payment network issues outside our control.

Delivery
Estimated delivery times are provided as guidance only. Risk of loss passes to you upon delivery to the address provided at checkout.

Returns
Returns are handled according to our Returns Policy. Please review that page for eligibility and process details.

Limitation of liability
To the fullest extent permitted by law, SACYSHOES is not liable for indirect or consequential losses arising from use of the website or products purchased through it.

Changes
We may update these terms from time to time. Continued use of the website after changes are posted constitutes acceptance of the updated terms.

Contact
Questions about these terms can be sent to support@sacyshoes.com.

Last updated: June 2026.
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
