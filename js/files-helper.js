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

/**
 * The FileSummary class encapsulates the file summary values and
 * the logic to render it in the given container
 *
 * @constructs FileSummary
 * @memberof OCA.Files
 *
 * @param $tr table row element
 * @param {OC.Backbone.Model} [options.filesConfig] files app configuration
 */
var FileSummary = function() {
	this.clear();
};

FileSummary.prototype = {
	summary: {
		totalFiles: 0,
		totalDirs: 0,
		totalHidden: 0,
		totalSize: 0,
		sumIsPending:false
	},

	/**
	 * Returns whether the given file info must be hidden
	 *
	 * @param {OC.Files.FileInfo} fileInfo file info
	 *
	 * @return {boolean} true if the file is a hidden file, false otherwise
	 */
	_isHiddenFile: function(file) {
		return file.name && file.name.charAt(0) === '.';
	},

	/**
	 * Adds file
	 * @param {OC.Files.FileInfo} file file to add
	 */
	add: function(file) {
		if (file.type === 'dir' || file.mime === 'httpd/unix-directory') {
			this.summary.totalDirs++;
		}
		else {
			this.summary.totalFiles++;
		}
		if (this._isHiddenFile(file)) {
			this.summary.totalHidden++;
		}

		var size = parseInt(file.size, 10) || 0;
		if (size >=0) {
			this.summary.totalSize += size;
		} else {
			this.summary.sumIsPending = true;
		}
	},
	/**
	 * Removes file
	 * @param {OC.Files.FileInfo} file file to remove
	 */
	remove: function(file) {
		if (file.type === 'dir' || file.mime === 'httpd/unix-directory') {
			this.summary.totalDirs--;
		}
		else {
			this.summary.totalFiles--;
		}
		if (this._isHiddenFile(file)) {
			this.summary.totalHidden--;
		}
		var size = parseInt(file.size, 10) || 0;
		if (size >=0) {
			this.summary.totalSize -= size;
		}
	},
	/**
	 * Returns the total of files and directories
	 */
	getTotal: function() {
		return this.summary.totalDirs + this.summary.totalFiles;
	},
	/**
	 * Recalculates the summary based on the given files array
	 * @param files array of files
	 */
	calculate: function(files) {
		var file;
		var summary = {
			totalDirs: 0,
			totalFiles: 0,
			totalHidden: 0,
			totalSize: 0,
			sumIsPending: false
		};

		for (var i = 0; i < files.length; i++) {
			file = files[i];
			if (file.type === 'dir' || file.mime === 'httpd/unix-directory') {
				summary.totalDirs++;
			}
			else {
				summary.totalFiles++;
			}
			if (this._isHiddenFile(file)) {
				summary.totalHidden++;
			}
			var size = parseInt(file.size, 10) || 0;
			if (size >=0) {
				summary.totalSize += size;
			} else {
				summary.sumIsPending = true;
			}
		}
		this.setSummary(summary);
	},
	/**
	 * Clears the summary
	 */
	clear: function() {
		this.calculate([]);
	},
	/**
	 * Sets the current summary values
	 * @param summary map
	 */
	setSummary: function(summary) {
		this.summary = summary;
	}
};

var FilesFiles = {
	/**
	 * Returns the download URL of the given file(s)
	 * @param {string} filename string or array of file names to download
	 * @param {string} [dir] optional directory in which the file name is, defaults to the current directory
	 * @param {bool} [isDir=false] whether the given filename is a directory and might need a special URL
	 */
	getDownloadUrl: function(filename, dir, isDir) {
		if (!_.isArray(filename) && !isDir) {
			var pathSections = dir.split('/');
			pathSections.push(filename);
			var encodedPath = '';
			_.each(pathSections, function(section) {
				if (section !== '') {
					encodedPath += '/' + encodeURIComponent(section);
				}
			});
			return OC.linkToRemoteBase('webdav') + encodedPath;
		}

		if (_.isArray(filename)) {
			filename = JSON.stringify(filename);
		}

		var params = {
			dir: dir,
			files: filename
		};
		return this.getAjaxUrl('download', params);
	},

	/**
	 * Returns the ajax URL for a given action
	 * @param action action string
	 * @param params optional params map
	 */
	getAjaxUrl: function(action, params) {
		var q = '';
		if (params) {
			q = '?' + OC.buildQueryString(params);
		}
		return OC.filePath('files', 'ajax', action + '.php') + q;
	},

	/**
	 * Handles the download and calls the callback function once the download has started
	 * - browser sends download request and adds parameter with a token
	 * - server notices this token and adds a set cookie to the download response
	 * - browser now adds this cookie for the domain
	 * - JS periodically checks for this cookie and then knows when the download has started to call the callback
	 *
	 * @param {string} url download URL
	 * @param {function} callback function to call once the download has started
	 */
	handleDownload: function(url, callback) {
		var randomToken = Math.random().toString(36).substring(2),
			checkForDownloadCookie = function() {
				if (!OC.Util.isCookieSetToValue('ocDownloadStarted', randomToken)){
					return false;
				} else {
					callback();
					return true;
				}
			};

		if (url.indexOf('?') >= 0) {
			url += '&';
		} else {
			url += '?';
		}
		OC.redirect(url + 'downloadStartSecret=' + randomToken);
		OC.Util.waitFor(checkForDownloadCookie, 500);
	}
};

OCA.Files = Files;
OCA.Files.App.fileList = FileList;
OCA.Files.FileSummary = FileSummary;
OCA.Files.Files = FilesFiles;

