window.checkoutShipping = (
  regions = [],
  initialRegionId = 0,
  initialOptionId = 0,
  subtotal = 0,
  tax = 0,
  initialSameAsShipping = true,
  discount = 0,
) => ({
  regions,
  regionId: Number(initialRegionId || 0),
  optionId: Number(initialOptionId || 0),
  subtotal: Number(subtotal || 0),
  tax: Number(tax || 0),
  discount: Number(discount || 0),
  sameAsShipping: Boolean(initialSameAsShipping),

  init() {
    this.onRegionChange(false);
    this.recalculateTotals();
  },

  syncBillingFromShipping() {
    if (!this.sameAsShipping) {
      return;
    }

    ['full_name', 'phone', 'email', 'address', 'city', 'country'].forEach((field) => {
      const shipping = document.getElementById(`shipping_${field}`);
      const billing = document.getElementById(`billing_${field}`);

      if (shipping && billing) {
        billing.value = shipping.value;
      }
    });
  },

  get region() {
    return this.regions.find((r) => r.id === this.regionId) || null;
  },

  get isAccra() {
    return Boolean(this.region?.is_accra);
  },

  get options() {
    return this.region?.options || [];
  },

  get selectedOption() {
    return this.options.find((o) => o.id === this.optionId) || null;
  },

  get deliveryFee() {
    if (!this.region) return 0;
    if (this.isAccra) return 0;
    return Number(this.selectedOption?.price || 0);
  },

  get deliveryLabel() {
    if (!this.region) return '—';
    if (this.isAccra) return 'Pay rider on delivery';
    if (!this.selectedOption) return 'Select option';
    return `${this.currencySymbol()} ${this.deliveryFee.toFixed(2)}`;
  },

  get discountLabel() {
    return `${this.currencySymbol()} ${this.discount.toFixed(2)}`;
  },

  get total() {
    return Number(Math.max(0, this.subtotal - this.discount) + this.tax + this.deliveryFee);
  },

  get totalLabel() {
    return `${this.currencySymbol()} ${this.total.toFixed(2)}`;
  },

  currencySymbol() {
    const el = document.body || document.documentElement;
    return el?.dataset?.currencySymbol || 'GH₵';
  },

  onRegionChange(recalculate = true) {
    if (this.isAccra) {
      this.optionId = 0;
      document.querySelectorAll('input[name="shipping_option_id"]').forEach((input) => {
        input.checked = false;
        input.disabled = true;
      });
    } else {
      document.querySelectorAll('input[name="shipping_option_id"]').forEach((input) => {
        input.disabled = false;
      });
    }

    if (this.isAccra) {
      this.optionId = 0;
    } else if (this.options.length === 1) {
      this.optionId = Number(this.options[0]?.id || 0);
    } else if (!this.selectedOption) {
      this.optionId = 0;
    }

    if (recalculate) {
      this.recalculateTotals();
    }
  },

  recalculateTotals() {
    // Nothing else required; computed getters update the UI.
  },
});
