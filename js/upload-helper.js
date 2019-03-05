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
/* global _, Gallery, Thumbnails */
/**
 * OCA.FileList methods needed for file uploading
 *
 * This hack makes it possible to use the Files scripts as is, without having to import and
 * maintain them in Gallery
 *
 * Empty methods are for the "new" button, if we want to implement that one day
 *
 * @type {{findFile: FileList.findFile, createFile: FileList.createFile,
 *     getCurrentDirectory: FileList.getCurrentDirectory, getUploadUrl:
 *     FileList.getUploadUrl}}
 */
var FileList = {
	/**
	 * Makes sure the filename does not exist
	 *
	 * Gives an early chance to the user to abort the action, before uploading everything to the
	 * server.
	 * Albums are not supported as we don't have a full list of images contained in a sub-album
	 *
	 * @param fileName
	 * @returns {*}
	 */
	findFile: function (fileName) {
		"use strict";
		var path = Gallery.currentAlbum + '/' + fileName;
		var galleryImage = Gallery.imageMap[path];
		if (galleryImage) {
			var fileInfo = {
				name: fileName,
				directory: Gallery.currentAlbum,
				path: path,
				etag: galleryImage.etag,
				mtime: galleryImage.mTime * 1000, // Javascript gives the Epoch time in milliseconds
				size: galleryImage.size
			};
			return fileInfo;
		} else {
			return null;
		}
	},

	/**
	 * Create an empty file inside the current album.
	 *
	 * @param {string} name name of the file
	 *
	 * @return {Promise} promise that will be resolved after the
	 * file was created
	 *
	 */
	createFile: function(name) {
		var self = this;
		var deferred = $.Deferred();
		var promise = deferred.promise();

		OCA.Files.isFileNameValid(name);

		var targetPath = this.getCurrentDirectory() + '/' + name;

		//Check if file already exists
		if(Gallery.imageMap[targetPath]) {
			OC.Notification.showTemporary(
				t('files', 'Could not create file "{file}" because it already exists', {file: name})
			);
			deferred.reject();
			return promise;
		}

		Gallery.filesClient.putFileContents(
			targetPath,
			'',
			{
				contentType: 'text/plain',
				overwrite: true
			}
			)
			.done(function() {
				// TODO: error handling / conflicts
				Gallery.filesClient.getFileInfo(
					targetPath, {
						properties: self.findFile(targetPath)
					}
					)
					.then(function(status, data) {
						deferred.resolve(status, data);
					})
					.fail(function(status) {
						OC.Notification.showTemporary(t('files', 'Could not create file "{file}"', {file: name}));
						deferred.reject(status);
					});
			})
			.fail(function(status) {
				if (status === 412) {
					OC.Notification.showTemporary(
						t('files', 'Could not create file "{file}" because it already exists', {file: name})
					);
				} else {
					OC.Notification.showTemporary(t('files', 'Could not create file "{file}"', {file: name}));
				}
				deferred.reject(status);
			});

		return promise;
	},


	/**
	 * Retrieves the current album
	 *
	 * @returns {string}
	 */
	getCurrentDirectory: function () {
		"use strict";

		// In Files, dirs start with a /
		return '/' + Gallery.currentAlbum;
	},

	/**
	 * Retrieves the WebDAV upload URL
	 *
	 * @param {string} fileName
	 * @param {string} dir
	 *
	 * @returns {string}
	 */
	getUploadUrl: function (fileName, dir) {
		if (_.isUndefined(dir)) {
			dir = this.getCurrentDirectory();
		}

		var pathSections = dir.split('/');
		if (!_.isUndefined(fileName)) {
			pathSections.push(fileName);
		}
		var encodedPath = '';
		_.each(pathSections, function (section) {
			if (section !== '') {
				encodedPath += '/' + encodeURIComponent(section);
			}
		});
		return OC.linkToRemoteBase('webdav') + encodedPath;
	}
};

/**
 * OCA.Files methods needed for file uploading
 *
 * This hack makes it possible to use the Files scripts as is, without having to import and
 * maintain them in Gallery
 *
 * @type {{isFileNameValid: Files.isFileNameValid, generatePreviewUrl: Files.generatePreviewUrl}}
 */
var Files = {
	App: {fileList: {}},

	isFileNameValid: function (name) {
		"use strict";
		var trimmedName = name.trim();
		if (trimmedName === '.' || trimmedName === '..') {
			throw t('files', '"{name}" is an invalid file name.', {name: name});
		} else if (trimmedName.length === 0) {
			throw t('files', 'File name cannot be empty.');
		} else if (OC.fileIsBlacklisted(trimmedName)) {
			throw t('files', '"{name}" is not an allowed filetype', {name: name});
		}

		return true;

	},

	/**
	 * Generates a preview for the conflict dialogue
	 *
	 * Since Gallery uses the fileId and Files uses the path, we have to use the preview endpoint
	 * of Files
	 */
	generatePreviewUrl: function (urlSpec) {
		"use strict";
		var previewUrl;
		var path = urlSpec.file;

		// In Files, root files start with //
		if (path.indexOf('//') === 0) {
			path = path.substring(2);
		} else {
			// Directories start with /
			path = path.substring(1);
		}

		if (Gallery.imageMap[path]) {
			var fileId = Gallery.imageMap[path].fileId;
			var thumbnail = Thumbnails.map[fileId];
			previewUrl = thumbnail.image.src;
		} else {
			var previewDimension = 96;
			urlSpec.x = Math.ceil(previewDimension * window.devicePixelRatio);
			urlSpec.y = Math.ceil(previewDimension * window.devicePixelRatio);
			urlSpec.forceIcon = 0;
			previewUrl = OC.generateUrl('/core/preview.png?') + $.param(urlSpec);
		}

		return previewUrl;
	}
};

OCA.Files = Object.assign({}, OCA.Files, Files);
OCA.Files.App.fileList = Object.assign({}, OCA.Files.App.fileList, FileList);
