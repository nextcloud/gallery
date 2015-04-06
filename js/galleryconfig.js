/* global $ */
/**
 * Stores the configuration about the current album
 * @constructor
 */
var GalleryConfig = function (albumConfig) {
	this.albumPermissions = this.setAlbumPermissions(albumConfig);
	this.albumInfo = this.setAlbumInfo(albumConfig);
	this.sorting = this.setAlbumSorting(albumConfig);
};

GalleryConfig.prototype = {
	/**
	 * Saves the permissions for the current album
	 *
	 * @param albumConfig
	 *
	 * @returns {{fileid: *, permissions: *}}
	 */
	setAlbumPermissions: function (albumConfig) {
		return {
			fileid: albumConfig.fileid,
			permissions: albumConfig.permissions
		};
	},

	/**
	 * Saves the description and copyright information for the current album
	 *
	 * @param albumConfig
	 *
	 * @returns {{}}
	 */
	setAlbumInfo: function (albumConfig) {
		var albumPath = albumConfig.path;
		var albumInfo = albumConfig.information;
		var params = {};
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

			params = {
				description: albumInfo.description,
				descriptionLink: albumInfo.description_link,
				copyright: albumInfo.copyright,
				copyrightLink: albumInfo.copyright_link,
				filePath: docPath
			};
		}

		return params;
	},

	/**
	 * Saves the sorting configuration for the current album
	 *
	 * @param albumConfig
	 *
	 * @returns {{type: string, order: string, albumOrder: string}}
	 */
	setAlbumSorting: function (albumConfig) {
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
	},

	/**
	 * Saves the fact that the description has been successfully loaded
	 */
	setInfoLoaded: function () {
		this.albumInfo.infoLoaded = true;
	},

	/**
	 * Updates the sorting order
	 */
	updateSorting: function (sortType, sortOrder, albumSortOrder) {
		this.sorting = {
			type: sortType,
			order: sortOrder,
			albumOrder: albumSortOrder
		};
	},

	/**
	 * Retrieves the permissions for the current album
	 *
	 * @returns {*|{fileid, permissions}|{fileid: *, permissions: *}}
	 */
	getAlbumPermissions: function () {
		return this.albumPermissions;
	},

	/**
	 * Retrieves the description and copyright information for the current album
	 *
	 * @returns {*|{}}
	 */
	getAlbumInfo: function () {
		return this.albumInfo;
	},

	/**
	 * Retrieves the sorting configuration for the current album
	 *
	 * @returns {*|{type, order, albumOrder}|{type: string, order: string, albumOrder: string}}
	 */
	getAlbumSorting: function () {
		return this.sorting;
	}
};