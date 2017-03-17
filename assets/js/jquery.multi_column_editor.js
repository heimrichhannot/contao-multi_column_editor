(function($)
{
    MultiColumnEditor = {

        init: function()
        {
            this.initWidget();
            this.initActions();
        },
        initWidget: function()
        {
            var $wrapper = $('.multi-column-editor-wrapper');

            $wrapper.each(function()
            {
                $(this).find('input[type="text"], input[type="password"], input[type="email"], select, textarea').each(function()
                {
                    var $input = $(this),
                        style = $input.attr('style'),
                        regExp = new RegExp('width:\\s?(\\d+)px', 'i');

                    if (style && regExp.test(style) && regExp.test(style))
                    {
                        if ($input.siblings('img').length == 1)
                        {
                            var $img = $input.siblings('img');

                            style = style.replace(regExp, 'width: ' + (parseInt(style.replace(regExp, '$1')) + parseInt($img.attr('width'))) + 'px');
                        }

                        // + 10 since margin-right is 10px
                        style = style.replace(regExp, 'width: ' + (parseInt(style.replace(regExp, '$1')) + 10) + 'px');

                        $input.closest('.form-group').attr('style', style);
                    }
                })
            });
        },
        initActions: function()
        {
            var $wrapper = $('.multi-column-editor-wrapper');

            if ($wrapper.length < 1)
            {
                return;
            }

            function doAction($link, action)
            {
                var formData = $link.closest('form').serializeArray(),
                    formDataNew = [],
                    isFrontend = $link.closest('.multi-column-editor-wrapper').find('.multi-column-editor').hasClass('fe');

                // remove FORM_SUBMIT -> no submit callbacks should be fired
                $.each(formData, function(index, item)
                {
                    if (item.name != 'FORM_SUBMIT')
                    {
                        formDataNew.push(item);
                    }
                });

                formData = formDataNew;

                $.merge(formData, [
                    {
                        'name': 'row',
                        'value': $link.closest('.mce-row').data('index')
                    },
                    {
                        'name': 'field',
                        'value': $link.closest('.multi-column-editor').data('field')
                    },
                    {
                        'name': 'table',
                        'value': $link.closest('.multi-column-editor').data('table')
                    }
                ]);

                if (!isFrontend)
                {
                    $.merge(formData, [
                        {
                            'name': 'action',
                            'value': action
                        }
                    ]);
                }

                // console.log(formData);

                $.post(
                    $link.attr('href'),
                    formData,
                    function(response)
                    {
                        if (isFrontend)
                        {

                            $link.closest('.multi-column-editor-wrapper').html(response.result.html);
                        }
                        else
                        {
                            $link.closest('.multi-column-editor-wrapper').html(response);
                            MultiColumnEditor.initChosen();
                            Stylect.convertSelects();
                        }
                    }
                );
            }

            $('body').on('click', '.multi-column-editor .add', function(e)
            {
                var $link = $(this);

                e.preventDefault();

                doAction($link, 'addRow');
            }).on('click', '.multi-column-editor .delete', function(e)
            {
                var $link = $(this);

                e.preventDefault();

                doAction($link, 'deleteRow');
            });
        }
    };

    $(document).ready(function()
    {
        MultiColumnEditor.init();
    });

})(jQuery);

// backend only
(function()
{
    if (typeof window.addEvent === 'function')
    {
        window.addEvent('domready', function()
        {
            MultiColumnEditor.initChosen = function()
            {
                $$('.multi-column-editor select.tl_chosen').each(function(el)
                {
                    if (typeof el.initialized === 'undefined')
                    {
                        el.initialized = $$('#' + el.getAttribute('id')).chosen();
                    }
                });
            };
        });
    }
})();