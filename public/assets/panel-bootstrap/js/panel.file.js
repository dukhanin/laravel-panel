panel.file = function (fileData) {
    this.children = [];

    this.url = panel.uploadUrl;

    this.setData(fileData);

    this.init();
};

panel.file.prototype.setData = function (fileData) {
    this.data = this.validateData(fileData);
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
    options = panel.file.prototype._resolveResizeOptions(options);

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
    panel.ajax({
        url: this.url + '/delete/' + this.data.id,
        method: 'post'
    });

    $(this).triggerHandler('delete');
};

panel.file.prototype.createResize = function (options, callback) {
    var file = this;

    panel.ajax({
        url: this.url + '/createResize/' + this.data.id,
        method: 'post',
        data: panel.file.prototype._resolveResizeOptions(options),
        success: function (responseJSON) {
            var resize = new panel.file(responseJSON.data);

            file.children.push(resize);

            callback && callback.call(this, resize);
        }
    });
};

panel.file.prototype.cropFromParent = function (options, callback) {
    var file = this;

    panel.ajax({
        url: this.url + '/cropFromParent/' + this.data.id,
        method: 'post',
        data: options,
        success: function (responseJSON) {
            file.setData(responseJSON.data);

            callback && callback.call(this);
        }
    });
};

panel.file.prototype.validateData = function (data) {
    if (!$.isPlainObject(data)) {
        data = {};
    }

    if (!('settings' in data) || !$.isPlainObject(data.settings)) {
        data.settings = {};
    }

    return data;
};

panel.file.prototype._resolveResizeOptions = function (options) {
    if (!$.isPlainObject(options)) {
        options = {
            key: panel.file.prototype._sizeToKey(options),
            size: options
        }
    }

    options = $.extend({
        key: '',
        size: ''
    }, options);

    options.size = panel.file.prototype._resolveImageSize(options.size);

    if (!options.key) {
        options.key = panel.file.prototype._sizeToKey(options.size);
    }

    return options;
};

panel.file.prototype._resolveImageSize = function (size) {
    if (!$.isPlainObject(size)) {
        size = this.parseImageSize(size);
    }

    return $.extend({
        width: null,
        height: null,
        static: null,
        enlarge: null,
        reduce: null
    }, size);
};

panel.file.prototype._sizeToKey = function (size) {
    size = panel.file.prototype._resolveImageSize(size);

    return [size.width, size.static ? 'xx' : 'x', size.height].join('');
};

panel.file.prototype.parseImageSize = function (size) {
    size = String(size).toLowerCase();
    var matches = size.match(/^(\d+)(x{1,2})(\d+)([-\+]{0,2})((\s+\w+){0,2})\s*$/i);

    if (!matches) {
        return null;
    }

    var parsed = {
        width: parseInt(matches[1]),
        height: parseInt(matches[3]),
        static: matches[2].length == 2,
        enlarge: false,
        reduce: true
    };

    if (matches[4].match(/\\+/)) {
        parsed.enlarge = true;
    }

    if (matches[4].match(/-/)) {
        parsed.reduce = true;
    }

    return parsed;
};


