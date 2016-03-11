/* global Gallery, Thumbnails */
/**
 * OCA.FileList methods needed for file uploading
 *
 * This hack makes it possible to use the Files scripts as is, without having to import and
 * maintain them in Gallery
 *
 * Empty methods are for the "new" button, if we want to implement that one day
 *
 * @type {{inList: FileList.inList, lastAction: FileList.lastAction, getUniqueName:
 *     FileList.getUniqueName, getCurrentDirectory: FileList.getCurrentDirectory, add:
 *     FileList.add, checkName: FileList.checkName}}
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
	 * Refreshes the photowall
	 *
	 * Called at the end of the uploading process when 1 or multiple files are sent
	 * Never called with folders on Chrome, unless files are uploaded at the same time as folders
	 *
	 * @param fileList
	 */
	highlightFiles: function (fileList) {
		"use strict";
		//Ask for a refresh of the photowall
		Gallery.getFiles(Gallery.currentAlbum).done(function () {
			var fileId, path;
			// Removes the cached thumbnails of files which have been re-uploaded
			_(fileList).each(function (fileName) {
				path = Gallery.currentAlbum + '/' + fileName;
				if (Gallery.imageMap[path]) {
					fileId = Gallery.imageMap[path].fileId;
					if (Thumbnails.map[fileId]) {
						delete Thumbnails.map[fileId];
					}
				}
			});

			Gallery.view.init(Gallery.currentAlbum);
		});
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

OCA.Files = Files;
OCA.Files.App.fileList = FileList;
