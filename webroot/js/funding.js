MappedRepairEvents.Funding = {

    init: () => {
        $('#fundingForm .input.required').find('input, textarea').each(function() {
            if ($(this).val() === '') {
                $(this).closest('.input').addClass('is-missing');
            }
        });
    },

    onClickHandler: (fieldName, checked) => {
        const fieldNameInputField = $(`#${fieldName}`);
        const wrapper = fieldNameInputField.closest('.input');
        wrapper.toggleClass('is-pending', !checked);
        wrapper.toggleClass('is-verified', checked);
        fieldNameInputField.prop('readonly', checked);
    },

    bindDeleteButton: (uid) => {
        $('#delete-button').on('click', function() {
            $.prompt('Möchtest du diesen Förderantrag wirklich löschen?',
                {
                    buttons: {L\u00f6schen: true, Abbrechen: false},
                    submit: function(v,m,f) {
                        if(m) {
                            document.location.href = '/foerderantrag/delete/' + uid;
                        }
                    }
                }
            );
        });
    },

    updateProgressBar: (parsedIsVerifiedData, totalFieldsCount) => {
        const verifiedDataLength = parsedIsVerifiedData === null ? 0 : parsedIsVerifiedData.length;
        const progressInPercent = verifiedDataLength / totalFieldsCount * 100;
        const progressWrapper = $('#fundingForm .progress-wrapper');
        $( "#progress-bar" ).progressbar({value: progressInPercent});
        if (progressInPercent === 100) {
            progressWrapper.find('p').text('Fortschritt: Alle Felder sind bestätigt, du kannst den Förderantrag jetzt einreichen.');
        } else {
            progressWrapper.find('.verified-count').text(verifiedDataLength);
        }
    },

    initIsVerified: (isVerifiedData, totalFieldsCount) => {

        const parsedIsVerifiedData = JSON.parse(isVerifiedData);
        MappedRepairEvents.Funding.updateProgressBar(parsedIsVerifiedData, totalFieldsCount);

        $('#fundingForm .input:not(.is-missing)').find('input:text, input:checkbox, input[type="email"],  input[type="tel"], textarea').each(function() {
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
