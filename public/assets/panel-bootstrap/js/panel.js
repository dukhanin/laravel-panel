panel = {};

panel.confirm = function (confirmText, callback) {
    if (!confirmText && 'string' === typeof confirmText) {
        confirmText = 'Confirm current action';
    }

    if (confirm(confirmText)) {
        callback.call(this);
    }
};

panel.go = function (url) {
    document.location = url;
};
