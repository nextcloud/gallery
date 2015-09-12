/* global Gallery */
(function ($, Gallery) {
	"use strict";
	/**
	 * Stores the gallery configuration
	 *
	 * @param {{features: string[], mediatypes: string[]}} config
	 * @constructor
	 */
	var Config = function (config) {
		this.galleryFeatures = this._setGalleryFeatures(config.features);
		this.mediaTypes = this._setMediaTypes(config.mediatypes);
	};

	Config.prototype = {
		galleryFeatures: [],
		mediaTypes: [],
		cachedMediaTypesString: '',
		albumPermissions: null,
		albumInfo: null,
		albumSorting: null,
		albumError: false,
		infoLoaded: false,

		/**
		 * Returns the list of supported media types in a string
		 *
		 * @returns {string}
		 */
		getMediaTypes: function () {
			return this.cachedMediaTypesString;
		},

		/**
		 * Stores the configuration about the current album
		 *
		 * @param {{
		 * 	fileid: number,
		 * 	permissions: number,
		 * 	path: string,
		 * 	etag: string
		 * 	information,
		 * 	sorting,
		 * 	error: string
		 * }} albumConfig
		 */
		setAlbumConfig: function (albumConfig) {
			this.albumPermissions = this._setAlbumPermissions(albumConfig);
			this.albumInfo = this._setAlbumInfo(albumConfig);
			this.albumSorting = this._setAlbumSorting(albumConfig);
			this.albumError = albumConfig.error;
		},

		/**
		 * Updates the sorting order
		 */
		updateAlbumSorting: function (sortConfig) {
			this.albumSorting = {
				type: sortConfig.type,
				order: sortConfig.order,
				albumOrder: sortConfig.albumOrder
			};
		},

		/**
		 * Saves the list of features which have been enabled in the app
		 *
		 * @param {string[]} configFeatures
		 *
		 * @returns {Array}
		 * @private
		 */
		_setGalleryFeatures: function (configFeatures) {
			var features = [];
			var feature = null;
			if (!$.isEmptyObject(configFeatures)) {
				for (var i = 0, keys = Object.keys(configFeatures); i < keys.length; i++) {
					feature = keys[i];
					if (this._validateFeature(feature)) {
						features.push(feature);
					}
				}
			}

			return features;
		},

		/**
		 * Saves the list of supported media types
		 *
		 * @param {string[]} mediaTypes
		 *
		 * @returns {Array}
		 * @private
		 */
		_setMediaTypes: function (mediaTypes) {
			var supportedMediaTypes = [];
			var mediaType = null;
			var mediaTypesString = '';
			for (var i = 0, keys = Object.keys(mediaTypes); i < keys.length; i++) {
				mediaType = keys[i];
				if (this._validateMediaType(mediaType)) {
					mediaTypesString += mediaType + ';';
					supportedMediaTypes[mediaType] = mediaTypes[mediaType];
				}
			}
			this.cachedMediaTypesString = mediaTypesString.slice(0, -1);

			return supportedMediaTypes;
		},

		/**
		 * Determines if we can accept the feature in this browser environment
		 *
		 * @param {string} feature
		 *
		 * @returns {bool}
		 * @private
		 */
		_validateFeature: function (feature) {
			var isAcceptable = true;
			if (feature === 'native_svg' && Gallery.ieVersion !== false) {
				isAcceptable = false;
			}

			return isAcceptable;
		},

		/**
		 * Determines if we can accept the media type in this browser environment
		 *
		 * @param {string} mediaType
		 *
		 * @returns {bool}
		 * @private
		 */
		_validateMediaType: function (mediaType) {
			var isAcceptable = true;
			if (mediaType === 'image/svg+xml' && Gallery.ieVersion !== false) {
				isAcceptable = false;
			}

			return isAcceptable;
		},

		/**
		 * Saves the permissions for the current album
		 *
		 * @param {{
		 * 	fileid: number,
		 * 	permissions: number,
		 * 	path: string,
		 * 	etag: string
		 * 	information,
		 * 	sorting,
		 * 	error: string
		 * }} albumConfig
		 *
		 * @returns {{fileid: number, permissions: number}}
		 * @private
		 */
		_setAlbumPermissions: function (albumConfig) {
			return {
				fileid: albumConfig.fileid,
				permissions: albumConfig.permissions
			};
		},

		/**
		 * Saves the description and copyright information for the current album
		 *
		 * @param {{
		 * 	fileid: number,
		 * 	permissions: number,
		 * 	path: string,
		 * 	etag: string
		 * 	information,
		 * 	sorting,
		 * 	error: string
		 * }} albumConfig
		 *
		 * @returns {null||{
		 * 	description: string,
		 * 	descriptionLink: string,
		 * 	copyright: string,
		 * 	copyrightLink: string,
		 * 	filePath: string,
		 * 	inherit: bool,
		 * 	level: number
		 * }}
		 * @private
		 */
		_setAlbumInfo: function (albumConfig) {
			var albumPath = albumConfig.path;

			/**@type {{
			 * 	description: string,
			 * 	description_link: string,
			 * 	copyright: string,
			 * 	copyright_link: string,
			 * 	inherit: bool,
			 * 	level: number
			 * }}
			 */
			var albumInfo = albumConfig.information;
			var params = null;
			if (!$.isEmptyObject(albumInfo)) {
				var docPath = albumPath;
				var level = albumInfo.level;
				if (level > 0) {
					if (docPath.indexOf('/') !== -1) {
						var folders = docPath.split('/');
						folders = folders.slice(-0, -level);
						docPath = folders.join('/') + '/';
					} else {
						docPath = '';
					}
				}

				/* jshint camelcase: false */
				params = {
					description: albumInfo.description,
					descriptionLink: albumInfo.description_link,
					copyright: albumInfo.copyright,
					copyrightLink: albumInfo.copyright_link,
					filePath: docPath,
					inherit: albumInfo.inherit,
					level: albumInfo.level
				};
			}

			return params;
		},

		/**
		 * Saves the sorting configuration for the current album
		 *
		 * @param {{
		 * 	fileid: number,
		 * 	permissions: number,
		 * 	path: string,
		 * 	etag: string
		 * 	information,
		 * 	sorting,
		 * 	error: string
		 * }} albumConfig
		 *
		 * @returns {{type: string, order: string, albumOrder: string}}
		 * @private
		 */
		_setAlbumSorting: function (albumConfig) {
			var sortType = 'name';
			var sortOrder = 'asc';
			var albumSortOrder = 'asc';
			if (!$.isEmptyObject(albumConfig.sorting)) {
				if (!$.isEmptyObject(albumConfig.sorting.type)) {
					sortType = albumConfig.sorting.type;
				}
				if (!$.isEmptyObject(albumConfig.sorting.order)) {
					sortOrder = albumConfig.sorting.order;
					if (sortType === 'name') {
						albumSortOrder = sortOrder;
					}
				}
			}

			return {
				type: sortType,
				order: sortOrder,
				albumOrder: albumSortOrder
			};
		}
	};

	Gallery.Config = Config;
})(jQuery, Gallery);
