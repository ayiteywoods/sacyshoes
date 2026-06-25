document.addEventListener('alpine:init', () => {
    Alpine.data('productVariantPicker', (
        variants = [],
        colorSwatches = {},
        initialSize = null,
        initialColor = null,
        initialHeel = null,
    ) => ({
        variants,
        colorSwatches,
        selectedSize: initialSize,
        selectedColor: initialColor,
        selectedHeel: initialHeel,
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
        colorSwatch(color) {
            if (this.colorSwatches[color]) {
                return this.colorSwatches[color];
            }

            let hash = 0;

            for (let index = 0; index < color.length; index++) {
                hash = color.charCodeAt(index) + ((hash << 5) - hash);
            }

            const hue = Math.abs(hash) % 360;

            return `hsl(${hue}, 45%, 50%)`;
        },
        get inStockVariants() {
            return this.variants.filter((variant) => variant.quantity > 0);
        },
        get showHeelSection() {
            return this.inStockVariants.some((variant) => this.hasHeel(variant));
        },
        get availableSizes() {
            return [...new Set(this.inStockVariants.map((variant) => variant.size))];
        },
        get availableColors() {
            return [...new Set(
                this.inStockVariants
                    .filter((variant) => !this.selectedSize || this.optionEquals(variant.size, this.selectedSize))
                    .map((variant) => variant.color),
            )];
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
                let message = `${this.selectedVariant.quantity} available for Size ${this.selectedVariant.size}, ${this.selectedVariant.color}`;

                if (this.hasHeel(this.selectedVariant)) {
                    message += `, ${this.selectedVariant.heel_length} heel`;
                }

                return message;
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
            return this.selectedVariant?.quantity ?? 1;
        },
        selectSize(size) {
            this.selectedSize = this.optionEquals(this.selectedSize, size) ? null : size;
            this.selectedColor = null;
            this.selectedHeel = null;

            if (this.selectedSize && this.availableColors.length === 1) {
                this.selectedColor = this.availableColors[0];
            }

            this.syncQuantityInput();
        },
        selectColor(color) {
            this.selectedColor = this.optionEquals(this.selectedColor, color) ? null : color;
            this.selectedHeel = null;
            this.syncQuantityInput();
        },
        selectHeel(heel) {
            this.selectedHeel = this.optionEquals(this.selectedHeel, heel) ? null : heel;
            this.syncQuantityInput();
        },
        syncQuantityInput() {
            const input = document.getElementById('quantity');
            const submit = document.getElementById('add-to-cart');

            if (input) {
                input.max = this.maxQuantity;
                input.value = Math.min(Number(input.value || 1), this.maxQuantity || 1);
                input.disabled = !this.selectedVariant;
            }

            if (submit) {
                submit.disabled = submit.dataset.outOfStock === 'true' || !this.selectedVariant;
            }
        },
    }));
});
