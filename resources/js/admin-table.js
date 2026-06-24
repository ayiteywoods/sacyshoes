window.adminTableSelection = function (pageIds = []) {
    const normalizedIds = pageIds.map((id) => String(id));

    return {
        selected: [],
        pageIds: normalizedIds,
        get allSelected() {
            return this.pageIds.length > 0 && this.pageIds.every((id) => this.selected.includes(id));
        },
        get someSelected() {
            return this.pageIds.some((id) => this.selected.includes(id)) && !this.allSelected;
        },
        get canExportInvoices() {
            return this.selected.length > 0;
        },
        appendSelectedToForm(form) {
            form.querySelectorAll('[data-bulk-order-id]').forEach((input) => input.remove());

            this.selected.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = id;
                input.setAttribute('data-bulk-order-id', '');
                form.appendChild(input);
            });
        },
        toggleAll() {
            if (this.allSelected) {
                this.selected = this.selected.filter((id) => !this.pageIds.includes(id));
            } else {
                this.selected = [...new Set([...this.selected, ...this.pageIds])];
            }
        },
        toggle(id) {
            const value = String(id);

            if (this.selected.includes(value)) {
                this.selected = this.selected.filter((item) => item !== value);
            } else {
                this.selected.push(value);
            }
        },
        isSelected(id) {
            return this.selected.includes(String(id));
        },
        clearSelection() {
            this.selected = [];
        },
    };
};
