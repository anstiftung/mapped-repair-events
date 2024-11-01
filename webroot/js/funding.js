MappedRepairEvents.Funding = {

    onClickHandler: (fieldName, checked) => {
        const fieldNameInputField = $(`#${fieldName}`);
        const wrapper = fieldNameInputField.closest('.input');
        wrapper.toggleClass('is-verified', checked);
        fieldNameInputField.prop('readonly', checked);
    },

    addIsVerifiedCheckboxToFundingEdit: (isVerifiedData) => {
        const parsedIsVerifiedData = JSON.parse(isVerifiedData);

        $('#fundingForm').find('.input input:text, .input input:checkbox').each(function() {
            const fieldName = $(this).attr('id');
            const checked = parsedIsVerifiedData === null ? false : parsedIsVerifiedData.includes(fieldName);

            const checkbox = $('<input>', {
                type: 'checkbox',
                value: fieldName,
                name: 'Fundings[verified_fields][]',
                checked: checked,
                on: {
                    change: (e) => MappedRepairEvents.Funding.onClickHandler(fieldName, e.target.checked),
                }
            }).trigger('change');

            const label = $('<label>', {
                class: 'verified no-required',
                text: 'verifiziert'
            }).append(checkbox);

            label.appendTo($(this).closest('.input'));
        });
    }
};
