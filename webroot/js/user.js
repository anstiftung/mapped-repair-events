MappedRepairEvents.User = {

    addPrivateFieldsToUserEdit : function(data) {

        $('#userProfileForm').find('.input input:text, .input input[type="email"], .input input[type="tel"], .input textarea, .input select, .pseudo-field').each(function() {
            if ($(this).data('private') === undefined) {
                return;
            }
            var fieldName = $(this).attr('id').replace('users-', '');
            $(this).closest('.input').addClass('is-private');
            var checked = '';
            if ($.inArray(fieldName, data) !== -1) {
                checked = ' checked="checked"';
            }

            var labelHtml = '<label class="private no-required">';
            labelHtml += '<input type="checkbox" name="Users[private_as_array][]" value="' + fieldName + '"' + checked + ' />';
            labelHtml += 'privat';
            labelHtml += '</label>';
            $(this).after(labelHtml);
        });

    }

};
