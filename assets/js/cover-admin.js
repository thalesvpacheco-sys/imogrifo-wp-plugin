jQuery(function ($) {
    var frame;

    $('.imo-cover-select').on('click', function (e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: imoCoverI18n.modalTitle,
            button: { text: imoCoverI18n.modalButton },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;

            $('.imo-cover-id-input').val(attachment.id);
            $('.imo-cover-preview img').attr('src', url);
            $('.imo-cover-preview').show();
            $('.imo-cover-select').text(imoCoverI18n.buttonChange);
            $('.imo-cover-remove').show();
        });

        frame.open();
    });

    $('.imo-cover-remove').on('click', function (e) {
        e.preventDefault();
        $('.imo-cover-id-input').val('');
        $('.imo-cover-preview').hide();
        $('.imo-cover-select').text(imoCoverI18n.buttonSelect);
        $(this).hide();
    });
});
