document.addEventListener('alpine:init', () => {
    Alpine.data('productShare', (config) => ({
        url: config.url,
        title: config.title,
        text: config.text ?? '',
        copied: false,

        async share() {
            const payload = {
                title: this.title,
                text: this.text,
                url: this.url,
            };

            if (navigator.share) {
                try {
                    await navigator.share(payload);

                    return;
                } catch (error) {
                    if (error?.name === 'AbortError') {
                        return;
                    }
                }
            }

            try {
                await navigator.clipboard.writeText(this.url);
                this.copied = true;
                window.setTimeout(() => {
                    this.copied = false;
                }, 2000);
            } catch {
                window.prompt('Copy this link:', this.url);
            }
        },
    }));
});
