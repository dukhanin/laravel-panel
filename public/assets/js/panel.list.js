panel.list = function (node) {
    this.node = $(node);
};

panel.list.prototype.init = function () {
    this.initDomProperties();
    this.initParams();
    this.initActions();
    this.initGroupActions();
    this.initModelActions();
    this.initActionsOnHover();
    this.initCheckboxes();
    this.initCategories();
    this.initMoveTo();
    this.initDoubleClick();

    console.log('panel.list.init');
};

panel.list.prototype.initDomProperties = function () {
    this.form                = this.node.find('.panel-list-form');
    this.rows                = this.node.find('tbody:not(.panel-list-empty)>tr');
    this.dataCells           = this.rows.find('.panel-list-data-cell');
    this.checkboxes          = this.rows.find('.panel-list-checkbox input');
    this.checkboxesCheckAll  = this.node.find('thead .panel-list-checkbox input');
    this.actionsButtons      = this.node.find('.panel-list-action');
    this.groupActionsButtons = this.node.find('.panel-list-group-action');
    this.modelActionsButtons = this.node.find('.panel-list-model-action a, .panel-list-model-action button');
    this.categoriesSelect    = this.node.find('.panel-list-categories-select');
    this.moveToSelect        = this.node.find('.panel-list-move-to-select');
};

panel.list.prototype.initParams = function () {
    this.url                        = this.form.attr('action');
    this.lastClickedCheckbox        = null;
    this.lastClickedCheckboxChecked = null;
    this.sortableInProgress         = false;
};

panel.list.prototype.initActions = function () {

};

panel.list.prototype.initGroupActions = function () {
    this.groupActionsButtons.click(function (e) {
        var button   = $(this),
            confirm  = button.data('confirm'),
            url      = button.data('url'),
            form     = button.closest('form'),
            callback = function () {
                form.attr('action', url);
                form.submit();
            };

        if (confirm) {
            panel.confirm(confirm, callback);
        } else {
            callback.call(this);
        }

        e.preventDefault();
    });
};

panel.list.prototype.initModelActions = function () {
    this.modelActionsButtons.click(function (e) {
        var button   = $(this),
            confirm  = button.data('confirm'),
            url      = button.attr('href'),
            callback = function () {
                panel.go(url);
            };

        if (confirm) {
            panel.confirm(confirm, callback);
        } else {
            callback.call(this);
        }

        e.preventDefault();
    });
};

panel.list.prototype.initActionsOnHover = function () {
    $()
        .add(this.actionsButtons)
        .add(this.groupActionsButtons)
        .add(this.modelActionsButtons)
        .filter('[data-icon-on-hover]')
        .on('focusin focusout mouseenter mouseleave', function (e) {
                var node        = $(this),
                    icon        = node.find('i'),
                    iconDefault = node.data('icon'),
                    iconOnHover = node.data('icon-on-hover');

                if (undefined === iconDefault) {
                    node.data('icon', icon.attr('class'));
                }

                icon.removeClass().addClass(e.type === 'focusin' || e.type === 'mouseenter' ? iconOnHover : iconDefault);
            });
};

panel.list.prototype.initCategories = function () {
    this.categoriesSelect.change(function (e) {
        var select = $(this),
            url    = select.data('url');

        panel.go(url.replace('dummyCategory', select.val()));

        e.preventDefault();
    });
};

panel.list.prototype.initMoveTo = function () {
    this.moveToSelect.change(function (e) {
        var select = $(this);

        if (select.val()) {
            var url      = select.data('url'),
                confirm  = select.data('confirm'),
                form     = select.closest('form'),
                callback = function () {
                    form.attr('action', url.replace('dummyMoveTo', select.val()));
                    form.submit();
                };

            panel.confirm(confirm, callback);
        }

        e.preventDefault();
    });
};

panel.list.prototype.initCheckboxes = function () {
    if (this.checkboxes.length == 0) {
        return;
    }

    this.checkboxes.on('change', function (e) {
        $(this).trigger('state-changed', e);
    });

    this.checkboxes.on('state-changed', $.proxy(function (e) {
        var checkbox   = $(e.target),
            row        = checkbox.closest('tr'),
            rowChecked = checkbox.prop('checked');

        row.toggleClass('bg-warning selected', rowChecked);
        this.updateCheckboxesCheckAll();
    }, this));

    this.checkboxesCheckAll.on('change', $.proxy(function (e) {
        this.checkboxes.prop('checked', $(e.target).prop('checked')).trigger('state-changed');
    }, this));

    this.dataCells.on('mousedown', $.proxy(function (e) {
        var checkbox = $(e.target).closest('tr').find('.panel-list-checkbox input'),
            checked  = !checkbox.prop('checked');

        checkbox.prop('checked', checked).trigger('state-changed');

        if (e.shiftKey && this.lastClickedCheckbox) {
            var lastClickedCheckboxIndex = this.checkboxes.index(this.lastClickedCheckbox),
                currentCheckboxIndex     = this.checkboxes.index(checkbox);

            this.checkboxes.slice(lastClickedCheckboxIndex, currentCheckboxIndex).prop('checked', checked).trigger('state-changed');
        }

        this.lastClickedCheckbox        = checkbox;
        this.lastClickedCheckboxChecked = checked;
    }, this));

    this.dataCells.on('mouseenter', $.proxy(function (e) {
        if (this.sortableInProgress || this.lastClickedCheckbox === null) {
            return;
        }

        if (e.buttons == 1 || e.buttons == 3) {
            var checkbox = $(e.target).closest('tr').find('.panel-list-checkbox input');
            checkbox.prop('checked', this.lastClickedCheckboxChecked).trigger('state-changed');
        }
    }, this));
};

panel.list.prototype.initDoubleClick = function () {
    this.dataCells.on('dblclick', function (e) {
        var row            = $(e.target).closest('tr'),
            actionSelector = row.data('dblclick'),
            url            = row.find(actionSelector).attr('href');

        if (actionSelector && url) {
            panel.go(url);
        }
    });
};

panel.list.prototype.updateCheckboxesCheckAll = function () {
    var checked = this.checkboxes.length > 0 && this.checkboxes.length == this.checkboxes.filter(':checked').length;

    this.checkboxesCheckAll.prop('checked', checked);
    this.checkboxesCheckAll.parent().toggleClass('checked', checked);
};