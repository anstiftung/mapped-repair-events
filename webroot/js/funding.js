MappedRepairEvents.Funding = {

    onClickHandler : function(fieldName, checked) {
        let fieldNameInputField = $('#' + fieldName);
        let wrapper = fieldNameInputField.closest('.input');
        if (checked) {
            wrapper.addClass('is-verified');
            fieldNameInputField.prop('readonly', true);
        } else {
            wrapper.removeClass('is-verified');
            fieldNameInputField.prop('readonly', false);
        }
    },

    addIsVerifiedCheckboxToFundingEdit : function(isVerifiedData) {

        let parsedIsVerifiedData = $.parseJSON(isVerifiedData);

        $('#fundingForm').find('.input input:text').each(function() {
            
            let fieldName = $(this).attr('id');
            let checked = $.inArray(fieldName, parsedIsVerifiedData) !== -1;

            let checkbox = $('<input>', {
                type: 'checkbox',
                value: fieldName,
                name: 'Fundings[verified_fields][]',
                checked: checked,
                on: {
                    change: (e) => MappedRepairEvents.Funding.onClickHandler(fieldName, e.target.checked),
                }
            }).trigger('change');

            checkbox = $('<label>', {
                class: 'verified no-required',
                text: 'verifiziert'
            }).append(checkbox);

            checkbox.insertAfter($(this));

        });

    }

};
