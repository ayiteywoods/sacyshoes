<p @class([
    'text-center sm:text-left',
    'text-neutral-500' => $isDark,
    'text-brand-muted' => ! $isDark,
])>
    &copy; {{ date('Y') }} SACYSHOES. All rights reserved.
</p>

<p @class([
    'inline-flex items-center justify-center gap-1.5 text-center',
    'text-neutral-400' => $isDark,
    'text-brand-muted' => ! $isDark,
])>
    <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
    </svg>
    <span>Payment secured by Paystack</span>
</p>

<p @class([
    'inline-flex items-center justify-center gap-1.5 text-center sm:justify-end sm:text-right',
    'text-neutral-400' => $isDark,
    'text-brand-muted' => ! $isDark,
])>
    <svg @class([
        'h-3.5 w-3.5',
        'text-neutral-300' => $isDark,
        'text-brand-black' => ! $isDark,
    ]) fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/>
    </svg>
    <span>Powered by EdenLabs</span>
</p>
