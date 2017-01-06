panel.list = function (node) {
    this.node                  = $(node);
    this.rows                  = this.node.find('tbody:not(.panel-list-empty)>tr');
    this.dataCells             = this.rows.find('>td:not(.panel-list-empty, .panel-list-checkbox, .panel-list-sort, .panel-list-model-action)');
    this.checkboxes            = this.rows.find('tbody .panel-list-checkbox input');
    this.checkboxesCheckAll    = this.node.find('thead .panel-list-checkbox input');
    this.lastClickedRow        = null;
    this.lastClickedRowChecked = null;
    this.actionsButtons        = this.node.find('.panel-list-action');
    this.groupActionsButtons   = this.node.find('.panel-list-group-action');
    this.modelActionsButtons   = this.node.find('.panel-list-model-action a, .panel-list-model-action button');
    this.categoriesSelect      = this.node.find('.panel-list-categories-select');
    this.moveToSelect          = this.node.find('.panel-list-move-to-select');
};

panel.list.prototype.init = function () {
    this.initActions();
    this.initGroupActions();
    this.initModelActions();
    this.initCheckboxes();
    this.initCategories();
    this.initMoveTo();
    this.initDoubleClick();

    console.log('panel.list.init');
};

panel.list.prototype.initActions = function () {

};

panel.list.prototype.initGroupActions = function () {
    this.groupActionsButtons.click(function (e) {
        var button  = $(this),
            confirm = button.data('confirm'),
            url     = button.data('url'),
            form    = button.closest('form');

        var callback = function () {
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
        var button  = $(this),
            confirm = button.data('confirm'),
            url     = button.attr('href');

        var callback = function () {
            panel.go()
        };

        if (confirm) {
            e.preventDefault();

            panel.confirm(confirm, function () {
                panel.go(url)
            });
        }
    });
};

panel.list.prototype.initCategories = function () {
    this.categoriesSelect.change(function (e) {
        var select = $(this);
        var url    = select.data('url');

        panel.go(url.replace('dummyCategory', select.val()));
        e.preventDefault();
    });
};

panel.list.prototype.initMoveTo = function () {
    this.moveToSelect.change(function (e) {
        var select = $(this);

        if (select.val()) {
            var url     = select.data('url'),
                confirm = select.data('confirm'),
                form    = select.closest('form');

            var callback = function () {
                form.attr('action', url.replace('dummyMoveTo', select.val()));
                form.submit();
            };

            panel.confirm(confirm, callback);
        }

        e.preventDefault();
    });
};

panel.list.prototype.initCheckboxes = function () {
    if (this.checkboxes.size() == 0) {
        return;
    }

    this.checkboxes.on('click', $.proxy(function (e) {
        var tr = $(e.target).closest('tr');

        this.toggleRow(tr, e.target.checked);
        this.updateCheckboxesCheckAll();

        this.lastClickedRow        = tr;
        this.lastClickedRowChecked = e.target.checked;
    }, this));

    this.checkboxesCheckAll.on('click', $.proxy(function (e) {
        this.rows.each($.proxy(function (index, tr) {
            this.toggleRow(tr, e.target.checked);
        }, this));

        this.updateCheckboxesCheckAll();
    }, this));

    this.dataCells.on('mousedown', $.proxy(function (e) {
        var row        = $(e.target).closest('tr');
        var rowChecked = this.toggleRow.call(this, row);

        if (e.shiftKey && this.lastClickedRow) {
            var lastClickedRowindex = this.rows.index(this.lastClickedRow);
            var currentRowIndex     = this.rows.index(row);

            this.rows.slice(lastClickedRowindex, currentRowIndex).each($.proxy(function (index, row) {
                this.toggleRow.call(this, row, rowChecked);
            }, this));
        }

        this.updateCheckboxesCheckAll();

        this.lastClickedRow        = row;
        this.lastClickedRowChecked = rowChecked;
    }, this));

    this.dataCells.on('mouseenter', $.proxy(function (e) {
        if (e.buttons == 1 || e.buttons == 3) {
            this.toggleRow.call(this, e.target, this.lastClickedRowChecked);
            this.updateCheckboxesCheckAll();
        }
    }, this));
};

panel.list.prototype.initDoubleClick = function () {
    this.rows.on('dblclick', function () {
        var row            = $(this);
        var actionSelector = row.data('dblclick');

        if (actionSelector) {
            var url = row.find(actionSelector).attr('href');
            url && panel.go(url);
        }
    });

};

panel.list.prototype.toggleRow = function (target, toggle) {
    var tr    = $(target).closest('tr');
    var input = tr.find('td.panel-list-checkbox input');

    if ('undefined' === typeof toggle) {
        toggle = !input.prop('checked');
    }

    tr.toggleClass('bg-warning', toggle);
    input.prop('checked', toggle);

    return toggle;
};


panel.list.prototype.updateCheckboxesCheckAll = function () {
    var isAnyChecked   = this.checkboxes.filter(':checked').size() > 0;
    var isAnyUnchecked = this.checkboxes.filter(':not(:checked)').size() > 0;

    this.checkboxesCheckAll.prop('checked', isAnyChecked && !isAnyUnchecked);
};