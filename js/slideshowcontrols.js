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
	 * @param {Array} features
	 * @constructor
	 */
	var Controls = function (slideshow, container, zoomablePreview, interval, features) {
		this.slideshow = slideshow;
		this.container = container;
		this.zoomablePreview = zoomablePreview;
		this.progressBar = container.find('.progress');
		this.interval = interval || 5000;
		if (features.indexOf('background_colour_toggle') > -1) {
			this.backgroundToggle = true;
		}
	};

	Controls.prototype = {
		current: 0,
		errorLoadingImage: false,
		playTimeout: 0,
		playing: false,
		active: false,
		backgroundToggle: false,

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
					evt.preventDefault();
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
		 * @param {boolean} autoPlay
		 */
		update: function (images, autoPlay) {
			this.images = images;
			this.active = true;
			this.showButton('.play');
			this.hideButton('.pause, .progress');
			this.playing = false;

			// Hide prev/next and play buttons when we only have one pic
			this.container.find('.next, .previous, .play').toggle(this.images.length > 1);

			// Hide the action buttons until we have something to show
			this.hideActionButtons();

			if (autoPlay) {
				this._playPauseToggle();
			}
		},

		/**
		 * Initialises local variables when the show starts
		 *
		 * @param {number} currentImageId
		 */
		show: function (currentImageId) {
			var currentImage = this.images[currentImageId];
			this.current = currentImageId;
			this.errorLoadingImage = false;
			if (this.playing) {
				this._setTimeout();
			}
			this._setName(currentImage.name);
		},

		/**
		 * Stops and hides the slideshow
		 */
		stop: function () {
			this._setName('');
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
		 * @param {boolean} errorLoadingImage
		 */
		updateControls: function (images, errorLoadingImage) {
			this.images = images;
			this.errorLoadingImage = errorLoadingImage;
		},

		/**
		 * Shows the action buttons
		 */
		showActionButtons: function (transparent) {
			if (transparent) {
				this._showBackgroundToggle();
			}
			this.showButton('.downloadImage');
		},

		/**
		 * Hides the action buttons
		 */
		hideActionButtons: function () {
			this.hideButton('.changeBackground');
			this.hideButton('.downloadImage');
		},

		/**
		 * Shows a button which has been hidden
		 */
		showButton: function (button) {
			this.container.find(button).removeClass('hidden');
		},

		/**
		 * Hides a button
		 *
		 * @param button
		 */
		hideButton: function (button) {
			this.container.find(button).addClass('hidden');
		},

		/**
		 * Removes a button
		 *
		 * @param button
		 */
		removeButton: function (button) {
			this.container.find(button).remove();
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
			this.container.children('.pause, .play').click(makeCallBack(this._playPauseToggle));
			this.progressBar.click(makeCallBack(this._playPauseToggle));
			this.container.children('.previous, .next, .menu, .name').on(
				'mousewheel DOMMouseScroll mousemove', function (evn) {
					this.container.children('.bigshotContainer')[0].dispatchEvent(
						new WheelEvent(evn.originalEvent.type, evn.originalEvent));
				}.bind(this));
		},

		/**
		 * Sets up additional buttons
		 *
		 * @param {Function} makeCallBack
		 * @private
		 */
		_specialButtonSetup: function (makeCallBack) {
			this.container.find('.downloadImage').click(makeCallBack(this._getImageDownload));
			this.container.find('.menu').width = 52;
			if (this.backgroundToggle) {
				this.container.find('.changeBackground').click(
					makeCallBack(this._toggleBackground));
				this.container.find('.menu').width += 52;
			} else {
				this.hideButton('.changeBackground');
			}


		},

		/**
		 * Shows the background colour switcher, if activated in the configuration
		 */
		_showBackgroundToggle: function () {
			if (this.backgroundToggle) {
				this.showButton('.changeBackground');
			}
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
					makeCallBack(this._playPauseToggle)(evt);
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
		 * Starts/stops autoplay and shows/hides the play/pause buttons
		 *
		 * @private
		 */
		_playPauseToggle: function () {
			if (this.playing === true) {
				this.playing = false;
				this._clearTimeout();
			} else {
				this.playing = true;
				this._setTimeout();
			}

			this.container.find('.play, .pause, .progress').toggleClass('hidden');
		},

		/**
		 * Shows the next slide
		 *
		 * @private
		 */
		_next: function () {
			this._setName('');
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
			this._setName('');
			this.slideshow.hideErrorNotification();
			this.zoomablePreview.reset();

			this.current = (this.current - 1 + this.images.length) % this.images.length;
			var previous = (this.current - 1 + this.images.length) % this.images.length;
			this._updateSlideshow(previous);
		},

		/**
		 * Asks the slideshow for the next image
		 *
		 * @param {number} imageId
		 * @private
		 */
		_updateSlideshow: function (imageId) {
			this.slideshow.next(this.current, this.images[imageId]);
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
			} else {
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
		},

		/**
		 * Shows the filename of the current image
		 * @param {string} imageName
		 * @private
		 */
		_setName: function (imageName) {
			var nameElement = this.container.find('.title');
			nameElement.text(imageName);
		}
	};

	SlideShow.Controls = Controls;
})(jQuery, SlideShow);
