<?php

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(EmailTemplateService::class)->syncDefaultContent(EmailTemplate::SLUG_PAYMENT_RECEIVED);
    }

    public function down(): void
    {
        // Template content is managed in admin; no rollback needed.
    }
};
