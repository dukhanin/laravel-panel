panel.imageEditor = function (options) {
    this.original     = null; // @todo rename to parent
    this.activeResize = null;
    this.resizes      = [];

    var defaults = {},
        options  = $.extend(true, defaults, options ? options : {});

    for (var key in options) {
        this[key] = options[key];
    }
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
        for (var i in this.resizes) {
            var resize = this.original.getResize(this.resizes[i]);

            if (!resize || !resize.changed) {
                continue;
            }

            resize.cropFromParent({
                                      area: resize.data.settings.crop.area,
                                      size: resize.resizeOptions.size
                                  });
        }

        this.modal.modal('hide');
    }, this));
};

panel.imageEditor.prototype.loadFile = function (file) {
    this.original = file;

    this.modal.find('.image-editor-preview').append(this.createImageNode(file));

    this.initResizes();
};

panel.imageEditor.prototype.initResizes = function()
{
    if(this.resizes.length === 0) {
        this.modal.find('.image-editor-resizes').parent().removeClass('col-md-3');
        this.modal.find('.image-editor-preview').parent().removeClass('col-md-9');

        return;
    }

    $(this.resizes).each($.proxy(function (key, resizeOptions) {
        var resize = this.original.getResize(resizeOptions);

        if (resize) {
            this.addResize(resize, resizeOptions);
        } else {
            this.original.createResize(resizeOptions, $.proxy(function (resize) {
                this.addResize(resize, resizeOptions);
                this.selectAnyResize();
            }, this));
        }
    }, this));

    this.selectAnyResize();
};

panel.imageEditor.prototype.selectAnyResize = function () {
    if (this.modal.find('.image-editor-resizes a.active').length === 0) {
        this.modal.find('.image-editor-resizes a').first().trigger('click');
    }
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

panel.imageEditor.prototype.initCropper = function (options) {
    var img            = this.modal.find('.image-editor-preview img'),
        cropper        = img.data('cropper'),
        resize         = this.activeResize,
        cropperArea    = this.getCropperArea(),
        cropperOptions = $.extend(true, {
            zoomable: false,
            strict:   false,
            guides:   false,
            autoCrop: false,
            center:   false,
            viewMode: 2,
            cropend:  function () {
                var img  = $(this),
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
        cropperOptions.data     = cropperArea;
    }

    img.cropper(cropperOptions);
};


panel.imageEditor.prototype.getCropperArea = function () {
    if (!this.activeResize) {
        return;
    }

    if ('crop' in this.activeResize.data.settings && 'area' in this.activeResize.data.settings.crop) {
        var a    = this.activeResize.data.settings.crop.area,
            area = {
                x:      a.x,
                y:      a.y,
                width:  a.w,
                height: a.h
            };


    } else if (this.activeResize.resizeOptions.size.static) {
        var resizeK   = this.activeResize.resizeOptions.size.width / this.activeResize.resizeOptions.size.height,
            originalK = this.original.getWidth() / this.original.getHeight(),
            area      = {
                x:      0,
                y:      0,
                width:  this.original.getWidth(),
                height: this.original.getHeight()
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

panel.imageEditor.prototype.createImageNode = function (file) {
    return $('<img src="' + file.getUrl() + '" />');
};

panel.imageEditor.prototype.addResize = function (file, resizeOptions) {
    resizeOptions = file._resolveResizeOptions(resizeOptions);

    file.resizeOptions = resizeOptions;
    file.changed       = false;

    var option = $('<a href="#" class="list-group-item">' + this.getResizeLabel(resizeOptions) + '</a>')
        .data('file', file)
        .on('click', $.proxy(function (e) {
            this.loadResize(file);

            e.preventDefault();
        }, this));

    this.modal.find('.image-editor-resizes').append(option);
};

panel.imageEditor.prototype.getResizeLabel = function (resizeOptions) {
    if ('label' in resizeOptions && resizeOptions.label) {
        return resizeOptions.label;
    }

    return resizeOptions.key;
};
