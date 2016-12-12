(function ($) {
    MultiColumnEditor = {

        init: function () {
            this.initWidget();
        },
        initWidget: function () {
            var $editor = $('.multi-column-editor');

            $editor
                .on('click', '.actions .add', function (e) {
                    var $link = $(this),
                        formData = $(this).closest('form').serializeArray();

                    e.preventDefault();

                    $.merge(formData, [
                        {
                            'name': 'action',
                            'value': 'addRow'
                        }, {
                            'name': 'row',
                            'value': $link.closest('.row').data('index')
                        },
                        {
                            'name': 'field',
                            'value': $link.closest('.multi-column-editor').data('field')
                        }
                    ]);

                    $.post(
                        $link.attr('href'),
                        formData,
                        function (response) {
                            $editor.html(response);
                            MultiColumnEditor.initChosen();
                            Stylect.convertSelects();
                        }
                    );
                })
                .on('click', '.actions .delete', function (e) {
                    console.log('Hallo');

                    e.preventDefault();

                    if ($(this).closest('.rows').children().length > 1) {
                        var $link = $(this),
                            formData = $(this).closest('form').serializeArray();

                        $.merge(formData, [
                            {
                                'name': 'action',
                                'value': 'deleteRow'
                            }, {
                                'name': 'row',
                                'value': $link.closest('.row').data('index')
                            },
                            {
                                'name': 'field',
                                'value': $link.closest('.multi-column-editor').data('field')
                            }
                        ]);

                        $.post(
                            $link.attr('href'),
                            formData,
                            function (response) {
                                $editor.html(response);
                                MultiColumnEditor.initChosen();
                                Stylect.convertSelects();
                            }
                        );
                    }
                });
        }
    };

    $(document).ready(function () {
        MultiColumnEditor.init();
    });

})(jQuery);

(function() {
    window.addEvent('domready', function() {
        MultiColumnEditor.initChosen = function() {
            $$('select.tl_chosen').chosen();
        };
    });
})();