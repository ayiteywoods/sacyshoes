document.addEventListener('alpine:init', () => {
    Alpine.data('productVariantPicker', (
        variants = [],
        sizeOptions = [],
        initialSize = null,
        initialColor = null,
        initialHeel = null,
    ) => ({
        variants,
        sizeOptions,
        selectedSize: initialSize,
        selectedColor: initialColor,
        selectedHeel: initialHeel,
        quantity: 1,
        init() {
            if (this.selectedSize && ! this.isSizeInStock(this.selectedSize)) {
                this.selectedSize = null;
            }

            this.syncQuantityInput();
        },
        normalizeOption(value) {
            return value == null ? '' : String(value).trim().toLowerCase();
        },
        optionEquals(left, right) {
            return this.normalizeOption(left) === this.normalizeOption(right);
        },
        hasHeel(variant) {
            const heel = variant.heel_length ? String(variant.heel_length).trim() : '';

            return heel !== '' && heel.toLowerCase() !== 'flat';
        },
        isSizeInStock(size) {
            return this.variants.some((variant) =>
                this.optionEquals(variant.size, size) && variant.quantity > 0,
            );
        },
        sizeButtonClass(size) {
            if (! this.isSizeInStock(size)) {
                return 'variant-size-option--unavailable cursor-not-allowed border-neutral-900 bg-white text-brand-black';
            }

            return this.optionEquals(this.selectedSize, size)
                ? '!border-brand-red !bg-brand-red !text-white'
                : 'border-neutral-300 bg-white text-brand-black hover:border-brand-red';
        },
        get inStockVariants() {
            return this.variants.filter((variant) => variant.quantity > 0);
        },
        get showHeelSection() {
            return this.inStockVariants.some((variant) => this.hasHeel(variant));
        },
        get availableColors() {
            const seen = new Set();

            return this.inStockVariants
                .filter((variant) => !this.selectedSize || this.optionEquals(variant.size, this.selectedSize))
                .map((variant) => variant.color)
                .filter((color) => {
                    const key = this.normalizeOption(color);

                    if (! key || seen.has(key)) {
                        return false;
                    }

                    seen.add(key);

                    return true;
                });
        },
        get availableHeels() {
            return [...new Set(
                this.inStockVariants
                    .filter((variant) => {
                        if (this.selectedSize && !this.optionEquals(variant.size, this.selectedSize)) {
                            return false;
                        }

                        if (this.selectedColor && !this.optionEquals(variant.color, this.selectedColor)) {
                            return false;
                        }

                        return this.hasHeel(variant);
                    })
                    .map((variant) => variant.heel_length),
            )];
        },
        get matchingVariants() {
            if (!this.selectedSize || !this.selectedColor) {
                return [];
            }

            return this.inStockVariants.filter((variant) =>
                this.optionEquals(variant.size, this.selectedSize)
                && this.optionEquals(variant.color, this.selectedColor),
            );
        },
        get selectedVariant() {
            const candidates = this.matchingVariants;

            if (candidates.length === 0) {
                return null;
            }

            if (candidates.length === 1) {
                return candidates[0];
            }

            if (!this.showHeelSection) {
                return candidates[0] ?? null;
            }

            if (this.selectedHeel) {
                return candidates.find((variant) => this.optionEquals(variant.heel_length, this.selectedHeel)) ?? null;
            }

            if (this.availableHeels.length === 1) {
                return candidates.find((variant) => this.optionEquals(variant.heel_length, this.availableHeels[0])) ?? null;
            }

            const withoutHeel = candidates.filter((variant) => !this.hasHeel(variant));

            if (withoutHeel.length === 1) {
                return withoutHeel[0];
            }

            return null;
        },
        get selectionMessage() {
            if (this.selectedVariant) {
                return null;
            }

            if (this.selectedSize && this.selectedColor && this.availableHeels.length > 1) {
                return 'Multiple heel lengths are available. Please select one to continue.';
            }

            if (this.selectedSize || this.selectedColor || this.selectedHeel) {
                return 'Select your size and color to continue.';
            }

            return this.showHeelSection
                ? 'Choose your size and color. Heel length is optional when only one option matches.'
                : 'Choose your size and color.';
        },
        get maxQuantity() {
            return this.quantityCap;
        },
        get quantityCap() {
            if (this.selectedVariant) {
                return this.selectedVariant.quantity;
            }

            const matches = this.matchingVariants;

            if (matches.length > 0) {
                return Math.max(...matches.map((variant) => variant.quantity));
            }

            return 0;
        },
        get canChangeQuantity() {
            return this.selectedSize && this.selectedColor && this.quantityCap > 0;
        },
        selectSize(size) {
            if (! this.isSizeInStock(size)) {
                return;
            }

            this.selectedSize = size;
            this.selectedColor = null;
            this.selectedHeel = null;

            if (this.availableColors.length === 1) {
                this.selectedColor = this.availableColors[0];
            }

            this.syncQuantityInput();
        },
        onColorChange() {
            this.selectedColor = this.selectedColor || null;
            this.selectedHeel = null;
            this.syncQuantityInput();
        },
        selectColor(color) {
            this.selectedColor = color || null;
            this.selectedHeel = null;
            this.syncQuantityInput();
        },
        selectHeel(heel) {
            this.selectedHeel = this.optionEquals(this.selectedHeel, heel) ? null : heel;
            this.syncQuantityInput();
        },
        adjustQuantity(delta) {
            if (! this.canChangeQuantity) {
                return;
            }

            this.quantity = Math.min(
                Math.max(this.quantity + delta, 1),
                this.quantityCap,
            );
        },
        syncQuantityInput() {
            const submit = document.getElementById('add-to-cart');

            if (! this.canChangeQuantity) {
                this.quantity = 1;
            } else {
                this.quantity = Math.min(this.quantity, this.quantityCap);
            }

            if (submit) {
                submit.disabled = submit.dataset.outOfStock === 'true' || !this.selectedVariant;
            }
        },
    }));
});
