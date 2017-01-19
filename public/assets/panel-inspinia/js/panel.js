$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

panel = {
    confirm: function (message, callback) {
        if (typeof message !== 'object') {
            message = {
                text: message
            }
        }

        if ('undefined' === typeof message.title) {
            message.title = 'Really?';
        }

        if ('undefined' === typeof message.text) {
            message.text = '';
        }

        if ('undefined' === typeof message.type) {
            message.type = 'warning';
        }

        if ('undefined' === typeof message.showCancelButton) {
            message.showCancelButton = true;
        }

        if ('undefined' === typeof message.confirmButtonText) {
            message.confirmButtonText = 'Confirm';
        }

        if ('undefined' === typeof message.cancelButtonText) {
            message.cancelButtonText = 'Cancel';
        }

        if ('undefined' === typeof message.closeOnConfirm) {
            message.closeOnConfirm = true;
        }

        if ('undefined' === typeof message.confirmButtonColor) {
            message.confirmButtonColor = '#DD6B55';
        }

        swal(message, callback);
    },

    go: function (url) {
        document.location = url;
    },

    handleResponseMessages: function (response) {
        if (response.status && response.status != 200) {
            return this.error(response);
        }

        var data = response.responseJSON;

        var message = {
            title: null,
            text:  typeof data == 'object' && data.messages.length > 0 ? data.messages[0].substr(0, 500) : null,
            type:  null
        };

        if (typeof data == 'object' && data.success) {
            message.type  = 'success';
            message.title = 'Success!';
        }
        else {
            message.type  = 'error';
            message.title = 'Error';

            if (typeof data == 'object' && data.error) {
                message.title += ' ' + data.error;
            }
        }

        if (message.type === 'error' || message.text) {
            //@todo @dukhanin
            swal(message);
        }
    },

    ajax: function (url, options) {
        if (typeof url === "object") {
            options = url;
            url     = undefined;
        }

        options = options || {};

        var successCallback, errorCallback, completeCallback;

        if (typeof options.success === 'function') {
            successCallback = options.success;
        }

        if (typeof options.error === 'function') {
            errorCallback = options.error;
        }

        if (typeof options.complete === 'function') {
            completeCallback = options.complete;
        }

        if (typeof options.data === 'object') {
            this.validateData(options.data);
        }

        options.dataType = 'json';
        options.error    = function (response, textStatus, jqXHR) {
            if (errorCallback) {
                errorCallback.call(this, typeof response.data === 'undefined' ? {} : response.data, textStatus, jqXHR);
            }

            panel.ajaxError.call(this, response, textStatus, jqXHR);
        };
        options.success  = function (response, textStatus, jqXHR) {
            if (!response.success) {
                options.error.call(this, typeof response.data === 'undefined' ? {} : response.data, textStatus, jqXHR);
                return;
            }

            if (successCallback) {
                successCallback.call(this, response.data === 'undefined' ? {} : response.data, textStatus, jqXHR);
            }

            panel.ajaxSuccess.call(this, response, textStatus, jqXHR);
        };
        options.complete = function (response, textStatus, jqXHR) {
            if (completeCallback) {
                completeCallback.call(this, response.data === 'undefined' ? {} : response.data, textStatus, jqXHR);
            }

            panel.ajaxComplete.call(this, response, textStatus, jqXHR);
        };

        return $.ajax(url, options);
    },

    ajaxSuccess: function (data, textStatus, jqXHR) {
        document.admin.handleResponseMessages(jqXHR);
    },

    ajaxError: function (data, textStatus, jqXHR) {
        document.admin.handleResponseMessages(jqXHR);
    },

    ajaxComplete: function (data, textStatus, jqXHR) {

    },

    validateData: function (data) {
        $.each(data, $.proxy(function (i) {
            if ($.isPlainObject(data[i])) {
                this.validateData(data[i]);
            }
            else if (typeof data[i] == 'boolean') {
                data[i] = data[i] ? 1 : null
            }
        }, this));
    }
};