/* global SlideShow */
(function ($, SlideShow) {
	"use strict";
	/**
	 * Button and key controls for the slideshow
	 *
	 * @param {Object} slideshow
	 * @param {*} container
	 * @param {Object} zoomablePreview
	 * @param {number} interval
	 * @constructor
	 */
	var Controls = function (slideshow, container, zoomablePreview, interval) {
		this.slideshow = slideshow;
		this.container = container;
		this.zoomablePreview = zoomablePreview;
		this.progressBar = container.find('.progress');
		this.interval = interval || 5000;

	};

	Controls.prototype = {
		current: 0,
		errorLoadingImage: false,
		playTimeout: 0,
		playing: false,
		active: false,

		/**
		 * Initialises the controls
		 */
		init: function () {
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
			this._specialButtonSetup(makeCallBack);
			this._keyCodeSetup(makeCallBack);
		},

		/**
		 * Updates the controls
		 *
		 * @param {{name:string, url: string, path: string, fallBack: string}[]} images
		 * @param {bool} autoPlay
		 */
		update: function (images, autoPlay) {
			this.images = images;
			this.active = true;
			this.container.find('.pause').hide();

			// Hide prev/next and play buttons when we only have one pic
			this.container.find('.next, .previous, .play').toggle(this.images.length > 1);

			// Hide the toggle background button until we have something to show
			this.container.find('.changeBackground').hide();

			if (autoPlay) {
				this._play();
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
		 * Stops the timed slideshow
		 */
		stop: function () {
			this.playing = false;
			this.slideshow.stop();
			this.zoomablePreview.stop();

			this._clearTimeout();
			this.container.hide();
			this.active = false;
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
		 * Sets up the button based navigation
		 *
		 * @param {Function} makeCallBack
		 * @private
		 */
		_buttonSetup: function (makeCallBack) {
			this.container.children('.next').click(makeCallBack(this._next));
			this.container.children('.previous').click(makeCallBack(this._previous));
			this.container.children('.exit').click(makeCallBack(this._exit));
			this.container.children('.pause').click(makeCallBack(this._pause));
			this.container.children('.play').click(makeCallBack(this._play));
		},

		/**
		 * Sets up additional buttons
		 *
		 * @param {Function} makeCallBack
		 * @private
		 */
		_specialButtonSetup: function (makeCallBack) {
			this.container.children('.downloadImage').click(makeCallBack(this._getImageDownload));
			this.container.children('.changeBackground').click(makeCallBack(this._toggleBackground));
		},

		/**
		 * Sets up the key based controls
		 *
		 * @param {Function} makeCallBack
		 * @private
		 */
		_keyCodeSetup: function (makeCallBack) {
			$(document).keyup(function (evt) {
				var escKey = 27;
				var leftKey = 37;
				var rightKey = 39;
				var spaceKey = 32;
				var fKey = 70;
				var zoomOutKeys = [48, 96, 79, 40]; // zero, o or down key
				var zoomInKeys = [57, 105, 73, 38]; // 9, i or up key
				if (evt.keyCode === escKey) {
					makeCallBack(this._exit)(evt);
				} else if (evt.keyCode === leftKey) {
					makeCallBack(this._previous)(evt);
				} else if (evt.keyCode === rightKey) {
					makeCallBack(this._next)(evt);
				} else if (evt.keyCode === spaceKey) {
					makeCallBack(this._play)(evt);
				} else if (evt.keyCode === fKey) {
					makeCallBack(this._fullScreenToggle)(evt);
				} else if (this._hasKeyBeenPressed(evt, zoomOutKeys)) {
					makeCallBack(this._zoomToOriginal)(evt);
				} else if (this._hasKeyBeenPressed(evt, zoomInKeys)) {
					makeCallBack(this._zoomToFit)(evt);
				}
			}.bind(this));
		},

		/**
		 * Determines if a key has been pressed by comparing the event and the key
		 *
		 * @param evt
		 * @param {Array} keys
		 *
		 * @returns {boolean}
		 * @private
		 */
		_hasKeyBeenPressed: function (evt, keys) {
			var i, keysLength = keys.length;
			for (i = 0; i < keysLength; i++) {
				if (evt.keyCode === keys[i]) {
					return true;
				}
			}
			return false;
		},

		/**
		 * Starts the slideshow timer
		 *
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
		 *
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
		 * Starts the timed slideshow
		 *
		 * @private
		 */
		_play: function () {
			this.playing = true;
			this._playPauseButtonToggle();
			this._setTimeout();
		},

		/**
		 * Pauses the timed slideshow
		 *
		 * @private
		 */
		_pause: function () {
			this.playing = false;
			this._playPauseButtonToggle();
			this._clearTimeout();
		},

		/**
		 * Shows the play or pause button depending on circumstances
		 *
		 * @private
		 */
		_playPauseButtonToggle: function () {
			this.container.find('.play, .pause').toggle();
		},

		/**
		 * Shows the next slide
		 *
		 * @private
		 */
		_next: function () {
			this.slideshow.hideErrorNotification();
			this.zoomablePreview.reset();

			if (this.errorLoadingImage) {
				this.current -= 1;
			}
			this.current = (this.current + 1) % this.images.length;
			var next = (this.current + 1) % this.images.length;
			this._updateSlideshow(next);
		},

		/**
		 * Shows the previous slide
		 *
		 * @private
		 */
		_previous: function () {
			this.slideshow.hideErrorNotification();
			this.zoomablePreview.reset();

			this.current = (this.current - 1 + this.images.length) % this.images.length;
			var previous = (this.current - 1 + this.images.length) % this.images.length;
			this._updateSlideshow(previous);
		},

		/**
		 * Shows a new image in the slideshow and preloads the next in the list
		 *
		 * @param {number} imageId
		 * @private
		 */
		_updateSlideshow: function (imageId) {
			this.slideshow.show(this.current).then(function () {
				// Preloads the next image in the list
				this.slideshow.loadImage(this.images[imageId]);
			}.bind(this));
		},

		/**
		 * Exits the slideshow by going back in history
		 *
		 * @private
		 */
		_exit: function () {

			// Only modern browsers can manipulate history
			if (history && history.replaceState) {
				// We simulate a click on the back button in order to be consistent
				window.history.back();
			} else{
				// For ancient browsers supported in core
				this.stop();
			}
		},

		/**
		 * Launches fullscreen mode if the browser supports it
		 *
		 * @private
		 */
		_fullScreenToggle: function () {
			this.zoomablePreview.fullScreenToggle();
		},

		/**
		 * Resizes the image to its original size
		 *
		 * @private
		 */
		_zoomToOriginal: function () {
			this.zoomablePreview.zoomToOriginal();
		},

		/**
		 * Fits the image in the browser window
		 *
		 * @private
		 */
		_zoomToFit: function () {
			this.zoomablePreview.zoomToFit();
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

	SlideShow.Controls = Controls;
})(jQuery, SlideShow);
