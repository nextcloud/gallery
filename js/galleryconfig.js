/* global $, Gallery */
(function () {
	/**
	 * Stores the configuration about the current album
	 *
	 * @param {Object} albumConfig
	 * @constructor
	 */
	var Config = function (albumConfig) {
		this.albumPermissions = this.setAlbumPermissions(albumConfig);
		this.albumInfo = this.setAlbumInfo(albumConfig);
		this.sorting = this.setAlbumSorting(albumConfig);
		this.error = albumConfig.error;
	};

	Config.prototype = {
		albumPermissions: null,
		albumInfo: null,
		sorting: null,
		error: false,
		infoLoaded: false,

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
		 * Updates the sorting order
		 */
		updateSorting: function (sortType, sortOrder, albumSortOrder) {
			this.sorting = {
				type: sortType,
				order: sortOrder,
				albumOrder: albumSortOrder
			};
		}
	};

	Gallery.Config = Config;
})();
