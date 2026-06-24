@extends('layouts.admin')

@section('heading', 'Edit '.$template->name)
@section('subheading', $template->description)

@section('content')
    <form method="POST" action="{{ route('admin.email-templates.update', $template) }}" class="card max-w-3xl space-y-4 p-6">
        @csrf
        @method('PUT')

        <div>
            <label for="subject" class="block text-sm font-medium">Email subject</label>
            <input id="subject" type="text" name="subject" value="{{ old('subject', $template->subject) }}" required class="input-field">
            @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="body" class="block text-sm font-medium">Email body</label>
            <textarea id="body" name="body" rows="18" class="input-field font-mono text-sm">{{ old('body', $template->body) }}</textarea>
            <p class="mt-1 text-xs text-brand-muted">Markdown is supported. Buttons and order summaries are added automatically below your message.</p>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        @if (count($placeholders))
            <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                <p class="text-sm font-medium">Available placeholders</p>
                <p class="mt-2 flex flex-wrap gap-2">
                    @foreach ($placeholders as $placeholder)
                        <code class="rounded bg-white px-2 py-1 text-xs text-brand-muted">{{ '{{'.$placeholder.'}}' }}</code>
                    @endforeach
                </p>
            </div>
        @endif

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Save template</button>
            <a href="{{ route('admin.email-templates.index') }}" class="btn-outline px-4 py-2">Cancel</a>
            <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" class="btn-outline px-4 py-2">Preview</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.email-templates.send-test', $template) }}" class="card mt-6 max-w-3xl space-y-4 p-6">
        @csrf

        <div>
            <h2 class="font-semibold">Send test email</h2>
            <p class="mt-1 text-sm text-brand-muted">
                @if ($template->slug === \App\Models\EmailTemplate::SLUG_PAYMENT_RECEIVED)
                    Sends a sample payment confirmation email with a PDF invoice attachment to your inbox.
                @else
                    Sends a sample email using placeholder data to your inbox.
                @endif
            </p>
        </div>

        <div>
            <label for="recipient" class="block text-sm font-medium">Send to</label>
            <input
                id="recipient"
                type="email"
                name="recipient"
                value="{{ old('recipient', auth()->user()->email) }}"
                required
                class="input-field"
            >
            @error('recipient')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-outline px-4 py-2">
            @if ($template->slug === \App\Models\EmailTemplate::SLUG_PAYMENT_RECEIVED)
                Send test invoice email
            @else
                Send test email
            @endif
        </button>
    </form>
@endsection
