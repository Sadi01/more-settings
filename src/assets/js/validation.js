window.yii.validation = (function ($) {
    var pub = {
        isEmpty: function (value) {
            return value === null || value === undefined || ($.isArray(value) && value.length === 0) || value === '';
        },

        addMessage: function (messages, message, value) {
            messages.push(message.replace(/\{value\}/g, value));
        },

        nationalcode: function (value, messages, options) {
            if (options.skipOnEmpty && pub.isEmpty(value)) {
                return;
            }

            var controlNumber = parseInt(value[9]);
            var sum = 0;
            var rem;
            var i;
            for (i = 0; i < 9; ++i) {
                sum += parseInt(value[i]) * (10 - i);
            }
            rem = sum % 11;

            if (!((rem < 2 && controlNumber == rem) || (rem >= 2 && controlNumber + rem == 11))
                || options.oneNumberRepeatedPattern.test(value)
                || !options.tenDigitsNumberPattern.test(value)
                || value == 0123456789
        )
            {
                pub.addMessage(messages, options.message, value);
            }
        },

        jalalidate: function (value, messages, options) {
            if (options.skipOnEmpty && pub.isEmpty(value)) {
                return;
            }

            if (!options.datePattern.test(value) || !moment(value, 'jYYYY/jMM/jDD').isValid()) {
                pub.addMessage(messages, options.message, value);
            }
        },
    };

    return pub;
})(jQuery);
