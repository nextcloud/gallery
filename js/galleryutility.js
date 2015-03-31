/* global OC, $, t, Gallery, oc_requesttoken */
(function () {
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
		 * @returns {*}
		 */
		getIeVersion: function () {
			if (navigator.userAgent.indexOf("MSIE") > 0) {
				return 'old';
			} else if (!!navigator.userAgent.match(/Trident.*rv[ :]*11\./)) {
				return 'modern';
			}

			return false;
		},

		/**
		 * Shows a notification to IE users, letting them know that they should use another browser
		 * in order to get the best experience
		 *
		 * @param {string} age
		 */
		showIeWarning: function (age) {
			var line1 = t('gallery', 'This application may not work properly on your browser.');
			var line2 = t('gallery',
				'For an improved experience, please install one of the following alternatives');
			var timeout = 15;
			if (age === 'old') {
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
		 * Returns the token alowing access to files
		 *
		 * @returns {string}
		 */
		getRequestToken: function () {
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
			var width = $(window).width() * window.devicePixelRatio;
			var height = $(window).height() * window.devicePixelRatio;
			/* jshint camelcase: false */
			var params = {
				c: etag,
				width: width,
				height: height,
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
						return a.path.toLowerCase().localeCompare(b.path.toLowerCase());
					};
				}
				//sortByNameDes
				return function (a, b) {
					return b.path.toLowerCase().localeCompare(a.path.toLowerCase());
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
})();