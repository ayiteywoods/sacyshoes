@props(['pickerId', 'pickerConfig'])

<script>
(function () {
    const root = document.getElementById(@json($pickerId));

    if (!root || root.dataset.variantPickerReady === 'true') {
        return;
    }

    root.dataset.variantPickerReady = 'true';

    const config = @json($pickerConfig);
    const variants = config.variants || [];
    const state = {
        selectedSize: config.initialSize || null,
        selectedColor: config.initialColor || null,
        selectedHeel: config.initialHeel || null,
        quantity: 1,
    };

    const els = {
        colorSelect: root.querySelector('[data-variant-color]'),
        sizeOptions: root.querySelector('[data-variant-size-options]'),
        heelSection: root.querySelector('[data-variant-heel-section]'),
        heelButtons: root.querySelector('[data-variant-heel-buttons]'),
        sizeInput: root.querySelector('[data-variant-size-input]'),
        colorInput: root.querySelector('[data-variant-color-input]'),
        heelInput: root.querySelector('[data-variant-heel-input]'),
        message: root.querySelector('[data-variant-message]'),
        quantityInput: root.querySelector('[data-variant-quantity]'),
        submit: root.querySelector('[data-variant-submit]'),
    };

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

    function sizeSlug(size) {
        return String(size).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'size';
    }

    function inStockVariants() {
        return variants.filter((variant) => variant.quantity > 0);
    }

    function isSizeInStockForColor(size, color) {
        return variants.some((variant) =>
            optionEquals(variant.size, size)
            && optionEquals(variant.color, color)
            && variant.quantity > 0,
        );
    }

    function isSizeInStock(size) {
        if (!state.selectedColor) {
            return false;
        }

        return isSizeInStockForColor(size, state.selectedColor);
    }

    function showHeelSection() {
        return inStockVariants().some((variant) => hasHeel(variant));
    }

    function sizesForColor(color) {
        const seen = new Set();

        return variants
            .filter((variant) => optionEquals(variant.color, color))
            .map((variant) => variant.size)
            .filter((size) => {
                const key = normalizeOption(size);

                if (!key || seen.has(key)) {
                    return false;
                }

                seen.add(key);

                return true;
            })
            .sort((left, right) => {
                if (!Number.isNaN(Number(left)) && !Number.isNaN(Number(right))) {
                    return Number(left) - Number(right);
                }

                return String(left).localeCompare(String(right), undefined, { numeric: true, sensitivity: 'base' });
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

        if (!state.selectedColor) {
            return 'Choose a color first.';
        }

        if (!state.selectedSize) {
            return 'Choose your size.';
        }

        if (state.selectedSize && state.selectedColor && availableHeels().length > 1) {
            return 'Multiple heel lengths are available. Please select one to continue.';
        }

        return showHeelSection()
            ? 'Heel length is optional when only one option matches.'
            : '';
    }

    function renderSizeRadios() {
        root.querySelectorAll('[data-variant-size-radio]').forEach((radio) => {
            const size = radio.value;
            const inStock = isSizeInStock(size);
            const selected = optionEquals(state.selectedSize, size);
            const label = radio.nextElementSibling;
            const isActive = inStock && selected;

            radio.disabled = !inStock;
            radio.checked = isActive;

            if (!label) {
                return;
            }

            label.classList.toggle('is-selected', isActive);
            label.style.borderColor = isActive ? '#e10600' : '';
            label.style.backgroundColor = isActive ? '#e10600' : '';
            label.style.color = isActive ? '#ffffff' : '';
        });
    }

    function renderSizes() {
        if (!els.sizeOptions) {
            return;
        }

        els.sizeOptions.innerHTML = '';

        if (!state.selectedColor) {
            const prompt = document.createElement('p');
            prompt.className = 'text-sm text-brand-muted';
            prompt.textContent = 'Select a color to see available sizes.';
            els.sizeOptions.appendChild(prompt);
            state.selectedSize = null;

            return;
        }

        const sizes = sizesForColor(state.selectedColor);

        if (sizes.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-brand-muted';
            empty.textContent = 'No sizes available for this color.';
            els.sizeOptions.appendChild(empty);
            state.selectedSize = null;

            return;
        }

        if (state.selectedSize && !sizes.some((size) => optionEquals(size, state.selectedSize))) {
            state.selectedSize = null;
        }

        sizes.forEach((size) => {
            const inStock = isSizeInStockForColor(size, state.selectedColor);
            const inputId = `${root.id}-${sizeSlug(size)}`;

            if (inStock) {
                const wrap = document.createElement('span');
                wrap.className = 'inline-flex';

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = `${root.id}-size`;
                radio.id = inputId;
                radio.value = size;
                radio.className = 'variant-size-radio peer sr-only';
                radio.setAttribute('data-variant-size-radio', '');

                const label = document.createElement('label');
                label.htmlFor = inputId;
                label.className = 'variant-size-option variant-size-option--available';
                label.textContent = size;

                wrap.appendChild(radio);
                wrap.appendChild(label);
                els.sizeOptions.appendChild(wrap);

                return;
            }

            const unavailable = document.createElement('span');
            unavailable.className = 'variant-size-option variant-size-option--unavailable';
            unavailable.setAttribute('aria-disabled', 'true');
            unavailable.textContent = size;
            els.sizeOptions.appendChild(unavailable);
        });

        renderSizeRadios();
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
        if (els.colorSelect && state.selectedColor) {
            els.colorSelect.value = state.selectedColor;
        }

        renderSizes();
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
            els.submit.disabled = els.submit.dataset.outOfStock === 'true';
        }
    }

    function selectSize(size) {
        if (!state.selectedColor || !isSizeInStock(size)) {
            return;
        }

        state.selectedSize = size;
        state.selectedHeel = null;
        render();
    }

    root.addEventListener('change', (event) => {
        if (event.target.matches('[data-variant-size-radio]')) {
            selectSize(event.target.value);

            root.querySelectorAll('[data-variant-size-radio]').forEach((radio) => {
                const label = radio.nextElementSibling;
                const isActive = radio === event.target;

                if (!label) {
                    return;
                }

                label.classList.toggle('is-selected', isActive);
                label.style.borderColor = isActive ? '#e10600' : '';
                label.style.backgroundColor = isActive ? '#e10600' : '';
                label.style.color = isActive ? '#ffffff' : '';
            });
        }
    });

    root.addEventListener('click', (event) => {
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
        state.selectedSize = null;
        state.selectedHeel = null;
        render();
    });

    if (state.selectedSize && state.selectedColor && !isSizeInStockForColor(state.selectedSize, state.selectedColor)) {
        state.selectedSize = null;
    }

    if (!state.selectedColor) {
        state.selectedSize = null;
    }

    render();
})();
</script>
