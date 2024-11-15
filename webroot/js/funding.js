MappedRepairEvents.Funding = {

    init: () => {
        $('#fundingForm .input.required').find('input, textarea').each(function() {
            if ($(this).val() === '') {
                $(this).closest('.input').addClass('is-missing');
            }
        });
    },

    onClickHandlerVerifiedCheckbox: (fieldName, checked) => {
        const fieldNameInputField = $(`#${fieldName}`);
        const wrapper = fieldNameInputField.closest('.input');
        wrapper.toggleClass('is-pending', !checked);
        wrapper.toggleClass('is-verified', checked);
        fieldNameInputField.prop('readonly', checked);
    },

    onClickHandlerDeleteCheckbox: () => {
        const deleteUploadCheckboxes = $('input[name="Fundings[delete_fundinguploads][]"]');
        const deleteUploadCheckboxesChecked = deleteUploadCheckboxes.is(':checked');
        const uploadInput = $('#fundings-files-fundinguploads');
        uploadInput.prop('disabled', deleteUploadCheckboxesChecked);
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

    initBindDeleteFundinguploads: () => {
        $('#fundingForm .input').find('input.is-upload').each(function() {
            const fieldName = $(this).attr('id');
            const fileuploadIdField = fieldName.replace('filename', 'id');
            const fileuploadId = $(`#${fileuploadIdField}`).val();

            const checkbox = $('<input>', {
                type: 'checkbox',
                value: fileuploadId,
                class: 'is-upload',
                name: 'Fundings[delete_fundinguploads][]',
                checked: false,
                on: {
                    change: (e) => MappedRepairEvents.Funding.onClickHandlerDeleteCheckbox(fieldName, e.target.checked),
                }
            });

            const label = $('<label>', {
                class: 'checkbox delete-upload no-required',
                text: 'löschen?'
            }).append(checkbox);

            label.appendTo($(this).closest('.input'));
        });
    },

    initIsVerified: (isVerifiedData, totalFieldsCount) => {

        const parsedIsVerifiedData = JSON.parse(isVerifiedData);
        MappedRepairEvents.Funding.updateProgressBar(parsedIsVerifiedData, totalFieldsCount);

        $('#fundingForm .input:not(.is-missing)').find('input[type="text"]:not(.is-upload), input[type="checkbox"]:not(.is-upload), input[type="email"],  input[type="tel"], textarea').each(function() {

            const fieldName = $(this).attr('id');
            const checked = parsedIsVerifiedData === null ? false : parsedIsVerifiedData.includes(fieldName);

            const checkbox = $('<input>', {
                type: 'checkbox',
                value: fieldName,
                name: 'Fundings[verified_fields][]',
                checked: checked,
                on: {
                    change: (e) => MappedRepairEvents.Funding.onClickHandlerVerifiedCheckbox(fieldName, e.target.checked),
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
