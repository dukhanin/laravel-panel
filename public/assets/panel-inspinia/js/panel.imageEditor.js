/**
 * @todo destroy editor and cropper objects after modal closed
 */

panel.imageEditor = function (options) {
    this.original = null; // @todo rename to parent
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
        '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">'
        + '<div class="modal-dialog modal-lg">'
        + '<div class="modal-content">'
        + '<div class="modal-body"  style="min-height: 400px;">'
        + '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
        + '<div class="row">'
        + '<div class="col-md-3">'

        +
        '<div class="list-group image-editor-resizes" style="margin-top: 20px;"></div>' + //@todo
        '' +
        '</div>'
        + '<div class="col-md-9 image-editor-preview" style="text-align: center;"></div>'
        + '</div>'
        + '</div>'

        + '<div class="modal-footer">'
        + '<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>'
        + '<button type="button" class="btn btn-primary">Сохранить</button>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>').appendTo(document.body);

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

    for (var i in this.resizes) {
        var resize = file.getResize(this.resizes[i]);

        if (resize) {
            this.addResize(resize, this.resizes[i]);
        }
    }

    this.modal.find('.image-editor-resizes a').first().trigger('click');
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
            cropend:  function () {
                var img         = $(this),
                    cropBoxData = img.cropper('getCropBoxData'),
                    canvasData  = img.cropper('getCanvasData'),
                    k           = canvasData.width / canvasData.naturalWidth;

                resize.data.settings = $.extend(true, resize.data.settings, {
                    crop: {
                        area: {
                            x: cropBoxData.left / k,
                            y: cropBoxData.top / k,
                            w: cropBoxData.width / k,
                            h: cropBoxData.height / k
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
    return $('<img style="max-width: 100%;" src="' + file.getUrl() + '" />');
};

panel.imageEditor.prototype.addResize = function (file, resizeOptions) {
    resizeOptions = file._resolveResizeOptions(resizeOptions); // @todo плохо, вынести в другие методы

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
