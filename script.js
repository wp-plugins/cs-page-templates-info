jQuery(document).ready(function ($) {

    $('#cs_pti_submit').click(function () {

        var ajax_result = $('#cs_pti_ajax_result');
        var cs_pti_check_list = $('#cs_pti_check_list');
        var checked_items = {checked_items: []};
        cs_pti_check_list.children('li').each(function (index) {
            if ($(this).children('input').is(':checked')) {
                checked_items.checked_items.push($(this).children('input').val());
            }
        });
        console.log(checked_items);

        var data = {
            'action': 'cs_pti_action',
            'cs_pti_request': JSON.stringify(checked_items)
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function (response) {
            ajax_result.html(response);
        });
    });

});