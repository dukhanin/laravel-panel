panel.inputFiles = function (inputNode, options) {
    var defaults = {
        input:     null,
        inputName: null,
        url:       '/admin/upload',
        multiple:  false,
        container: null,
        files:     [],
        file:      null,
        resizes:   []
    };

    var options = $.extend(true, defaults, options ? options : {});

    for (var key in options) {
        this[key] = options[key];
    }

    this.input = inputNode;
};

panel.inputFiles.prototype.init = function () {
    this.initInputName();
    this.initMultiple();
    this.initContainer();
    this.initInputImmidiateUpload();
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
    }
};

panel.inputFiles.prototype.initContainer = function () {
    if (this.container === null) {
        this.container = $('<div class="container"></div>');
        this.container.appendTo(this.input.parent());
    } else {
        this.container = $(this.container);
    }
};

panel.inputFiles.prototype.initInputImmidiateUpload = function () {
    var inputFiles = this;

    this.input.on('change', function () {
        var data = new FormData();

        $.each(this.files, function (index, file) {
            data.append('file[]', file);
        });

        document.admin.ajax({
            url:         inputFiles.url,
            data:        data,
            cache:       false,
            contentType: false,
            processData: false,
            type:        'POST',
            success:     function (filesData) {
                if (!inputFiles.multiple) {
                    inputFiles.deleteFiles();
                }

                $(filesData).each(function (index, fileData) {
                    inputFiles.addFile(new panel.file(fileData));
                });
            }
        });

        $(inputFiles).val('');
    });
};

panel.inputFiles.prototype.supplyFileObject = function (file) {
    file.htmlNode   = null;
    file.inputFiles = this;

    file.getHtmlNode = function () {
        if (this.htmlNode === null) {
            this.initHtmlNode();
        }

        return this.htmlNode;
    };


    file.initHtmlNode = function () {
        this.htmlNode = $('<div class="file-item" id="file-item-' + this.data.id + '"></div>')
            .append(this.isImage() ? this.createImageHtmlNode() : this.createLinkHtmlNode());
    };

    file.createLinkHtmlNode = function () {
        var node = $('<div class="file-link"></div>');

        node.append('<a href="' + this.getUrl() + '" target="_blank">' + this.getUrl() + '</a>');

        return node;
    };

    file.createImageHtmlNode = function (options) {
        var original      = this,
            node          = $('<div class="file-image"></div>'),
            resizeOptions = {
                key:  'admin_default',
                size: '150xx150'
            },
            resize        = this.getResize(resizeOptions) || this._suggestResize(resizeOptions) || original;

        if ('suggested' in resize) {
            node.addClass('file-image-suggested');

            this.createResize(resizeOptions, function () {
                node.replaceWith(original.createImageHtmlNode(options));
            });
        } else {
            node.click(function () {
                var editor = new panel.imageEditor({
                    resizes: original.inputFiles.resizes
                });

                editor.init();

                editor.modal.one('shown.bs.modal', function () {
                    editor.loadFile(original);
                });

                editor.modal.modal('show');
            });

        }

        node.append('<img src="' + resize.getUrl() + '" width="' + resize.getWidth() + '" height="' + resize.getHeight() + '" />')

        return node;
    };


    file._suggestResize = function (options) {
        options = this._resolveResizeOptions(options);

        if (!options.size) {
            return;
        }

        var suggestedSize = this._suggestSize(options.size),
            fileData      = $.extend({}, this.data, {
                id:         null,
                key:        options.key,
                width:      suggestedSize.width,
                height:     suggestedSize.height,
                size:       0,
                updated_at: '',
                created_at: '',
                children:   []
            });

        return new panel.file(fileData, {suggested: true});
    };

    file._suggestSize = function (size) {
        size = this._resolveImageSize(size);

        if (size.static) {
            // @todo ?
        } else {
            var k = Math.max(this.getWidth() / size.width, this.getHeight() / size.height);

            size.width  = Math.round(this.getWidth() / k);
            size.height = Math.round(this.getHeight() / k);
        }

        return size;
    };

    $(file).on('delete', function () {
        this.getHtmlNode().remove();

        for (var i in this.inputFiles.files) {
            if (this.inputFiles.files[i].getId() === this.getId()) {
                this.inputFiles.files.splice(i, 1);
            }
        }
    });

    file.getHtmlNode()
        .append('<input type="hidden" name="' + this.inputName + '" value="' + file.getId() + '" />')
        .append(
        $('<a href="#" class="file-item-delete"> (удалить файл)</a>').on('click', function (e) {
            document.admin.confirm(
                {title: 'Удалить файл?', confirmButtonText: 'Да, Удалить'},
                function () {
                    file.delete();
                }
            );

            e.preventDefault();
        })
    );

    for (var i in this.resizes) {
        file.getResize(this.resizes[i]) || file.createResize(this.resizes[i]);
    }

    return file;
};

panel.inputFiles.prototype.addFile = function (file) {
    this.supplyFileObject(file);

    this.files.push(file);

    this.container.append(file.getHtmlNode());
};

panel.inputFiles.prototype.deleteFiles = function (file) {
    for (var i in this.files) {
        this.files[i].delete();
    }
};

(function ($) {
    $.fn.inputFiles = function (options) {
        if (!this.prop('inputFiles')) {
            this.prop('inputFiles', new panel.inputFiles(this, options));
        }

        return this.prop('inputFiles');
    }
})(jQuery);
