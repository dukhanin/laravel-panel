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

        swal(message);
    },

    error: function (message) {
        message = this.validateMessage(message);

        if (message.title === '') {
            message.title = this.trans.responses.error;
        }

        swal(message)
    },

    confirm: function (message, callback) {
        message = this.validateMessage(message);

        message.showCancelButton = true;

        if (message.title === '') {
            message.title = this.trans.confirm.default;
        }

        swal(message, callback);
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
            var message = responseJSON.messages[0];
        } else if (responseJSON.error != 0 && responseJSON.error !== null) {
            var message = 'Code ' + responseJSON.error + ' ' + jqXHR.responseText;
        } else {
            var message = '';
        }

        message = this.validateMessage(message);

        if (responseJSON.success) {
            message.type = 'success';
            !message.text || this.alert(message);
        } else {
            message.type = 'error';
            this.error(message);
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

        if (!('type' in message)) {
            message.type = 'warning';
        }

        if (!('showCancelButton' in message)) {
            message.showCancelButton = false;
        }

        if (!('confirmButtonText' in message)) {
            message.confirmButtonText = this.trans.buttons.confirm;
        }

        if (!('cancelButtonText' in message)) {
            message.cancelButtonText = this.trans.buttons.cancel;
        }

        if (!('closeOnConfirm' in message)) {
            message.closeOnConfirm = true;
        }

        if (!('confirmButtonColor' in message)) {
            message.confirmButtonColor = '#DD6B55';
        }

        message.text = this.validateMessageText(message.text);

        return message;
    },


    validateMessageText: function (text) {
        var node = $('<div>' +  text + '</div>');

        node.find('style').remove();

        return $.trim(node.text().substring(0, 500));
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
                completeCallback.call(this, undefined === jqXHR.responseJSON ? {} : jqXHR.responseJSON, textStatus, jqXHR);
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