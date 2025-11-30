// shorthand no-conflict safe document-ready function
jQuery(function ($) {
    // Hook into the "notice-my-class" class we added to the notice, so
    // Only listen to YOUR notices being dismissed
    $(document).on('click', '.notice-cwicly', function () {
        // Make an AJAX call
        // Since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.ajax(ajaxurl,
            {
                type: 'POST',
                data: {
                    action: 'cc_dismissed_notice_handler',
                }
            }).done($(".notice-cwicly").slideUp());
    });
});