class RowMarker {
    constructor() {
        this.rowMarkerCheckboxSelector = 'input.row-marker[type="checkbox"]';
        this.rowMarkerAllCheckboxSelector = 'input#row-marker-all';
        this.init();
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
            `Möchtest du die Aktion <b>${button.text()}</b> wirklich ausführen?<br />Ausgewählte Zeilen: ${selectedIds.length}`,
            {
                buttons: {Ja: true, Abbrechen: false},
                submit: (confirmed) => {
                    if (confirmed) {
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
        const anySelected = $('table.list').find(this.rowMarkerCheckboxSelector + ':checked').length > 0;

        buttons.each(function() {
            const button = $(this);
            if (anySelected) {
                MappedRepairEvents.Helper.enableButton(button);
            } else {
                MappedRepairEvents.Helper.disableButton(button);
            }
        });
    }
};
