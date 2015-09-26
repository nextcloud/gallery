/* global oc_requesttoken, Gallery */
(function ($, OC, t, oc_requesttoken, Gallery) {
	"use strict";
	/**
	 * Contains utility methods
	 *
	 * @fixme OC.generateUrl, OC.buildQueryString, OC.Notification are private APIs
	 *
	 * @constructor
	 */
	var Utility = function () {
	};

	Utility.prototype = {
		/**
		 * Detects if the browser is a recent or an old version of Internet Explorer
		 *
		 * @returns {string|bool}
		 */
		getIeVersion: function () {
			// Blocking IE8
			if ($('html').is('.ie8')) {
				return 'unsupportedIe';
			} else if (navigator.userAgent.indexOf("MSIE") > 0) {
				return 'oldIe';
			} else if ((!!navigator.userAgent.match(/Trident.*rv[ :]*11\./)) ||
				(navigator.userAgent.indexOf("Edge/") > 0)) {
				return 'modernIe';
			}

			return false;
		},

		/**
		 * Shows a notification to IE users, letting them know that they should use another browser
		 * in order to get the best experience
		 *
		 * @param {string} version
		 */
		showIeWarning: function (version) {
			var line1 = t('gallery', 'This application may not work properly on your browser.');
			var line2 = t('gallery',
				'For an improved experience, please install one of the following alternatives');
			var timeout = 15;
			if (version === 'unsupportedIe') {
				line1 = t('gallery', 'Your browser is not supported!');
				line2 = t('gallery', 'please install one of the following alternatives');
				timeout = 60;
			}

			var recommendedBrowsers = '</br>' +
				'<a href="http://www.getfirefox.com"><strong>Mozilla Firefox</strong></a> or ' +
				'<a href="https://www.google.com/chrome/"><strong>Google Chrome</strong></a>' +
				'</br>';

			var text = '<strong>' + line1 + '</strong></br>' + line2 + recommendedBrowsers;
			this.showHtmlNotification(text, timeout);
		},

		/**
		 * Shows a notification at the top of the screen
		 *
		 * @param {string} text
		 * @param {int} timeout
		 */
		showHtmlNotification: function (text, timeout) {
			var options = {
				timeout: timeout,
				isHTML: true
			};
			OC.Notification.showTemporary(text, options);
		},

		/**
		 * Returns the token allowing access to files shared via link
		 *
		 * @returns {string}
		 */
		getPublicToken: function () {
			var element = $('#gallery');
			var token;

			if (element.data('token')) {
				token = element.data('token');
			}

			if (element.data('requesttoken')) {
				/* jshint camelcase: false */
				oc_requesttoken = element.data('requesttoken');
			}

			return token;
		},

		/**
		 * Builds the URL which will retrieve a large preview of the file
		 *
		 * @fixme we cannot get rid of oc_requesttoken parameter as it's missing from the headers
		 *
		 * @param {number} fileId
		 * @param {number} etag
		 *
		 * @return {string}
		 */
		getPreviewUrl: function (fileId, etag) {
			var width = Math.floor(screen.width * window.devicePixelRatio);
			var height = Math.floor(screen.height * window.devicePixelRatio);

			/* Find value of longest edge. */
			var longEdge = Math.max(width, height);

			/* Find the next larger image size. */
			if (longEdge % 100 !== 0) {
				longEdge = ( longEdge + 100 ) - ( longEdge % 100 );
			}

			/* jshint camelcase: false */
			var params = {
				c: etag,
				width: longEdge,
				height: longEdge,
				requesttoken: oc_requesttoken
			};
			return this.buildGalleryUrl('preview', '/' + fileId, params);
		},

		/**
		 * Builds a URL pointing to one of the app's controllers
		 *
		 * @param {string} endPoint
		 * @param {undefined|string} path
		 * @param params
		 *
		 * @returns {string}
		 */
		buildGalleryUrl: function (endPoint, path, params) {
			if (path === undefined) {
				path = '';
			}
			var extension = '';
			if (Gallery.token) {
				params.token = Gallery.token;
				extension = '.public';
			}
			var query = OC.buildQueryString(params);
			return OC.generateUrl('apps/' + Gallery.appName + '/' + endPoint + extension + path,
					null) +
				'?' +
				query;
		},

		/**
		 * Builds a URL pointing to one of the files' controllers
		 *
		 * @param {string} path
		 * @param {string} files
		 *
		 * @returns {string}
		 */
		buildFilesUrl: function (path, files) {
			var subUrl = '';
			var params = {
				path: path,
				files: files
			};

			if (Gallery.token) {
				params.token = Gallery.token;
				subUrl = 's/{token}/download?path={path}&files={files}';
			} else {
				subUrl = 'apps/files/ajax/download.php?dir={path}&files={files}';
			}

			return OC.generateUrl(subUrl, params);
		},

		/**
		 * Sorts arrays based on name or date
		 *
		 * @param {string} sortType
		 * @param {string} sortOrder
		 *
		 * @returns {Function}
		 */
		sortBy: function (sortType, sortOrder) {
			if (sortType === 'name') {
				if (sortOrder === 'asc') {
					//sortByNameAsc
					return function (a, b) {
						return OC.Util.naturalSortCompare(a.path, b.path);
					};
				}
				//sortByNameDes
				return function (a, b) {
					return -OC.Util.naturalSortCompare(a.path, b.path);
				};
			}
			if (sortType === 'date') {
				if (sortOrder === 'asc') {
					//sortByDateAsc
					return function (a, b) {
						return b.mTime - a.mTime;
					};
				}
				//sortByDateDes
				return function (a, b) {
					return a.mTime - b.mTime;
				};
			}
		}
	};

	Gallery.Utility = Utility;
})(jQuery, OC, t, oc_requesttoken, Gallery);
