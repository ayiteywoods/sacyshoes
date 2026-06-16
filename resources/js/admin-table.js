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
