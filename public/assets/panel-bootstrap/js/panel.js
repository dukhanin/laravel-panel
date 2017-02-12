panel = {
    labels:  {
        buttons:   {
            confirm: 'Confirm',
            cancel:  'Cancel'
        },
        confirm:   {
            'default': 'Really?'
        },
        responses: {
            success: 'Success!',
            error:   'Error!'
        }
    }
};

panel.confirm = function (confirmText, callback) {
    if (!confirmText && 'string' === typeof confirmText) {
        confirmText = this.labels.confirm.default;
    }

    if (confirm(confirmText)) {
        callback.call(this);
    }
};

panel.go = function (url) {
    document.location = url;
};
