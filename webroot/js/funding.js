MappedRepairEvents.Funding = {

    onClickHandler: (fieldName, checked) => {
        const fieldNameInputField = $(`#${fieldName}`);
        const wrapper = fieldNameInputField.closest('.input');
        wrapper.toggleClass('is-pending', !checked);
        wrapper.toggleClass('is-verified', checked);
        fieldNameInputField.prop('readonly', checked);
    },

    addIsVerifiedCheckboxToFundingEdit: (isVerifiedData) => {
        const parsedIsVerifiedData = JSON.parse(isVerifiedData);

        $('#fundingForm').find('.input input:text, .input input:checkbox, .input input[type="email"], .input input[type="tel"], .input textarea').each(function() {
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
                class: 'verified checkbox no-required',
                text: 'bestätigt?'
            }).append(checkbox);

            label.appendTo($(this).closest('.input'));
        });
    }
};
