function normalizeOption(value) {
    return value == null ? '' : String(value).trim().toLowerCase();
}

function optionEquals(left, right) {
    return normalizeOption(left) === normalizeOption(right);
}

function hasHeel(variant) {
    const heel = variant.heel_length ? String(variant.heel_length).trim() : '';

    return heel !== '' && heel.toLowerCase() !== 'flat';
}

function readPickerConfig(root) {
    const configEl = root.querySelector('[data-variant-picker-config]');

    if (configEl?.textContent?.trim()) {
        try {
            return JSON.parse(configEl.textContent);
        } catch (error) {
            console.error('Variant picker: invalid config JSON', error);
        }
    }

    if (root.dataset.config) {
        try {
            return JSON.parse(root.dataset.config);
        } catch (error) {
            console.error('Variant picker: invalid data-config', error);
        }
    }

    return {};
}

function initProductVariantPicker(root) {
    if (root.dataset.variantPickerReady === 'true') {
        return;
    }

    root.dataset.variantPickerReady = 'true';

    const config = readPickerConfig(root);
    const variants = config.variants || [];
    const state = {
        selectedSize: config.initialSize || null,
        selectedColor: config.initialColor || null,
        selectedHeel: config.initialHeel || null,
        quantity: 1,
    };

    const els = {
        colorSelect: root.querySelector('[data-variant-color]'),
        heelSection: root.querySelector('[data-variant-heel-section]'),
        heelButtons: root.querySelector('[data-variant-heel-buttons]'),
        sizeInput: root.querySelector('[data-variant-size-input]'),
        colorInput: root.querySelector('[data-variant-color-input]'),
        heelInput: root.querySelector('[data-variant-heel-input]'),
        message: root.querySelector('[data-variant-message]'),
        quantityInput: root.querySelector('[data-variant-quantity]'),
        submit: root.querySelector('[data-variant-submit]'),
    };

    function inStockVariants() {
        return variants.filter((variant) => variant.quantity > 0);
    }

    function isSizeInStock(size) {
        return variants.some((variant) => optionEquals(variant.size, size) && variant.quantity > 0);
    }

    function showHeelSection() {
        return inStockVariants().some((variant) => hasHeel(variant));
    }

    function availableColors() {
        const seen = new Set();

        return inStockVariants()
            .filter((variant) => !state.selectedSize || optionEquals(variant.size, state.selectedSize))
            .map((variant) => variant.color)
            .filter((color) => {
                const key = normalizeOption(color);

                if (!key || seen.has(key)) {
                    return false;
                }

                seen.add(key);

                return true;
            });
    }

    function availableHeels() {
        return [...new Set(
            inStockVariants()
                .filter((variant) => {
                    if (state.selectedSize && !optionEquals(variant.size, state.selectedSize)) {
                        return false;
                    }

                    if (state.selectedColor && !optionEquals(variant.color, state.selectedColor)) {
                        return false;
                    }

                    return hasHeel(variant);
                })
                .map((variant) => variant.heel_length),
        )];
    }

    function matchingVariants() {
        if (!state.selectedSize || !state.selectedColor) {
            return [];
        }

        return inStockVariants().filter((variant) =>
            optionEquals(variant.size, state.selectedSize)
            && optionEquals(variant.color, state.selectedColor),
        );
    }

    function selectedVariant() {
        const candidates = matchingVariants();

        if (candidates.length === 0) {
            return null;
        }

        if (candidates.length === 1) {
            return candidates[0];
        }

        if (!showHeelSection()) {
            return candidates[0] ?? null;
        }

        if (state.selectedHeel) {
            return candidates.find((variant) => optionEquals(variant.heel_length, state.selectedHeel)) ?? null;
        }

        const heels = availableHeels();

        if (heels.length === 1) {
            return candidates.find((variant) => optionEquals(variant.heel_length, heels[0])) ?? null;
        }

        const withoutHeel = candidates.filter((variant) => !hasHeel(variant));

        if (withoutHeel.length === 1) {
            return withoutHeel[0];
        }

        return null;
    }

    function quantityCap() {
        const variant = selectedVariant();

        if (variant) {
            return variant.quantity;
        }

        const matches = matchingVariants();

        if (matches.length > 0) {
            return Math.max(...matches.map((item) => item.quantity));
        }

        return 0;
    }

    function canChangeQuantity() {
        return Boolean(state.selectedSize && state.selectedColor && quantityCap() > 0);
    }

    function selectionMessage() {
        if (selectedVariant()) {
            return '';
        }

        if (state.selectedSize && state.selectedColor && availableHeels().length > 1) {
            return 'Multiple heel lengths are available. Please select one to continue.';
        }

        if (state.selectedSize || state.selectedColor || state.selectedHeel) {
            return 'Select your size and color to continue.';
        }

        return showHeelSection()
            ? 'Choose your size and color. Heel length is optional when only one option matches.'
            : 'Choose your size and color.';
    }

    function renderSizeButtons() {
        root.querySelectorAll('[data-variant-size]').forEach((button) => {
            const size = button.dataset.variantSize;
            const inStock = isSizeInStock(size);
            const selected = optionEquals(state.selectedSize, size);

            button.disabled = !inStock;
            button.setAttribute('aria-pressed', inStock && selected ? 'true' : 'false');
            button.classList.toggle('is-selected', inStock && selected);
            button.classList.toggle('variant-size-option--unavailable', !inStock);
            button.classList.toggle('variant-size-option--available', inStock);
        });
    }

    function renderColors() {
        if (!els.colorSelect) {
            return;
        }

        const colors = availableColors();
        const current = state.selectedColor && colors.some((color) => optionEquals(color, state.selectedColor))
            ? state.selectedColor
            : '';

        els.colorSelect.innerHTML = '<option value="">Select color</option>';

        colors.forEach((color) => {
            const option = document.createElement('option');
            option.value = color;
            option.textContent = color;

            if (optionEquals(color, current)) {
                option.selected = true;
            }

            els.colorSelect.appendChild(option);
        });

        state.selectedColor = current || null;
    }

    function renderHeels() {
        if (!els.heelSection || !els.heelButtons) {
            return;
        }

        const heels = availableHeels();
        const visible = showHeelSection() && state.selectedSize && state.selectedColor && heels.length > 0;

        els.heelSection.hidden = !visible;
        els.heelButtons.innerHTML = '';

        if (!visible) {
            return;
        }

        heels.forEach((heel) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = heel;
            button.className = optionEquals(state.selectedHeel, heel)
                ? 'variant-heel-option is-selected border px-3 py-2 text-sm transition'
                : 'variant-heel-option border px-3 py-2 text-sm transition border-neutral-300 bg-white text-brand-black hover:border-brand-red';

            button.addEventListener('click', () => {
                state.selectedHeel = optionEquals(state.selectedHeel, heel) ? null : heel;
                render();
            });

            els.heelButtons.appendChild(button);
        });
    }

    function render() {
        renderSizeButtons();
        renderColors();
        renderHeels();

        const variant = selectedVariant();
        const cap = quantityCap();
        const canQty = canChangeQuantity();

        if (!canQty) {
            state.quantity = 1;
        } else {
            state.quantity = Math.min(state.quantity, cap);
        }

        if (els.sizeInput) {
            els.sizeInput.value = variant?.size || state.selectedSize || '';
        }

        if (els.colorInput) {
            els.colorInput.value = variant?.color || state.selectedColor || '';
        }

        if (els.heelInput) {
            els.heelInput.value = variant?.heel_length || state.selectedHeel || '';
        }

        if (els.message) {
            const message = selectionMessage();
            els.message.textContent = message;
            els.message.hidden = message === '';
        }

        if (els.quantityInput) {
            els.quantityInput.value = String(state.quantity);
            els.quantityInput.max = String(cap || 1);
            els.quantityInput.disabled = !canQty;
        }

        const decrease = root.querySelector('[data-variant-quantity-decrease]');
        const increase = root.querySelector('[data-variant-quantity-increase]');

        if (decrease) {
            decrease.disabled = !canQty || state.quantity <= 1;
        }

        if (increase) {
            increase.disabled = !canQty || state.quantity >= cap;
        }

        if (els.submit) {
            els.submit.disabled = els.submit.dataset.outOfStock === 'true' || !variant;
        }
    }

    function selectSize(size) {
        if (!isSizeInStock(size)) {
            return;
        }

        state.selectedSize = size;
        state.selectedColor = null;
        state.selectedHeel = null;

        const colors = availableColors();

        if (colors.length === 1) {
            state.selectedColor = colors[0];
        }

        render();
    }

    root.addEventListener('click', (event) => {
        const sizeButton = event.target.closest('[data-variant-size]');

        if (sizeButton && root.contains(sizeButton)) {
            event.preventDefault();
            selectSize(sizeButton.dataset.variantSize);

            return;
        }

        if (event.target.closest('[data-variant-quantity-decrease]')) {
            event.preventDefault();

            if (!canChangeQuantity() || state.quantity <= 1) {
                return;
            }

            state.quantity -= 1;
            render();

            return;
        }

        if (event.target.closest('[data-variant-quantity-increase]')) {
            event.preventDefault();

            if (!canChangeQuantity() || state.quantity >= quantityCap()) {
                return;
            }

            state.quantity += 1;
            render();
        }
    });

    els.colorSelect?.addEventListener('change', () => {
        state.selectedColor = els.colorSelect.value || null;
        state.selectedHeel = null;
        render();
    });

    if (state.selectedSize && !isSizeInStock(state.selectedSize)) {
        state.selectedSize = null;
    }

    render();
}

function bootProductVariantPickers() {
    document.querySelectorAll('[data-product-variant-picker]').forEach(initProductVariantPicker);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootProductVariantPickers);
} else {
    bootProductVariantPickers();
}
