MappedRepairEvents.InfoSheet = {

    addCustomHtmlToSelect2EmptyResults : function(selectElement, selectorClass, text) {

        var html = 'Keine Übereinstimmungen gefunden.<br />';
        html += '<a class="button ' + selectorClass.replace(/\./, '') + '" href="javascript:void(0);">';
        html += text + '</a>';
        selectElement.select2({
            language: {
                'noResults': function(){
                    return html;
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });

        // class for button does not yet exist, so use $('body').on-syntax to add click event for button
        $('body').on('click', selectorClass, function() {
            selectElement.val(-1).trigger('change');
            selectElement.select2('close');
        });

    },

    initDependentRadioButtons : function() {
        this.bindDependentRadioButtonOnClick('.defect_found', 'infosheets-defect-found-1', '.defect_found_reason', '.repair_postponed_reason, .no_repair_reason, .no_repair_reason_text_wrapper');
        this.bindDependentRadioButtonOnClick('.defect_found_reason', 'infosheets-defect-found-reason-2', '.repair_postponed_reason', '.no_repair_reason_text_wrapper');
        this.bindDependentRadioButtonOnClick('.defect_found_reason', 'infosheets-defect-found-reason-3', '.no_repair_reason', '.no_repair_reason_text_wrapper');
        this.bindDependentRadioButtonOnClick('.no_repair_reason', 'infosheets-no-repair-reason-10', '.no_repair_reason_text_wrapper');
    },

    bindDependentRadioButtonOnClick : function(wrapperClass, radioButtonId, selectorToShow, selectorToHideIfNotSelected) {
        $(wrapperClass).find('input').on('click', function() {
            if ($(this).attr('id') == radioButtonId) {
                $(selectorToShow).show();
            } else {
                $(selectorToShow).hide();
                $(selectorToHideIfNotSelected).hide();
            }
        });
        // init edit forms
        $(wrapperClass + ':visible').find('input[checked="checked"]').trigger('click');
    }

    ,bindAddDropdownOnChange : function(selectElement, detailSelector) {
        selectElement.on('change', function() {
            var selected = $(this).find(':selected');
            var selectedId = selected.val();
            var detailFormFields = $(this).closest('fieldset').find(detailSelector);
            if (selectedId == -1) {
                detailFormFields.show();
            } else {
                detailFormFields.hide();
            }
        });
        selectElement.trigger('change'); // if dropdown is preselected
    }

    ,bindTogglePowerSupplyOnChange : function(selectElement, detailSelector) {

        var allowedMainCategoryNames = [
            'Elektro Sonstiges',
            'Haushaltsgeräte',
            'Unterhaltungselektronik',
            'Computer',
            'Handy / Smartphone / Tablet'
        ];

        selectElement.on('change', function() {

            var selected = $(this).find(':selected');
            var mainCategoryName;
            if ($(this).attr('id') == 'infosheets-new-subcategory-parent-id') {
                mainCategoryName = selected.text();
                if (selected.val() == '') {
                    return;
                }
            } else {
                mainCategoryName = selected.closest('optgroup').attr('label');
            }

            var detailFormFields = $(this).closest('fieldset').find(detailSelector);
            if ($.inArray(mainCategoryName, allowedMainCategoryNames) !== -1) {
                detailFormFields.show();
            } else {
                detailFormFields.hide();
            }

        });

        selectElement.trigger('change'); // if dropdown is preselected

    }

    ,initSubCategoryDropdown : function(container) {
        var dropdown = $(container);
        this.addCustomHtmlToSelect2EmptyResults(
            dropdown,
            '.select2-add-subcategory-button',
            'Unterkategorie hinzufügen'
        );
        this.bindAddDropdownOnChange(dropdown, '.add-subcategory');
        this.bindTogglePowerSupplyOnChange(dropdown, '.power_supply');
    }

    ,initMainCategoryDropdown : function(container) {
        this.bindTogglePowerSupplyOnChange($(container), '.power_supply');
    }

    ,initBrandDropdown : function(container) {
        var dropdown = $(container);
        this.addCustomHtmlToSelect2EmptyResults(
            dropdown,
            '.select2-add-brand-button',
            'Marke hinzufügen'
        );
        this.bindAddDropdownOnChange(dropdown, '.add-brand');
    }

};