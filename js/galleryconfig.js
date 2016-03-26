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
		cachedFeaturesString: '',
		mediaTypes: [],
		cachedMediaTypesString: '',
		albumInfo: null,
		albumSorting: null,
		albumDesign: null,
		albumError: false,
		infoLoaded: false,

		/**
		 * Returns the list of supported features in a string
		 *
		 * @returns {string}
		 */
		getFeatures: function () {
			return this.cachedFeaturesString;
		},

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
		 * 	design,
		 * 	information,
		 * 	sorting,
		 * 	error: string
		 * }} albumConfig
		 * @param albumPath
		 */
		setAlbumConfig: function (albumConfig, albumPath) {
			this.albumInfo = this._setAlbumInfo(albumConfig, albumPath);
			this.albumSorting = this._setAlbumSorting(albumConfig);
			this.albumDesign = this._setAlbumDesign(albumConfig);
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
			var i, configFeaturesLength = configFeatures.length;
			if (configFeaturesLength) {
				for (i = 0; i < configFeaturesLength; i++) {
					feature = configFeatures[i];
					if (this._worksInCurrentBrowser(feature, 'native_svg')) {
						features.push(feature);
						Gallery.utility.addDomPurifyHooks();
					}
				}
			}
			this.cachedFeaturesString = features.join(';');

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
			var i, mediaTypesLength = mediaTypes.length;
			if (mediaTypesLength) {
				for (i = 0; i < mediaTypesLength; i++) {
					mediaType = mediaTypes[i];
					if (this._worksInCurrentBrowser(mediaType, 'image/svg+xml')) {
						supportedMediaTypes.push(mediaType);
					}
				}
			}
			this.cachedMediaTypesString = supportedMediaTypes.join(';');

			return supportedMediaTypes;
		},

		/**
		 * Determines if we can accept a specific config element in Internet Explorer
		 *
		 * @param {string} feature
		 * @param {string} validationRule
		 *
		 * @returns {boolean}
		 * @private
		 */
		_worksInCurrentBrowser: function (feature, validationRule) {
			var isAcceptable = true;
			if (feature === validationRule &&
				(Gallery.ieVersion !== false && Gallery.ieVersion !== 'edge')) {
				isAcceptable = false;
			}

			return isAcceptable;
		},

		/**
		 * Saves the description and copyright information for the current album
		 *
		 * @param {{
		 * 	design,
		 * 	information,
		 * 	sorting,
		 * 	error: string
		 * }} albumConfig
		 * @param albumPath
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
		_setAlbumInfo: function (albumConfig, albumPath) {
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
					level: level
				};
			}

			return params;
		},

		/**
		 * Saves the description and copyright information for the current album
		 *
		 * @param {{
		 * 	design,
		 * 	information,
		 * 	sorting,
		 * 	error: string
		 * }} albumConfig
		 *
		 * @returns {null||{
		 * 	background: string,
		 * 	inherit: bool,
		 * 	level: number
		 * }}
		 * @private
		 */
		_setAlbumDesign: function (albumConfig) {
			/**@type {{
			 * 	background: string,
			 * 	inherit: bool,
			 * 	level: number
			 * }}
			 */
			var albumDesign = albumConfig.design;
			var params = null;
			if (!$.isEmptyObject(albumDesign)) {
				params = {
					background: albumDesign.background,
					inherit: albumDesign.inherit,
					level: albumDesign.level
				};
			}

			return params;
		},

		/**
		 * Saves the sorting configuration for the current album
		 *
		 * @param {{
		 * 	design,
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
