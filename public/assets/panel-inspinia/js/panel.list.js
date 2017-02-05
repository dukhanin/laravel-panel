/**
 * @todo split into pieces for each feature (actions/group actions/checkboxes/sort...)
 */

panel.list = function (node) {
    this.node        = $(node);
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
    this.initSortable();

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
    this.actionsButtons.click(function (e) {
        var button   = $(this),
            confirm  = button.data('confirm'),
            blank    = button.attr('target') == '_blank',
            url      = button.attr('href'),
            callback = function () {
                if (blank) {
                    var win = window.open(url, '_blank');
                    win.focus();
                } else {
                    document.location = url;
                }
            };

        if (confirm) {
            panel.confirm(confirm, callback);
        } else {
            callback.call(this);
        }

        e.preventDefault();
    });
};

panel.list.prototype.initGroupActions = function () {
    this.groupActionsButtons.click(function (e) {
        e.preventDefault();

        var button   = $(this),
            confirm  = button.data('confirm'),
            url      = button.data('url'),
            form     = button.closest('form'),
            callback = function () {
                form.attr('action', url);
                form.submit();
            };

        if(!url) {
            return;
        }

        if (confirm) {
            panel.confirm(confirm, callback);
        } else {
            callback.call(this);
        }
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
    if (this.checkboxes.size() == 0) {
        return;
    }

    this.checkboxes.on('ifToggled', $.proxy(function (e) {
        var row        = $(e.target).closest('tr'),
            rowChecked = e.target.checked;

        row.toggleClass('panel-list-selected', rowChecked);
        this.updateCheckboxesCheckAll();
    }, this));

    this.checkboxes.trigger('ifToggled');

    this.checkboxesCheckAll.on('ifToggled', $.proxy(function (e) {
        this.checkboxes.iCheck(e.target.checked ? 'check' : 'uncheck');
    }, this));

    this.dataCells.on('mousedown', $.proxy(function (e) {
        var checkbox       = $(e.target).closest('tr').find('.panel-list-checkbox input'),
            checkboxAction = checkbox.prop('checked') ? 'uncheck' : 'check';

        checkbox.iCheck(checkboxAction);

        if (e.shiftKey && this.lastClickedCheckbox) {
            var lastClickedCheckboxIndex = this.checkboxes.index(this.lastClickedCheckbox),
                currentCheckboxIndex     = this.checkboxes.index(checkbox);

            this.checkboxes.slice(lastClickedCheckboxIndex, currentCheckboxIndex).iCheck(checkboxAction);
        }

        this.lastClickedCheckbox        = checkbox;
        this.lastClickedCheckboxChecked = checkbox.prop('checked');
    }, this));

    this.dataCells.on('mouseenter', $.proxy(function (e) {
        if (this.sortableInProgress || this.lastClickedCheckbox === null) {
            return;
        }

        if (e.buttons == 1 || e.buttons == 3) {
            var checkbox = $(e.target).closest('tr').find('.panel-list-checkbox input');
            checkbox.iCheck(this.lastClickedCheckboxChecked ? 'check' : 'uncheck');
        }
    }, this));
};

panel.list.prototype.initSortable = function () {

    $(this.node).find('tbody').sortable({
        handle: '.panel-list-sort-handler',
        items:  'tr:not(.panel-list-selected)',
        start:  $.proxy(function (e, ui) {
            this.sortableInProgress = true;

            var row      = ui.item,
                checkbox = row.find('td.panel-list-checkbox input');

            if (!checkbox.prop('checked')) {
                this.checkboxes.iCheck('uncheck');
                checkbox.iCheck('check');
            }

            if (this.rows.filter('.panel-list-selected').size() > 1) {
                this.rows.filter('.panel-list-selected').hide();
                // @todo unstable ordering in multiply mode
            }
        }, this),
        stop:   $.proxy(function (e, ui) {
            var row = ui.item;

            row.after(this.rows.filter('.panel-list-selected').css('display', 'table-row'));

            this.sortableInProgress = false;
        }, this),
        update: $.proxy(function (e, ui) {

            setTimeout($.proxy(function () {
                this.initDomProperties();

                var orderedList = [];

                this.rows.each(function () {
                    orderedList.push($(this).data('key'));
                });

                /* @todo add url handling */
                var url = this.url.split('?');

                panel.ajax({
                    url:    url[0] + '/sortSlice' + (url.length > 1 ? '?' + url[1] : ''),
                    method: 'post',
                    data:   {group: orderedList}
                });
            }, this), 100);
        }, this)
    });
};

panel.list.prototype.updateCheckboxesCheckAll = function () {
    var checked = this.checkboxes.size() > 0 && this.checkboxes.size() == this.checkboxes.filter(':checked').size();

    this.checkboxesCheckAll.prop('checked', checked);
    this.checkboxesCheckAll.parent().toggleClass('checked', checked);
};