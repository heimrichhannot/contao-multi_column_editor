(function ($) {
    MultiColumnEditor = {

        init: function () {
            this.initWidget();
        },
        initWidget: function () {
            var $wrapper = $('.multi-column-editor-wrapper');

            $('body')
                .on('click', '.multi-column-editor .add', function (e) {
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
                            $wrapper.html(response);
                            MultiColumnEditor.initChosen();
                            Stylect.convertSelects();
                        }
                    );
                })
                .on('click', '.multi-column-editor .delete', function (e) {
                    e.preventDefault();

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
                            $wrapper.html(response);
                            MultiColumnEditor.initChosen();
                            Stylect.convertSelects();
                        }
                    );
                });
        }
    };

    $(document).ready(function () {
        MultiColumnEditor.init();
    });

})(jQuery);

(function () {
    window.addEvent('domready', function () {
        MultiColumnEditor.initChosen = function () {
            $$('.multi-column-editor select.tl_chosen').each(function (el) {
                if (typeof el.initialized === 'undefined')
                {
                    el.initialized = $$('#' + el.getAttribute('id')).chosen();
                }
            });
        };
    });
})();