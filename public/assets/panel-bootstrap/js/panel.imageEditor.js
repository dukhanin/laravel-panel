panel.imageEditor = function (options) {
    this.activeResize = null;
    this.resizes = [];
    this.file = new panel.file;

    $.extend(true, this, $.isPlainObject(options) ? options : {});
};

panel.imageEditor.prototype.init = function () {
    this.initModal();
};

panel.imageEditor.prototype.initModal = function () {
    this.modal = $(
        '<div class="modal fade image-editor" tabindex="-1" role="dialog" aria-hidden="true">'
        + '<div class="modal-dialog modal-lg">'
        + '<div class="modal-content">'

        + '<div class="modal-body">'
        + '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
        + '<div class="row">'

        + '<div class="col-md-3">'
        + '<div class="list-group image-editor-resizes"></div>'
        + '&nbsp;</div>'

        + '<div class="col-md-9">'
        + '<div class="image-editor-preview"></div>'
        + '</div>'

        + '</div>'
        + '</div>'

        + '<div class="modal-footer">'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>').appendTo(document.body);

    if (this.resizes.length === 0) {
        this.modal.find('.modal-footer').append('<button type="button" class="btn btn-default" data-dismiss="modal">' + panel.trans.buttons.close + '</button>');
    } else {
        this.modal.find('.modal-footer').append('<button type="button" class="btn btn-default" data-dismiss="modal">' + panel.trans.buttons.cancel + '</button>');
        this.modal.find('.modal-footer').append('<button type="button" class="btn btn-primary">' + panel.trans.buttons.apply + '</button>');
    }

    this.modal.find('.btn-primary').click($.proxy(function () {
        this.updateAllCroppedFiles();
        this.close();
    }, this));

    this.modal.one('shown.bs.modal', $.proxy(function () {
        this.loadFile();
    }, this));
};

panel.imageEditor.prototype.open = function () {
    this.modal.modal('show');
};

panel.imageEditor.prototype.close = function () {
    this.modal.modal('hide');
};

panel.imageEditor.prototype.setFile = function (file) {
    this.file = file;
};

panel.imageEditor.prototype.loadFile = function () {
    this.modal.find('.image-editor-preview').append(this.createImageNode());

    this.initResizes();
};

panel.imageEditor.prototype.setResizes = function (resizes) {
    this.resizes = resizes;
};

panel.imageEditor.prototype.initResizes = function () {
    if (this.resizes.length === 0) {
        this.modal.find('.image-editor-resizes').parent().removeClass('col-md-3');
        this.modal.find('.image-editor-preview').parent().removeClass('col-md-9');

        return;
    }

    $(this.resizes).each($.proxy(function (key, resizeOptions) {
        var resize = this.file.getResize(resizeOptions);

        if (resize) {
            this.addResize(resize, resizeOptions);
        } else {
            this.file.createResize(resizeOptions, $.proxy(function (resize) {
                this.addResize(resize, resizeOptions);
                this.selectAnyResize();
            }, this));
        }
    }, this));

    this.selectAnyResize();
};

panel.imageEditor.prototype.addResize = function (file, resizeOptions) {
    resizeOptions = panel.file.prototype._resolveResizeOptions(resizeOptions);

    file.resizeOptions = resizeOptions;
    file.changed = false;

    var option = $('<a href="#" class="list-group-item">' + this.getResizeLabel(resizeOptions) + '</a>')
        .data('file', file)
        .on('click', $.proxy(function (e) {
            this.loadResize(file);

            e.preventDefault();
        }, this));

    this.modal.find('.image-editor-resizes').append(option);
};

panel.imageEditor.prototype.loadResize = function (file) {
    this.unloadResize();

    this.activeResize = file;

    this.modal.find('.image-editor-resizes a').each(function () {
        var a = $(this);

        a.toggleClass('active', file.resizeOptions.key == a.data('file').resizeOptions.key);
    });

    this.initCropper();
};

panel.imageEditor.prototype.unloadResize = function (file) {
    var img = this.modal.find('.image-editor-preview img');

    this.activeResize = null;

    if (img.data('cropper')) {
        img.cropper('destroy');
    }
};

panel.imageEditor.prototype.getResizeLabel = function (resizeOptions) {
    if ('label' in resizeOptions && resizeOptions.label) {
        return resizeOptions.label;
    }

    return resizeOptions.key;
};

panel.imageEditor.prototype.selectAnyResize = function () {
    if (this.modal.find('.image-editor-resizes a.active').length === 0) {
        this.modal.find('.image-editor-resizes a').first().trigger('click');
    }
};

panel.imageEditor.prototype.initCropper = function (options) {
    var img = this.modal.find('.image-editor-preview img'),
        cropper = img.data('cropper'),
        resize = this.activeResize,
        cropperArea = this.getCropperArea(),
        cropperOptions = $.extend(true, {
            zoomable: false,
            strict: false,
            guides: false,
            autoCrop: false,
            center: false,
            viewMode: 2,
            cropend: function () {
                var img = $(this),
                    data = img.cropper('getData');

                resize.data.settings = $.extend(true, resize.data.settings, {
                    crop: {
                        area: {
                            x: data.x,
                            y: data.y,
                            w: data.width,
                            h: data.height
                        }
                    }
                });

                resize.changed = true;
            }
        }, options ? options : {});

    if (resize && resize.resizeOptions.size.static) {
        cropperOptions.aspectRatio = resize.resizeOptions.size.width / resize.resizeOptions.size.height;
    }

    if (cropperArea) {
        cropperOptions.autoCrop = true;
        cropperOptions.data = cropperArea;
    }

    img.cropper(cropperOptions);
};

panel.imageEditor.prototype.updateAllCroppedFiles = function () {
    for (var i in this.resizes) {
        var resize = this.file.getResize(this.resizes[i]);

        if (!resize || !resize.changed) {
            continue;
        }

        resize.cropFromParent({
            area: resize.data.settings.crop.area,
            size: resize.resizeOptions.size
        });
    }
};

panel.imageEditor.prototype.getCropperArea = function () {
    if (!this.activeResize) {
        return;
    }

    if ('crop' in this.activeResize.data.settings && 'area' in this.activeResize.data.settings.crop) {
        var a = this.activeResize.data.settings.crop.area,
            area = {
                x: a.x,
                y: a.y,
                width: a.w,
                height: a.h
            };


    } else if (this.activeResize.resizeOptions.size.static) {
        var resizeK = this.activeResize.resizeOptions.size.width / this.activeResize.resizeOptions.size.height,
            originalK = this.file.getWidth() / this.file.getHeight(),
            area = {
                x: 0,
                y: 0,
                width: this.file.getWidth(),
                height: this.file.getHeight()
            };

        if (originalK < resizeK) {
            var k = originalK / resizeK;

            area.y = area.height * (1 - k) / 2;
            area.height *= k;
        } else if (originalK > resizeK) {
            var k = resizeK / originalK;

            area.x = area.width * (1 - k) / 2;
            area.width *= k;
        }
    }

    return area;
};

panel.imageEditor.prototype.createImageNode = function () {
    return $('<img src="' + this.file.getUrl() + '" />');
};
