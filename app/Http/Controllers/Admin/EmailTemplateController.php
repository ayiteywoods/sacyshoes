<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailTemplateRequest;
use App\Http\Requests\Admin\SendTestEmailRequest;
use App\Models\EmailTemplate;
use App\Services\EmailDispatchService;
use App\Services\EmailTemplateMailFactory;
use App\Services\EmailTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailTemplateController extends Controller
{
    public function index(EmailDispatchService $dispatches): View
    {
        $templates = EmailTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $recentInvoiceEmails = $dispatches->recentInvoiceDispatches();

        return view('admin.email-templates.index', compact('templates', 'recentInvoiceEmails'));
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        return view('admin.email-templates.edit', [
            'template' => $emailTemplate,
            'placeholders' => $emailTemplate->placeholderList(),
        ]);
    }

    public function update(
        EmailTemplateRequest $request,
        EmailTemplate $emailTemplate,
        EmailTemplateService $templates,
    ): RedirectResponse {
        $emailTemplate->update($request->validated());
        $templates->clearCache($emailTemplate->slug);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', $emailTemplate->name.' updated successfully.');
    }

    public function preview(EmailTemplate $emailTemplate, EmailTemplateMailFactory $factory): Response
    {
        return response($factory->make($emailTemplate)->render());
    }

    public function sendTest(
        SendTestEmailRequest $request,
        EmailTemplate $emailTemplate,
        EmailTemplateMailFactory $factory,
        EmailDispatchService $dispatches,
    ): RedirectResponse {
        $recipient = $request->validated('recipient');

        try {
            Mail::to($recipient)->sendNow($factory->make($emailTemplate));

            $dispatches->log(
                slug: $emailTemplate->slug,
                recipient: $recipient,
                isTest: true,
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.email-templates.edit', $emailTemplate)
                ->with('error', 'Test email could not be sent. Check your mail settings and try again.');
        }

        $message = $emailTemplate->slug === EmailTemplate::SLUG_PAYMENT_RECEIVED
            ? 'Test invoice email sent to '.$recipient.'.'
            : 'Test email sent to '.$recipient.'.';

        return redirect()
            ->route('admin.email-templates.edit', $emailTemplate)
            ->with('success', $message);
    }
}
