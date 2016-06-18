/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Robin Appelman <icewind1991@gmail.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Robin Appelman 2012-2015
 * @copyright Olivier Paroz 2014-2016
 */

/**
 * Wrapper for server side events
 * (http://en.wikipedia.org/wiki/Server-sent_events)
 */

/* global EventSource, oc_requesttoken, Gallery */
(function (oc_requesttoken) {
	"use strict";
	/**
	 * Create a new event source
	 *
	 * Comes from core and is thus not modified too much in order to be able to easily backport
	 * changes in core
	 *
	 * @param {string} src
	 * @param {object} [data] to be send as GET
	 * @constructor
	 */
	var CustomEventSource = function (src, data) {
		var dataStr = '';
		var joinChar;
		this.typelessListeners = [];
		if (data) {
			for (var i = 0, keys = Object.keys(data); i < keys.length; i++) {
				dataStr += keys[i] + '=' + encodeURIComponent(data[keys[i]]) + '&';
			}
		}
		/* jshint camelcase: false */
		dataStr += 'requesttoken=' + encodeURIComponent(oc_requesttoken);
		if (typeof EventSource !== 'undefined') {
			joinChar = '&';
			if (src.indexOf('?') === -1) {
				joinChar = '?';
			}
			var options = {};
			if (EventSource.isPolyfill !== undefined) {
				// 10 thumbnails * 200k per thumbnail
				options.bufferSizeLimit = 10 * 200 * 1024;
				//options.loggingEnabled = true;
			}
			this.source = new EventSource(src + joinChar + dataStr, options);
			this.source.onmessage = function (e) {
				for (var i = 0; i < this.typelessListeners.length; i++) {
					this.typelessListeners[i](JSON.parse(e.data));
				}
			}.bind(this);
			//add close listener
			this.listen('__internal__', function (data) {
				if (data === 'close') {
					this.close();
				}
			}.bind(this));
		}
	};

	CustomEventSource.prototype = {
		typelessListeners: [],

		/**
		 * Listen to a given type of events.
		 *
		 * @param {String} type event type
		 * @param {Function} callback event callback
		 */
		listen: function (type, callback) {
			if (callback && callback.call) {
				if (type) {
					this.source.addEventListener(type, function (e) {
						if (typeof e.data !== 'undefined') {
							callback(JSON.parse(e.data));
						} else {
							callback('');
						}
					}, false);
				} else {
					this.typelessListeners.push(callback);
				}
			}
		},
		/**
		 * Closes this event source.
		 */
		close: function () {
			if (typeof this.source !== 'undefined') {
				this.source.close();
			}
		}
	};

	Gallery.EventSource = CustomEventSource;
})(oc_requesttoken);
