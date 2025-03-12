MappedRepairEvents.RowMarker = {

    init: () => {
        const rowMarkerCheckboxSelector = 'input.row-marker[type="checkbox"]';
        const constRowMarkerAllCheckboxSelector = 'input#row-marker-all';

        $(constRowMarkerAllCheckboxSelector).on('click', function () {
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

        $([rowMarkerCheckboxSelector,constRowMarkerAllCheckboxSelector].join(',')).on('click', function () {
            MappedRepairEvents.RowMarker.updateObjectSelectionActionButtons();
        });

        $('.selectable-action').on('click', function () {

            if ($(this).hasClass('disabled')) {
                return;
            }

            const selectedRows = $('table.list').find(rowMarkerCheckboxSelector + ':checked').closest('tr');
            const selectedIds = selectedRows.map(function () {
                return parseInt($(this).find('td.id').text());
            }).get();

            let newUrl = $(this).data('url') + '?selectedIds=' + selectedIds.join(',');

            $.prompt(
                'Möchtest du die Aktion <b>' + $(this).text() + '</b> wirklich ausführen?<br />Ausgewählte Zeilen: ' + selectedIds.length,
                {
                    buttons: {Ja: true, Abbrechen: false},
                    submit: function(v,m,f) {
                        if(m) {
                            window.location.href = newUrl;
                        }
                    }
                }
            );
            
        });
    
    },

    updateObjectSelectionActionButtons: () => {
        const buttons = $('.selectable-action');
        const anySelected = $('table.list').find('input.row-marker[type="checkbox"]:checked').length > 0;

        buttons.each(function () {
            const button = $(this);
            MappedRepairEvents.Helper.disableButton(button);
            if (anySelected) {
                MappedRepairEvents.Helper.enableButton(button);
            }
        });
    },

};