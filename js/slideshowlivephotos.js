/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author François Sylvestre <francoissylv@gmail.com>
 *
 * @copyright François Sylvestre 2017
 */
/* global SlideShow, bigshot*/
(function ($, SlideShow, LivePhotosKit, OC) {
	"use strict";
	/**
	 * Creates a zoomable preview
	 *
	 * @param {*} container
	 * @constructor
	 */
	var LivePreview = function (container) {
		this.container = container;
		this.element = this.container.get(0);
		this.livePhotoContainer = container.find('.livePhotoContainer');
		this.livePhotoContainer.css({display: 'block', width: '1px', height: '1px'});
		this.player = LivePhotosKit.createPlayer();

		this.livePhotoContainer.append(this.player);
		// this.livePhotoContainer.css('display', 'none');

		this._detectFullscreen();
		this._setupControls();

		// Reset image position and size on orientation change
		var self = this;
		$(window).bind('orientationchange resize', function () {
			self._resetView();
		});
	};

	LivePreview.prototype = {
		container: null,
		element: null,
		fullScreen: null,
		currentImage: null,
		mimeType: null,
		smallImageDimension: 200 / window.devicePixelRatio,
		smallImageScale: 2,

		/**
		 * Launches the Bigshot zoomable preview
		 *
		 * @param {*} image
		 * @param {number} currentImage
		 * @param {string} mimeType
		 */
		startLivePreview: function (image, currentImage) {
			var defer = $.Deferred();
			if (image.mimeType === "image/jpeg" && image.name.substr(-5).toLowerCase().indexOf('.jpg') !== -1) {
				var videoExt = '.mov';
				if (image.name.substr(-4) === '.JPG')
					videoExt = '.MOV';
				var videoUrl = OC.generateUrl(['../remote.php/webdav/', encodeURI(image.path.substr(0, image.path.length - 4) + videoExt)].join(''));
				$.ajax({
					url: videoUrl,
					type: 'HEAD',
					success: function() {
						this.livePhotoContainer.css({display: 'block'});
						this.currentImage = currentImage;
						this.mimeType = image.mimeType;

						this._resetView();

						this.player.photoSrc = this.currentImage.src;
						this.player.videoSrc = videoUrl;
						defer.resolve();
					}.bind(this),
					error: function() {
						this.livePhotoContainer.css('display', 'none');
						defer.reject();
					}.bind(this)
				});
			} else {
				defer.reject();
			}
			return defer.promise();
		},

		/**
		 * Resets the element for the next image to be displayed
		 */
		reset: function () {
			this.livePhotoContainer.css('display', 'none');
			this.player.photoSrc = null;
			this.player.videoSrc = null;
		},

		/**
		 * Throws away the zoomable preview
		 */
		stop: function () {
			if (this.fullScreen !== null) {
				this._fullScreenExit();
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
			this.player.playbackStyle = LivePhotosKit.PlaybackStyle.FULL;
		},

		/**
		 * Resets the image to its original zoomed size
		 *
		 * @private
		 */
		_resetView: function () {
			var imgWidth = this.currentImage.naturalWidth / window.devicePixelRatio;
			var imgHeight = this.currentImage.naturalHeight / window.devicePixelRatio;

			var origSizeW = imgWidth;
			var origSizeH = imgHeight;
			var ratioVt=(origSizeW/origSizeH);
			var ratioHz=(origSizeH/origSizeW);
			var winW = $(window).width();
			var winH = $(window).height();
			var screenSizeW=Math.round(winW);
			var screenSizeH=Math.round(winH);
			var wantedWidth, wantedHeight, wantedLeft, wantedTop;

			if (origSizeW>=origSizeH) {
				var newHeight = Math.round(screenSizeW*ratioHz);
				if (newHeight <= screenSizeH){
					wantedHeight = newHeight;
					wantedWidth = screenSizeW;
				} else{
					wantedHeight = screenSizeH;
					wantedWidth = Math.round(screenSizeH*ratioVt);
				}
			} else{
				wantedHeight = screenSizeH;
				wantedWidth = Math.round(screenSizeH*ratioVt);
			}
			wantedLeft = Math.round((screenSizeW - wantedWidth) / 2);
			wantedTop = Math.round((screenSizeH - wantedHeight) / 2);

			$(this.livePhotoContainer.children().get(0)).css({'width': wantedWidth + 'px', 'height': wantedHeight + 'px', 'top': wantedTop + 'px', 'left': wantedLeft + 'px'});
		},

		/**
		 * Starts the fullscreen previews
		 *
		 * @private
		 */
		_fullScreenStart: function () {
			this._resetView();
		},

		/**
		 * Stops the fullscreen previews
		 *
		 * @private
		 */
		_fullScreenExit: function () {
			this._resetView();
		}
	};

	SlideShow.LivePreview = LivePreview;
})(jQuery, SlideShow, LivePhotosKit, OC);
