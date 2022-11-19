/**
 * renderSettingsForm plugin
 */
(function ($) {

    $.fn.renderSettingsForm = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.renderSettingsForm');
            return false;
        }
    };

    // Default settings
    var defaults = {
        pjaxContainerId: '#render-form-pjax-container',
        pjaxSettings: {
            timeout: 20000,
            scrollTo: false,
            push: false,
            skipOuterContainers: true,
            url: window.location.href
        },
        loadingText: 'Loading...'
    };

    var events = {
        /**
         * beforeRender event is triggered before rendering the form.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         *
         * If the handler returns a boolean false, it will stop further form rendering after this event.
         */
        beforeRender: 'beforeRender',
        /**
         * afterRender event is triggered after form has been rendered.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         */
        afterRender: 'afterRender',
    };

    var renderSettingsFormData = {};

    // Methods
    var methods = {
        init: function (options) {
            return this.each(function () {
                var $renderSettingsForm = $(this);
                var settings = $.extend({}, defaults, options || {});
                var id = $renderSettingsForm.attr('id');

                if (renderSettingsFormData[id] === undefined) {
                    renderSettingsFormData[id] = {};
                } else {
                    //return;
                }

                renderSettingsFormData[id] = $.extend(renderSettingsFormData[id], {settings: settings});

                var eventParams = {wrapperSelector: id};

                $(renderSettingsFormData[id].settings.formId).closest('form').attr('enctype', 'multipart/form-data');

                $(renderSettingsFormData[id].settings.formId).on('change', eventParams, handleChangeForm);
                $(renderSettingsFormData[id].settings.formId).on('depdrop:change', eventParams, handleChangeForm);
                $(renderSettingsFormData[id].settings.formId).closest('form').on('beforeSubmit', eventParams, handleSubmitForm);
            });
        },
        data: function () {
            var id = $(this).attr('id');
            return renderSettingsFormData[id];
        }
    };

    function handleSubmitForm(params) {
        form = $(this);
        var settings = renderSettingsFormData[params.data.wrapperSelector].settings;
        var submitBtn = form.find(':submit');
        var submitBtnOldHtml = submitBtn.html();
        var fd = new FormData(form);

        $.each($('input:file'), function (key, input) {
            fd.append(input.name, input.files[0]);
        });

        $.each(form.serializeArray(), function (key, input) {
            fd.append(input.name, input.value);
        });

        submitBtn.attr('disabled', true).text(settings.loadingText);

        $.ajax({
            url: form.attr('action'),
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (data) {
                if (data !== null && typeof data === 'object') {// errors handling
                    form.yiiActiveForm('updateMessages', data, true);
                } else {
                    // redirect to index view after save
                    window.location.replace(window.location.href.substring(0, window.location.href.lastIndexOf("/")));
                }
            }
        }).fail(function (xhr, status, error) {
            submitBtn.attr('disabled', false).text(submitBtnOldHtml);
            swal({
                title: xhr.responseText,
                type: "error",
                confirmButtonText: '<i class="fa fa-thumbs-up font-22"></i>',
            });
        }).then(function (result) {
            submitBtn.attr('disabled', false).text(submitBtnOldHtml);
        });

        return false;
    }

    function handleChangeForm(params) {
        form = $(this).closest('form');
        var settings = renderSettingsFormData[params.data.wrapperSelector].settings;
        var pjaxSettings = $.extend(settings.pjaxSettings, {container: settings.pjaxContainerId});
        var selectedFormId = parseInt($(settings.formId).val(), 10) || '';
        form.yiiActiveForm('resetForm');

        pjaxSettings.url = settings.renderSettingsFormUrl ? settings.renderSettingsFormUrl : window.location.href;
        pjaxSettings.url += '&id=' + selectedFormId;

        $(pjaxSettings.container).off('pjax:error');
        $(pjaxSettings.container).on('pjax:error', function (event, xhr, textStatus, error, options) {
            swal({
                title: xhr.responseText,
                type: "error",
                confirmButtonText: '<i class="fa fa-thumbs-up font-22"></i>',
            });
            return false;
        });

        $(pjaxSettings.container).off('pjax:beforeSend');
        $(pjaxSettings.container).on('pjax:beforeSend', function (event, xhr, textStatus, error, options) {
            var event = $.Event(events.beforeRender);
            form.trigger(event);
            if (event.result === false) {
                return false;
            } else {
                form.yiiActiveForm('data').attributes = jQuery.grep(form.yiiActiveForm('data').attributes, function (attribute) {
                    return attribute.name.indexOf('extra_field_') === -1;
                });
            }
        });


        $(pjaxSettings.container).off('pjax:complete');
        $(pjaxSettings.container).on('pjax:complete', function (event, xhr, textStatus, error, options) {
            form.trigger($.Event(events.afterRender));
        });

        $.pjax(pjaxSettings);
    }
})
(window.jQuery);