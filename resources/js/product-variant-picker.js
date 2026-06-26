document.addEventListener('alpine:init', () => {
    Alpine.data('productVariantPicker', (
        variants = [],
        initialSize = null,
        initialColor = null,
        initialHeel = null,
    ) => ({
        variants,
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
        get allSizes() {
            const seen = new Set();
            const sizes = [];

            for (const variant of this.variants) {
                const key = this.normalizeOption(variant.size);

                if (! key || seen.has(key)) {
                    continue;
                }

                seen.add(key);
                sizes.push(variant.size);
            }

            return sizes.sort((left, right) => {
                const leftNumber = Number(left);
                const rightNumber = Number(right);

                if (! Number.isNaN(leftNumber) && ! Number.isNaN(rightNumber)) {
                    return leftNumber - rightNumber;
                }

                return String(left).localeCompare(String(right));
            });
        },
        isSizeInStock(size) {
            return this.variants.some((variant) =>
                this.optionEquals(variant.size, size) && variant.quantity > 0,
            );
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
            return this.selectedVariant?.quantity ?? 1;
        },
        get quantity() {
            const input = document.getElementById('quantity');

            return Number(input?.value || 1);
        },
        selectSize(size) {
            if (! this.isSizeInStock(size)) {
                return;
            }

            this.selectedSize = this.optionEquals(this.selectedSize, size) ? null : size;
            this.selectedColor = null;
            this.selectedHeel = null;

            if (this.selectedSize && this.availableColors.length === 1) {
                this.selectedColor = this.availableColors[0];
            }

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
            const input = document.getElementById('quantity');

            if (!input || input.disabled) {
                return;
            }

            const next = Math.min(
                Math.max(Number(input.value || 1) + delta, 1),
                Number(input.max || 1),
            );

            input.value = String(next);
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
