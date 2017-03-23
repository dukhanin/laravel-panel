panel.inputFiles = function (input, options) {
    $.extend(true,
        this,
        this.defaults,
        {uploadUrl: panel.uploadUrl},
        options ? options : {}
    );

    this.input = $(input);
};

panel.inputFiles.prototype.defaults = {
    input: null,
    inputName: null,
    directory: '',
    uploadUrl: panel.uploadUrl,
    multiple: false,
    container: null,
    files: [],
    file: null,
    resizes: []
};

panel.inputFiles.prototype.init = function () {
    this.initInputName();
    this.initContainer();
    this.initMultiple();
    this.initUpload();
};

panel.inputFiles.prototype.initInputName = function () {
    this.inputName = this.input.attr('name');
    this.input.attr('name', '');
};

panel.inputFiles.prototype.initMultiple = function () {
    if (this.input.attr('multiple')) {
        this.multiple = true;

        if (!this.inputName.match(/\[\]$/)) {
            this.inputName += '[]';
        }

        this.container.sortable();
    }
};

panel.inputFiles.prototype.initContainer = function () {
    if (this.container === null) {
        this.container = $('<div class="files-container"></div>');
        this.container.appendTo(this.input.parent());
    } else {
        this.container = $(this.container);
    }
};

panel.inputFiles.prototype.initUpload = function () {
    this.input.on('change', $.proxy(this.doUpload, this));
};

panel.inputFiles.prototype.doUpload = function () {
    var data = new FormData(),
        files = this.input.prop('files');

    for (var i in files) {
        data.append('file[]', files[i]);
    }

    data.append('directory', this.directory);

    panel.ajax({
        url: this.uploadUrl,
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        success: $.proxy(function (responseJSON) {
            var files = $.map(responseJSON.data, function (fileData) {
                return new panel.file(fileData);
            });

            this.onFilesUpload(files);

            for (var i in files) {
                this.onFileUpload(files[i]);
            }
        }, this)
    });

    this.input.val('');
};

panel.inputFiles.prototype.onFilesUpload = function (files) {
    if (!this.multiple) {
        this.deleteFiles();
    }
};

panel.inputFiles.prototype.onFileUpload = function (file) {
    this.addFile(file);
};

panel.inputFiles.prototype.onFileDelete = function (file) {
    file.htmlNode.remove();

    for (var i in this.files) {
        if (this.files[i].getId() === file.getId()) {
            this.files.splice(i, 1);
        }
    }
};

panel.inputFiles.prototype.createDeleteButtonForFile = function (file) {
    return $('<a href="#" class="file-item-delete"> <i class="fa fa-times"></i> ' + panel.trans.actions.delete + '</a>').on('click', function (e) {
        panel.confirm(
            {title: panel.trans.confirm.delete, confirmButtonText: panel.trans.buttons.confirm},
            function () {
                file.delete();
            }
        );

        e.preventDefault();
    });
};

panel.inputFiles.prototype.createInputIdForFile = function (file) {
    return $('<input type="hidden" name="' + this.inputName + '" value="' + file.getId() + '" />');
};

panel.inputFiles.prototype.getTypeOfFile = function (file) {

    if (file.isImage()) {
        return 'image';
    }

    if (file.isDocument()) {
        return 'document';
    }

    if (file.isAudio()) {
        return 'audio';
    }

    if (file.isVideo()) {
        return 'video';
    }

    return 'common';
};

panel.inputFiles.prototype.htmlNodeForFile = function (file) {
    var methodName,
        node = $('<div class="file-item file-item-' + file.type + '" id="file-item-' + file.getId() + '"></div>');

    if ((methodName = file.type + 'NodeForFile') in this) {
        node.append(this[methodName].call(this, file));
    } else {
        node.append(this.defaultNodeForFile(file));
    }

    node.append(this.createDeleteButtonForFile(file));

    node.append(this.createInputIdForFile(file));

    return node;
};

panel.inputFiles.prototype.imageNodeForFile = function (file, isRecursiveCall) {
    var resizeOptions = {key: 'panel_default', size: '150xx150'},
        resize = file.getResize(resizeOptions) || this.suggestResizeForFile(file, resizeOptions),
        node = $('<img src="' + resize.getUrl() + '" width="' + resize.getWidth() + '" height="' + resize.getHeight() + '" />');

    if ('suggested' in resize && !isRecursiveCall) {
        node.addClass('file-image-suggested');

        file.createResize(resizeOptions, $.proxy(function (resize) {
            node.replaceWith(this.imageNodeForFile(file, true));
        }, this));

        return node;
    }

    if ('imageEditor' in panel) {
        node.click($.proxy(function () {
            var editor = new panel.imageEditor();

            editor.setResizes(this.inputFiles.resizes);

            editor.setFile(file);

            editor.init();

            editor.open();
        }, file));
    }

    for (var i in this.resizes) {
        file.getResize(this.resizes[i]) || file.createResize(this.resizes[i]);
    }

    return node;
};

panel.inputFiles.prototype.defaultNodeForFile = function (file) {
    return $('<div class="file"<a href="' + file.getUrl() + '" target="_blank">' + file.getUrl() + '</a>');
};

panel.inputFiles.prototype.suggestResizeForFile = function (file, options) {
    options = panel.file.prototype._resolveResizeOptions(options);

    if (!options.size) {
        return;
    }

    var suggestedSize = this.suggestSizeForFile(file, options.size),
        fileData = $.extend({}, file.data, {
            id: null,
            key: options.key,
            width: suggestedSize.width,
            height: suggestedSize.height,
            size: 0,
            updated_at: '',
            created_at: '',
            children: []
        });

    return $.extend(new panel.file(fileData), {suggested: true});
};

panel.inputFiles.prototype.suggestSizeForFile = function (file, size) {
    size = panel.file.prototype._resolveImageSize(size);

    if (!size.static) {
        var k = Math.max(file.getWidth() / size.width, file.getHeight() / size.height);

        size.width = Math.round(file.getWidth() / k);
        size.height = Math.round(file.getHeight() / k);
    }

    return size;
};

panel.inputFiles.prototype.supplyFileObject = function (file) {
    file.inputFiles = this;

    file.type = this.getTypeOfFile(file);

    file.htmlNode = this.htmlNodeForFile(file);

    $(file).on('delete', $.proxy(function () {
        this.inputFiles.onFileDelete(this);
    }, file));

    return file;
};

panel.inputFiles.prototype.addFile = function (file) {
    this.supplyFileObject(file);

    this.files.push(file);

    this.container.append(file.htmlNode);
};

panel.inputFiles.prototype.deleteFiles = function (file) {
    for (var i in this.files) {
        this.files[i].delete();
    }
};