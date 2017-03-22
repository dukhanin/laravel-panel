panel = {
    uploadUrl: '/panel/upload',

    trans: {
        buttons: {
            confirm: 'Confirm',
            cancel: 'Cancel'
        },
        confirm: {
            'default': 'Really?'
        },
        responses: {
            success: 'Success!',
            error: 'Error!'
        }
    },

    alert: function (message) {
        message = this.validateMessage(message);

        alert((message.title ? message.title + "\n\n" : "") + message.text)
    },

    error: function (message) {
        message = this.validateMessage(message);

        if (message.title === '') {
            message.title = this.trans.responses.error;
        }

        alert((message.title ? message.title + "\n\n" : "") + message.text)
    },

    confirm: function (message, callback) {
        message = this.validateMessage(message);

        if (message.title === '') {
            message.title = this.trans.confirm.default;
        }

        if (confirm((message.title ? message.title + "\n\n" : "") + message.text)) {
            callback.call();
        }
    },

    go: function (url) {
        document.location = url;
    },

    ajax: function (options) {
        return $.ajax(panel.validateAjaxOptions(options));
    },

    ajaxSuccess: function (jqXHR) {
        panel.handleAjaxMessages(jqXHR);
    },

    ajaxError: function (jqXHR) {
        panel.handleAjaxMessages(jqXHR);
    },

    ajaxComplete: function (data, textStatus, jqXHR) {

    },

    handleAjaxMessages: function (jqXHR) {
        if (jqXHR.status && jqXHR.status != 200) {
            return this.error(jqXHR.statusText);
        }

        var responseJSON = this.validateResponseJSON(jqXHR.responseJSON);

        if (responseJSON.messages.length > 0) {
            var message = {text: responseJSON.messages[0]};
        } else if (responseJSON.error != 0 && responseJSON.error !== null) {
            var message = {text: 'Code ' + responseJSON.error + ' ' + jqXHR.responseText};
        } else {
            var message = {text: ''};
        }

        message.type = responseJSON.success ? 'success' : 'error';
        message = this.validateMessage(message);

        if (!responseJSON.success) {
            this.error(message);
        } else if (message.text) {
            this.alert(message);
        }
    },

    validateData: function (data) {
        for (var key in data) {
            if ($.isPlainObject(data[key])) {
                data[key] = this.validateData(data[key]);
                continue;
            }

            if (typeof data[key] == 'boolean') {
                data[key] = data[key] ? 1 : null;
                continue;
            }
        }

        return data;
    },

    validateMessage: function (message) {
        if (!$.isPlainObject(message)) {
            message = {text: message}
        }

        if (!('title' in message)) {
            message.title = '';
        }

        if (!('text' in message)) {
            message.text = '';
        }

        message.text = this.validateMessageText(message.text);

        return message;
    },

    validateMessageText: function (text) {
        var node = $('<div>' + text + '</div>');

        if (node.find('.exception_message').length > 0) {
            node = node.find('.exception_message');
        }

        var text = node.text()
            .replace(/^\s*[\r\n]/gm, '')
            .replace(/^\s*\n/gm, '')
            .replace(/^[\s\t]*/gm, '')
            .substring(0, 500);

        return $.trim(text);
    },

    validateAjaxOptions: function (options) {
        options = options || {};

        options.dataType = 'json';

        var successCallback, errorCallback, completeCallback;

        if ('function' === typeof options.success) {
            successCallback = options.success;
        }

        if ('function' === typeof options.error) {
            errorCallback = options.error;
        }

        if ('function' === typeof options.complete) {
            completeCallback = options.complete;
        }

        options.data = this.validateData('data' in options ? options.data : {});

        options.error = function (jqXHR, textStatus, errorThrown) {
            if (errorCallback) {
                errorCallback.call(this, jqXHR, textStatus, errorThrown);
            }

            panel.ajaxError.call(this, jqXHR);
        };

        options.success = function (responseJSON, textStatus, jqXHR) {
            responseJSON = panel.validateResponseJSON(jqXHR.responseJSON);

            if (responseJSON.success !== true) {
                options.error.call(this, jqXHR, textStatus, 'JSON backend returned an error');

                return;
            }

            if (successCallback) {
                successCallback.call(this, responseJSON, textStatus, jqXHR);
            }

            panel.ajaxSuccess.call(this, jqXHR);
        };

        options.complete = function (jqXHR, textStatus) {
            if (completeCallback) {
                completeCallback.call(this, response.data === 'undefined' ? {} : response.data, textStatus, jqXHR);
            }

            panel.ajaxComplete.call(this, jqXHR);
        };

        return options;
    },

    validateResponseJSON: function (response) {
        return $.extend({
            error: null,
            success: null,
            messages: [],
            data: {}
        }, $.isPlainObject(response) ? response : {});
    }
};