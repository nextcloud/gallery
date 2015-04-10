/* global $, OC, t, SlideShow, bigshot*/
(function () {

	/**
	 * Creates a zoomable preview
	 *
	 * @param container
	 * @constructor
	 */
	var ZoomablePreview = function (container) {
		this.container = container;
		this.element = this.container.get(0);

		this._detectFullscreen();
		this._setupControls();

		$(window).resize(function () {
			this._zoomDecider();
		}.bind(this));
	};

	ZoomablePreview.prototype = {
		container: null,
		element: null,
		zoomable: null,
		fullScreen: null,
		canFullScreen: false,
		currentImage: null,
		maxZoom: 3,
		smallImageDimension: 200 / window.devicePixelRatio,
		smallImageScale: 2,

		/**
		 * Launches the Bigshot zoomable preview
		 *
		 * @param image
		 * @param currentImage
		 */
		startBigshot: function (image, currentImage) {
			this.currentImage = currentImage;
			if (this.zoomable !== null) {
				this.zoomable.dispose();
				this.zoomable = null;
			}
			var maxZoom = this.maxZoom;
			var imgWidth = image.naturalWidth / window.devicePixelRatio;
			var imgHeight = image.naturalHeight / window.devicePixelRatio;
			if (imgWidth < this.smallImageDimension && imgHeight < this.smallImageDimension) {
				maxZoom += 3;
				this.currentImage.isSmallImage = true;
			}
			this.zoomable = new bigshot.SimpleImage(new bigshot.ImageParameters({
				container: this.element,
				maxZoom: maxZoom,
				minZoom: 0,
				touchUI: false,
				width: imgWidth,
				height: imgHeight
			}), image);
			if (this.fullScreen === null && this.currentImage.mimeType !== 'image/svg+xml') {
				this._resetZoom();
			}

			// prevent zoom-on-doubleClick
			this.zoomable.addEventListener('dblclick', function (ie) {
				ie.preventDefault();
			});
		},

		/**
		 * Resets the element for the next image to be displayed
		 */
		reset: function () {
			if (this.zoomable !== null) {
				this.zoomable.stopFlying();
				this._resetZoom();
			}
		},

		/**
		 * Throws away the zoomable preview
		 */
		stop: function () {
			if (this.fullScreen !== null) {
				this._fullScreenExit();
			}
			if (this.zoomable !== null) {
				this.zoomable.dispose();
				this.zoomable = null;
			}
		},

		/**
		 * Launches fullscreen mode if the browser supports it
		 */
		fullScreenToggle: function () {
			if (this.zoomable === null) {
				return;
			}
			if (this.fullScreen !== null) {
				this._fullScreenExit();
			} else {
				this._fullScreenStart();
			}
		},

		/**
		 * Resizes the image to its original size
		 */
		zoomToOriginal: function () {
			if (this.zoomable === null) {
				return;
			}
			if (this.currentImage.isSmallImage) {
				this.zoomable.flyTo(0, 0, this.smallImageScale, true);
			} else {
				this.zoomable.flyTo(0, 0, 0, true);
			}
		},

		/**
		 * Fits the image in the browser window
		 */
		zoomToFit: function () {
			if (this.zoomable !== null) {
				this.zoomable.flyZoomToFit();
			}
		},

		/**
		 * Detect fullscreen capability
		 * @private
		 */
		_detectFullscreen: function () {
			this.canFullScreen = this.element.requestFullscreen !== undefined ||
			this.element.mozRequestFullScreen !== undefined ||
			this.element.webkitRequestFullscreen !== undefined ||
			this.element.msRequestFullscreen !== undefined;
		},

		/**
		 * Makes UI controls work on touchscreens. Pinch only works on iOS
		 * @private
		 */
		_setupControls: function () {
			var browser = new bigshot.Browser();
			this.container.children('input').each(function (i, e) {
				browser.registerListener(e, 'click', browser.stopEventBubblingHandler(), false);
				browser.registerListener(e, 'touchstart', browser.stopEventBubblingHandler(),
					false);
				browser.registerListener(e, 'touchend', browser.stopEventBubblingHandler(), false);
			});
		},

		/**
		 * Determines whether the image should be shown at its original size or if it should fill
		 * the screen
		 * @private
		 */
		_zoomDecider: function () {
			if (this.fullScreen === null && this.currentImage.mimeType !== 'image/svg+xml') {
				this.zoomToOriginal();
			} else {
				this.zoomToFit();
			}
		},

		/**
		 * Resets the image to its original zoomed size
		 * @private
		 */
		_resetZoom: function () {
			if (this.zoomable === null) {
				return;
			}
			if (this.currentImage.isSmallImage) {
				this.zoomable.setZoom(this.smallImageScale, true);
			} else {
				this.zoomable.setZoom(0, true);
			}
		},

		/**
		 * Starts the fullscreen previews
		 * @private
		 */
		_fullScreenStart: function () {
			if (!this.canFullScreen) {
				return;
			}
			this.fullScreen = new bigshot.FullScreen(this.element);
			this.fullScreen.open();
			this.fullScreen.addOnClose(function () {
				this._fullScreenExit();
			}.bind(this));
		},

		/**
		 * Stops the fullscreen previews
		 * @private
		 */
		_fullScreenExit: function () {
			if (this.fullScreen === null) {
				return;
			}
			this.fullScreen.close();
			this.fullScreen = null;
			this._zoomDecider();

		}
	};

	SlideShow.ZoomablePreview = ZoomablePreview;
})();
