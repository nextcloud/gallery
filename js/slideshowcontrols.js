/* global $, OC, SlideShow */
/**
 * Button and key controls for the slideshow
 *
 * @param slideshow
 * @param container
 * @param images
 * @param interval
 * @constructor
 */
var SlideShowControls = function (slideshow, container, images, interval) {
	this.slideshow = slideshow;
	this.container = container;
	this.images = images;
	this.current = 0;
	this.errorLoadingImage = false;
	this.progressBar = container.find('.progress');
	this.interval = interval || 5000;
	this.playTimeout = 0;
	this.playing = false;
	this.active = false;
};

SlideShowControls.prototype = {
	/**
	 * Initialises the controls
	 *
	 * @param {bool} play
	 */
	init: function (play) {
		this.active = true;
		// hide arrows and play/pause when only one pic
		this.container.find('.next, .previous').toggle(this.images.length > 1);
		if (this.images.length === 1) {
			this.container.find('.play, .pause').hide();
		}

		// Hide the toggle background button until we have something to show
		this.container.find('.changeBackground').hide();

		var makeCallBack = function (handler) {
			return function (evt) {
				if (!this.active) {
					return;
				}
				evt.stopPropagation();
				handler.call(this);
			}.bind(this);
		}.bind(this);

		this._buttonSetup(makeCallBack);
		this._keyCodeSetup(makeCallBack);

		if (play) {
			this._play();
		} else {
			this._pause();
		}
	},
	/**
	 * Initialises local variables when the show starts
	 *
	 * @param {number} currentImageId
	 */
	show: function (currentImageId) {
		this.current = currentImageId;
		this.errorLoadingImage = false;
		if (this.playing) {
			this._setTimeout();
		}
	},

	/**
	 * Updates the private variables in case of problems loading an image
	 *
	 * @param {Array} images
	 * @param {bool} errorLoadingImage
	 */
	updateControls: function (images, errorLoadingImage) {
		this.images = images;
		this.errorLoadingImage = errorLoadingImage;
	},

	/**
	 * Sets up the button based controls
	 * @param {Function} makeCallBack
	 * @private
	 */
	_buttonSetup: function (makeCallBack) {
		this.container.children('.next').click(makeCallBack(this._next));
		this.container.children('.previous').click(makeCallBack(this._previous));
		this.container.children('.exit').click(makeCallBack(this._stop));
		this.container.children('.pause').click(makeCallBack(this._pause));
		this.container.children('.play').click(makeCallBack(this._play));
		this.container.children('.downloadImage').click(makeCallBack(this._getImageDownload));
		this.container.children('.changeBackground').click(makeCallBack(this._toggleBackground));
		//this.container.click(makeCallBack(this.next));
	},

	/**
	 * Sets up the key based controls
	 * @param {Function} makeCallBack
	 * @private
	 */
	_keyCodeSetup: function (makeCallBack) {
		$(document).keyup(function (evt) {
			if (evt.keyCode === 27) { // esc
				makeCallBack(this._stop)(evt);
			} else if (evt.keyCode === 37) { // left
				makeCallBack(this._previous)(evt);
			} else if (evt.keyCode === 39) { // right
				makeCallBack(this._next)(evt);
			} else if (evt.keyCode === 32) { // space
				makeCallBack(this._play)(evt);
			} else if (evt.keyCode === 70) { // f (fullscreen)
				makeCallBack(this._fullScreenToggle)(evt);
			} else if (this._zoomOutKey(evt)) {
				makeCallBack(this._zoomToOriginal)(evt);
			} else if (this._zoomInKey(evt)) {
				makeCallBack(this._zoomToFit)(evt);
			}
		}.bind(this));
	},

	/**
	 * Defines the keys we can use to zoom in
	 *
	 * Currently zero, o or down
	 *
	 * @param evt
	 *
	 * @returns {boolean}
	 * @private
	 */
	_zoomOutKey: function (evt) {
		// zero, o or down key
		return (evt.keyCode === 48 || evt.keyCode === 96 || evt.keyCode === 79 ||
		evt.keyCode === 40);
	},

	/**
	 * Defines the keys we can use to zoom in
	 *
	 * Currently 9, i or up
	 *
	 * @param evt
	 *
	 * @returns {boolean}
	 * @private
	 */
	_zoomInKey: function (evt) {
		// 9, i or up key
		return (evt.keyCode === 57 || evt.keyCode === 105 || evt.keyCode === 73 ||
		evt.keyCode === 38);
	},

	/**
	 * Starts the slideshow timer
	 * @private
	 */
	_setTimeout: function () {
		this._clearTimeout();
		this.playTimeout = setTimeout(this._next.bind(this), this.interval);
		this.progressBar.stop();
		this.progressBar.css('height', '6px');
		this.progressBar.animate({'height': '26px'}, this.interval, 'linear');
	},

	/**
	 * Stops the slideshow timer
	 * @private
	 */
	_clearTimeout: function () {
		if (this.playTimeout) {
			clearTimeout(this.playTimeout);
		}
		this.progressBar.stop();
		this.progressBar.css('height', '6px');
		this.playTimeout = 0;
	},

	/**
	 * Starts the times slideshow
	 * @private
	 */
	_play: function () {
		this.playing = true;
		this.container.find('.pause').show();
		this.container.find('.play').hide();
		this._setTimeout();
	},

	/**
	 * Pauses the timed slideshow
	 * @private
	 */
	_pause: function () {
		this.playing = false;
		this.container.find('.pause').hide();
		this.container.find('.play').show();
		this._clearTimeout();
	},

	/**
	 * Shows the next slide
	 * @private
	 */
	_next: function () {
		this.slideshow.next();
		if (this.errorLoadingImage) {
			this.current -= 1;
		}
		this.current = (this.current + 1) % this.images.length;
		var next = (this.current + 1) % this.images.length;
		this.slideshow.show(this.current).then(function () {
			// preload the next image
			this.slideshow.loadImage(this.images[next]);
		}.bind(this));
	},

	/**
	 * Shows the previous slide
	 * @private
	 */
	_previous: function () {
		this.slideshow.previous();
		this.current = (this.current - 1 + this.images.length) % this.images.length;
		var previous = (this.current - 1 + this.images.length) % this.images.length;
		this.slideshow.show(this.current).then(function () {
			// preload the next image
			this.slideshow.loadImage(this.images[previous]);
		}.bind(this));
	},

	/**
	 * Stops the timed slideshow
	 * @private
	 */
	_stop: function () {
		this.slideshow.stop();

		this._clearTimeout();
		this.container.hide();
		this.active = false;
	},

	_togglePlay: function () {
		if (this.playing) {
			this._pause();
		} else {
			this._play();
		}
	},

	/**
	 * Launches fullscreen mode if the browser supports it
	 * @private
	 */
	_fullScreenToggle: function () {
		this.slideshow.fullScreenToggle();
	},

	/**
	 * Resizes the image to its original size
	 * @private
	 */
	_zoomToOriginal: function () {
		this.slideshow.zoomToOriginal();
	},

	/**
	 * Fits the image in the browser window
	 * @private
	 */
	_zoomToFit: function () {
		this.slideshow.zoomToFit();
	},

	/**
	 * Sends the current image as a download
	 *
	 * @returns {boolean}
	 * @private
	 */
	_getImageDownload: function () {
		var downloadUrl = this.images[this.current].downloadUrl;

		return this.slideshow.getImageDownload(downloadUrl);
	},

	/**
	 * Changes the colour of the background of the image
	 *
	 * @private
	 */
	_toggleBackground: function () {
		this.slideshow.toggleBackground();
	}

};
