class RowMarker {
    constructor() {
        this.rowMarkerCheckboxSelector = 'input.row-marker[type="checkbox"]';
        this.rowMarkerAllCheckboxSelector = 'input#row-marker-all';
    }

    init() {
        // Handle "select all" checkbox
        $(this.rowMarkerAllCheckboxSelector).on('click', (e) => this.handleSelectAllClick(e.target));

        // Handle individual row checkbox clicks
        $('table.list').find(this.rowMarkerCheckboxSelector).on('click', (e) => {
            const row = $(e.target).closest('tr');
            row.toggleClass('selected');
        });

        // Update action buttons on any checkbox change
        $([this.rowMarkerCheckboxSelector, this.rowMarkerAllCheckboxSelector].join(',')).on('click', 
            () => this.updateObjectSelectionActionButtons());

        // Handle action button clicks
        $('.selectable-action').on('click', (e) => this.handleActionButtonClick(e));
        
        // Store original button labels as data attributes
        $('.selectable-action').each((_, btn) => {
            $(btn).data('original-label', $(btn).text().trim());
        });
    }

    handleSelectAllClick(checkbox) {
        if (checkbox.checked) {
            // Select all visible and enabled checkboxes
            const rows = $(this.rowMarkerCheckboxSelector + ':not(:checked):not(:disabled)');
            rows.each((_, row) => {
                if ($(row).closest('tr').css('display') !== 'none') {
                    $(row).prop('checked', true);
                    $(row).closest('tr').addClass('selected');
                }
            });
        } else {
            // Deselect all checked checkboxes
            const rows = $(this.rowMarkerCheckboxSelector + ':checked');
            rows.prop('checked', false);
            rows.closest('tr').removeClass('selected');
        }
    }

    handleActionButtonClick(event) {
        const button = $(event.target);
        
        // Skip if button is disabled
        if (button.hasClass('disabled')) {
            return;
        }

        // Get selected row IDs
        const selectedRows = $('table.list').find(this.rowMarkerCheckboxSelector + ':checked').closest('tr');
        const selectedIds = selectedRows.map(function() {
            return parseInt($(this).find('td.id').text());
        }).get();

        // Build action URL with selected IDs
        const newUrl = button.data('url') + '?selectedIds=' + selectedIds.join(',');

        // Display confirmation prompt
        $.prompt(
            `Möchtest du die Aktion <b>${this.getOriginalButtonLabel(button)}</b> wirklich ausführen?<br />Ausgewählte Zeilen: ${selectedIds.length}`,
            {
                buttons: {Ja: true, Abbrechen: false},
                submit: function(v,m,f) {
                    if(m) {
                        window.location.href = newUrl;
                    }
                }
            }
        );
    }

    /**
     * Updates action buttons based on selection state
     */
    updateObjectSelectionActionButtons() {
        const buttons = $('.selectable-action');
        const selectedCount = $('table.list').find(this.rowMarkerCheckboxSelector + ':checked').length;
        const anySelected = selectedCount > 0;

        buttons.each((_, element) => {
            const button = $(element);
            
            if (anySelected) {
                MappedRepairEvents.Helper.enableButton(button);
                // Update label with selected count
                button.text(`${this.getOriginalButtonLabel(button)} (${selectedCount})`);
            } else {
                MappedRepairEvents.Helper.disableButton(button);
                // Reset to original label
                button.text(this.getOriginalButtonLabel(button));
            }
        });
    }
    
    /**
     * Gets the original button label without the count suffix
     */
    getOriginalButtonLabel(button) {
        return $(button).data('original-label') || button.text().replace(/ \(\d+\)$/, '');
    }
};

// Initialize when document is ready
$(document).ready(() => {
    window.rowMarker = new RowMarker();
});
