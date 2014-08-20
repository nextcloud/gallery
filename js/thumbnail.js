function Thumbnail (path, square) {
	this.square = square;
	this.path = path;
	this.url = Thumbnail.getUrl(path, square);
	this.image = null;
	this.loadingDeferred = new $.Deferred();
	this.ratio = null;
}

Thumbnail.map = {};
Thumbnail.squareMap = {};
Thumbnail.height = 200;
Thumbnail.width = 400;

Thumbnail.get = function (path, square) {
	var map = (square) ? Thumbnail.squareMap : Thumbnail.map;
	if (!map[path]) {
		map[path] = new Thumbnail(path, square);
	}
	return map[path];
};

Thumbnail.getUrl = function (path, square) {
	if (path.substr(path.length - 4) === '.svg' || path.substr(path.length - 5) === '.svgz') {
		return Gallery.getImage(path);
	}
	return OC.generateUrl('apps/gallery/ajax/thumbnail?file={file}&scale={scale}&square={square}', {
		file: encodeURIComponent(path),
		scale: window.devicePixelRatio,
		square: (square) ? 1 : 0
	});
};

Thumbnail.loadBatch = function (paths, square) {
	var map = (square) ? Thumbnail.squareMap : Thumbnail.map;
	paths = paths.filter(function (path) {
		return !map[path];
	});
	var thumbnails = {};
	if (paths.length) {
		var parts = paths[0].split('/');
		var user = parts[0];

		paths = paths.map(function (path) {
			var thumb = new Thumbnail(path, square);
			thumb.image = new Image();
			map[path] = thumbnails[path] = thumb;
			return path.substr(user.length + 1); //strip /$user
		});

		var url = OC.generateUrl('apps/gallery/ajax/thumbnail/batch?user={user}&image={images}&scale={scale}&square={square}', {
			user: user,
			images: paths.map(encodeURIComponent).join(';'),
			scale: window.devicePixelRatio,
			square: (square) ? 1 : 0
		});

		var eventSource = new OC.EventSource(url);
		eventSource.listen('preview', function (data) {
			var path = user + '/' + data.image;
			var extension = path.substr(path.length - 3);
			var thumb = thumbnails[path];
			thumb.image.onload = function () {
				Thumbnail.loadingCount--;
				thumb.image.ratio = thumb.image.width / thumb.image.height;
				thumb.image.originalWidth = 200 * thumb.image.ratio;
				thumb.loadingDeferred.resolve(thumb.image);
			};
			thumb.image.src = 'data:image/' + extension + ';base64,' + data.preview;
		});
	}
	return thumbnails;
};

Thumbnail.prototype.load = function () {
	var that = this;
	if (!this.image) {
		this.image = new Image();
		this.image.onload = function () {
			Thumbnail.loadingCount--;
			that.image.ratio = that.image.width / that.image.height;
			that.image.originalWidth = that.image.width / window.devicePixelRatio;
			that.loadingDeferred.resolve(that.image);
			Thumbnail.processQueue();
		};
		this.image.onerror = function () {
			Thumbnail.loadingCount--;
			that.loadingDeferred.reject(that.image);
			Thumbnail.processQueue();
		};
		Thumbnail.loadingCount++;
		this.image.src = this.url;
	}
	return this.loadingDeferred;
};

Thumbnail.queue = [];
Thumbnail.loadingCount = 0;
Thumbnail.concurrent = 3;
Thumbnail.paused = false;

Thumbnail.processQueue = function () {
	if (!Thumbnail.paused && Thumbnail.queue.length && Thumbnail.loadingCount < Thumbnail.concurrent) {
		var next = Thumbnail.queue.shift();
		next.load();
		Thumbnail.processQueue();
	}
};

Thumbnail.prototype.queue = function () {
	if (!this.image) {
		Thumbnail.queue.push(this);
	}
	Thumbnail.processQueue();
	return this.loadingDeferred;
};
