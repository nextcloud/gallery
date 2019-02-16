/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */
/* global DOMPurify, oc_requesttoken, Gallery */

// The Utility class can also be loaded by the Files app
window.Gallery = window.Gallery || {};

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
		 * @returns {string|boolean}
		 */
		getIeVersion: function () {
			// Blocking IE8
			if ($('html').is('.ie8')) {
				return 'unsupportedIe';
			} else if (navigator.userAgent.indexOf("MSIE") > 0) {
				return 'unsupportedIe';
			} else if (!!navigator.userAgent.match(/Trident.*rv[ :]*11\./)) {
				return 'modernIe';
			} else if (navigator.userAgent.indexOf("Edge/") > 0) {
				return 'edge';
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
		 * Returns the host we can use for WebDAV
		 * 
		 * On public galleries, we need to provide the token as authorization
		 *
		 * @returns {string}
		 */
		getWebdavHost: function () {
			var host = OC.getHost();
			if (Gallery.token) {
				host = Gallery.token + '@' + host;
			}

			return host;
		},

		/**
		 * Returns the WebDAV endpoint we can use for files operations
		 *
		 * @returns {string}
		 */
		getWebdavRoot: function () {
			var root = OC.linkToRemoteBase('webdav');
			if (Gallery.token) {
				root = root.replace('remote.php', 'public.php');
			}

			return root;
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
			var width = Math.ceil(screen.width * window.devicePixelRatio);
			var height = Math.ceil(screen.height * window.devicePixelRatio);

			/* Find value of longest edge. */
			var longEdge = Math.max(width, height);

			/* Find the next larger image size. */
			if (longEdge % 100 !== 0) {
				longEdge = ( longEdge + 100 ) - ( longEdge % 100 );
			}

			/* jshint camelcase: false */
			var params = {
				width: longEdge,
				height: longEdge,
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
				subUrl = 's/{token}/download?dir={path}&files={files}';
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
		},

		/**
		 * Adds hooks to DOMPurify
		 */
		addDomPurifyHooks: function () {
			// allowed URI schemes
			var whitelist = ['http', 'https'];

			// build fitting regex
			var regex = new RegExp('^(' + whitelist.join('|') + '):', 'gim');

			DOMPurify.addHook('afterSanitizeAttributes', function (node) {
				// This hook enforces URI scheme whitelist
				// @link
				// https://github.com/cure53/DOMPurify/blob/master/demos/hooks-scheme-whitelist.html

				// build an anchor to map URLs to
				var anchor = document.createElement('a');

				// check all href attributes for validity
				if (node.hasAttribute('href')) {
					anchor.href = node.getAttribute('href');
					if (anchor.protocol && !anchor.protocol.match(regex)) {
						node.removeAttribute('href');
					}
				}
				// check all action attributes for validity
				if (node.hasAttribute('action')) {
					anchor.href = node.getAttribute('action');
					if (anchor.protocol && !anchor.protocol.match(regex)) {
						node.removeAttribute('action');
					}
				}
				// check all xlink:href attributes for validity
				if (node.hasAttribute('xlink:href')) {
					anchor.href = node.getAttribute('xlink:href');
					if (anchor.protocol && !anchor.protocol.match(regex)) {
						node.removeAttribute('xlink:href');
					}
				}

				// This hook restores the proper, standard namespace in SVG files
				var encodedXmlns, decodedXmlns;

				// Restores namespaces which were put in the DOCTYPE by Illustrator
				if (node.hasAttribute('xmlns') && node.getAttribute('xmlns') === '&ns_svg;') {
					encodedXmlns = node.getAttribute('xmlns');
					decodedXmlns = encodedXmlns.replace('&ns_svg;', 'http://www.w3.org/2000/svg');
					node.setAttribute('xmlns', decodedXmlns);
				}
				if (node.hasAttribute('xmlns:xlink') &&
					node.getAttribute('xmlns:xlink') === '&ns_xlink;') {
					encodedXmlns = node.getAttribute('xmlns:xlink');
					decodedXmlns =
						encodedXmlns.replace('&ns_xlink;', 'http://www.w3.org/1999/xlink');
					node.setAttribute('xmlns:xlink', decodedXmlns);
				}
			});
		}
	};

	Gallery.Utility = Utility;
})(jQuery, OC, t, oc_requesttoken, Gallery);
