/**
 * @todo force resize param
 */

panel.file = function (fileData, options) {
    this.setOptions(options);
    this.setData(fileData);

    this.init();
};

panel.file.prototype.setOptions = function (options) {
    var options = $.extend(true, {
        url:      '/admin/upload/',
        children: []
    }, options ? options : {});

    for (var key in options) {
        this[key] = options[key];
    }
};

panel.file.prototype.setData = function (fileData) {
    this.data = fileData;

    if (!$.isPlainObject(this.data.settings)) {
        this.data.settings = {};
    }
};

panel.file.prototype.init = function () {
    this.initChildren();
};

panel.file.prototype.initChildren = function () {
    for (var i in this.data.children) {
        this.children.push(new panel.file(this.data.children[i]));
    }
};

panel.file.prototype.getId = function () {
    return this.data.id;
};

panel.file.prototype.getUrl = function () {
    return this.data.url;
};

panel.file.prototype.getKey = function () {
    return this.data.key;
};

panel.file.prototype.getWidth = function () {
    return this.data.width;
};

panel.file.prototype.getHeight = function () {
    return this.data.height;
};

panel.file.prototype.getChild = function (key) {
    for (var i in this.children) {
        if (this.children[i].getKey() == key) {
            return this.children[i];
        }
    }
};

panel.file.prototype.getResize = function (options) {
    options = this._resolveResizeOptions(options);

    return this.getChild(options.key);
};

panel.file.prototype.isImage = function () {
    return this.data.is_image;
};

panel.file.prototype.isAudio = function () {
    return this.data.is_audio;
};

panel.file.prototype.isVideo = function () {
    return this.data.is_video;
};

panel.file.prototype.isDocument = function () {
    return this.data.is_document;
};

panel.file.prototype.delete = function () {
    document.admin.ajax(this.url + 'delete/' + this.data.id);

    $(this).triggerHandler('delete');
};

panel.file.prototype.createResize = function (options, callback) {
    var file = this;

    document.admin.ajax({
        url:     this.url + 'createResize/' + this.data.id,
        method:  'post',
        data:    this._resolveResizeOptions(options),
        success: function (resizeData) {
            var resize = new panel.file(resizeData);

            file.children.push(resize);

            callback && callback.call(this, resize);
        }
    });
};

panel.file.prototype.cropFromParent = function (options, callback) {
    var file = this;

    document.admin.ajax({
        url:     this.url + 'cropFromParent/' + this.data.id,
        method:  'post',
        data:    options,
        success: function (fileData) {
            file.setData(fileData);

            callback && callback.call(this);
        }
    });
};

panel.file.prototype._resolveResizeOptions = function (options) {
    if (!$.isPlainObject(options)) {
        options = {
            key:  this._sizeToKey(options),
            size: options
        }
    }

    options = $.extend({
        key:  '',
        size: ''
    }, options);

    options.size = this._resolveImageSize(options.size);

    if (!options.key) {
        options.key = this._sizeToKey(options.size);
    }

    return options;
};

panel.file.prototype._resolveImageSize = function (size) {
    if (!$.isPlainObject(size)) {
        size = this._parseImageSize(size);
    }

    return $.extend({
        width:         null,
        height:        null,
        static:        null,
        enlarge:       null,
        reduce:        null,
        static_align:  null,
        static_valign: null
    }, size);
};

panel.file.prototype._sizeToKey = function (size) {
    size = this._resolveImageSize(size);

    return [size.width, size.static ? 'xx' : 'x', size.height].join('');
};

panel.file.prototype._parseImageSize = function (size) {
    size        = String(size).toLowerCase();
    var matches = size.match(/^(\d+)(x{1,2})(\d+)([-\+]{0,2})((\s+\w+){0,2})\s*$/i);

    if (!matches) {
        return null;
    }

    var parsed = {
        width:         parseInt(matches[1]),
        height:        parseInt(matches[3]),
        static:        matches[2].length == 2,
        enlarge:       false,
        reduce:        true,
        static_align:  'center',
        static_valign: 'center'
    };

    if (matches[4].match(/\\+/)) {
        parsed.enlarge = true;
    }

    if (matches[4].match(/-/)) {
        parsed.reduce = true;
    }

    if (parsed.static) {
        aligments = $.trim(matches[5]).replace(/\s+/, ' ').split(' ');

        if ($.inArray('left', aligments) !== -1) {
            parsed.static_align = 'left';
        }
        else if ($.inArray('right', aligments) !== -1) {
            parsed.static_align = 'right';
        }

        if ($.inArray('top', aligments) !== -1) {
            parsed.static_valign = 'top';
        }
        else if ($.inArray('bottom', aligments) !== -1) {
            parsed.static_valign = 'bottom';
        }
    }

    return parsed;
};


