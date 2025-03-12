MappedRepairEvents.RowMarker = {

    init: () => {
        var rowMarkerAll = $('input#row-marker-all').on('click', function () {
            var row;
            if (this.checked) {
                row = $('input.row-marker[type="checkbox"]:not(:checked):not(:disabled)');
                if (row.closest('tr').css('display') != 'none') {
                    row.prop('checked', true);
                    row.closest('tr').addClass('selected');
                }
            } else {
                row = $('input.row-marker[type="checkbox"]:checked');
                row.prop('checked', false);
                row.closest('tr').removeClass('selected');
            }
        });
    
        return rowMarkerAll;
    },

};