MappedRepairEvents.RowMarker = {

    init: () => {
        const rowMarkerCheckboxSelector = 'input.row-marker[type="checkbox"]';

        $('input#row-marker-all').on('click', function () {
            let row;
            if (this.checked) {
                row = $(rowMarkerCheckboxSelector + ':not(:checked):not(:disabled)');
                if (row.closest('tr').css('display') != 'none') {
                    row.prop('checked', true);
                    row.closest('tr').addClass('selected');
                }
            } else {
                row = $(rowMarkerCheckboxSelector + ':checked');
                row.prop('checked', false);
                row.closest('tr').removeClass('selected');
            }
        });

        // change color of row on click of checkbox
        $('table.list').find(rowMarkerCheckboxSelector).on('click', function () {
            const row = $(this).closest('tr');
            if (row.hasClass('selected')) {
                row.removeClass('selected');
            } else {
                row.addClass('selected');
            }
        });
    
    },

};