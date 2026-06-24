document.addEventListener('alpine:init', () => {
  Alpine.data('adminConfirmModal', () => ({
    open: false,
    message: '',
    pendingForm: null,

    init() {
      window.addEventListener('admin-confirm', (event) => {
        this.message = event.detail?.message ?? 'Are you sure?';
        this.pendingForm = event.detail?.form ?? null;
        this.open = true;
      });
    },

    confirm() {
      if (this.pendingForm) {
        this.pendingForm.submit();
      }

      this.close();
    },

    close() {
      this.open = false;
      this.message = '';
      this.pendingForm = null;
    },
  }));
});

window.adminConfirm = (message, form) => {
  window.dispatchEvent(
    new CustomEvent('admin-confirm', {
      detail: { message, form },
    }),
  );
};
