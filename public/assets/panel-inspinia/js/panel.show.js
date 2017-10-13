panel.show = function (node) {
    this.node = $(node);
};

panel.show.prototype.init = function () {
    this.initDomProperties();
    this.initButtons();

    console.log('panel-show-init');
};

panel.show.prototype.initDomProperties = function () {
    this.buttons = this.node.find('.panel-buttons .btn');
};

panel.show.prototype.initButtons = function () {
    this.buttons.click(function (e) {
        var button = $(this),
            confirm = button.attr('confirm'),
            confirmed = button.data('confirmed');

        if (!confirm || confirmed) {
            return true;
        }

        if (confirm) {
            panel.confirm(confirm, function () {
                button.data('confirmed', 1);
                e.target.click();
            });
        }

        e.preventDefault();
        e.stopPropagation();
    });
};