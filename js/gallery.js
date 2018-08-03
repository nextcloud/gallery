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
/* global Album, GalleryImage */
(function ($, OC, t) {
	"use strict";
	var Gallery = {
		currentAlbum: null,
		currentEtag: null,
		config: {},
		/** Map of the whole gallery, built as we navigate through folders */
		albumMap: {},
		/** Used to pick an image based on the URL */
		imageMap: {},
		appName: 'gallery',
		token: undefined,
		activeSlideShow: null,
		buttonsWidth: 600,
		browserToolbarHeight: 150,
		filesClient: null,

		/**
		 * Refreshes the view and starts the slideshow if required
		 *
		 * @param {string} path
		 * @param {string} albumPath
		 */
		refresh: function (path, albumPath) {
			if (Gallery.currentAlbum !== albumPath) {
				Gallery.view.init(albumPath, null);
			}

			// If the path is mapped, that means that it's an albumPath
			if (Gallery.albumMap[path]) {
				if (Gallery.activeSlideShow) {
					Gallery.activeSlideShow.stop();
				}
			} else if (Gallery.imageMap[path] && Gallery.activeSlideShow.active === false) {
				Gallery.view.startSlideshow(path, albumPath);
			}
		},

		/**
		 * Retrieves information about all the images and albums located in the current folder
		 *
		 * @param {string} currentLocation
		 *
		 * @returns {*}
		 */
		getFiles: function (currentLocation) {
			// Cache the sorting order of the current album before loading new files
			if (!$.isEmptyObject(Gallery.albumMap)) {
				Gallery.albumMap[Gallery.currentAlbum].sorting = Gallery.config.albumSorting;
			}
			// Checks if we've visited this location before ands saves the etag to use for
			// comparison later
			var albumEtag;
			var albumCache = Gallery.albumMap[decodeURIComponent(currentLocation)];
			if (!$.isEmptyObject(albumCache)) {
				albumEtag = albumCache.etag;
			}

			// Sends the request to the server
			var params = {
				location: currentLocation,
				mediatypes: Gallery.config.getMediaTypes(),
				features: Gallery.config.getFeatures(),
				etag: albumEtag
			};
			// Only use the folder as a GET parameter and not as part of the URL
			var url = Gallery.utility.buildGalleryUrl('files', '/list', params);
			return $.getJSON(url).then(
				function (/**@type{{
					* files:Array,
					* albums:Array,
					* albumconfig:Object,
					* albumpath:String,
					* updated:Boolean}}*/
						  data) {
					var albumpath = data.albumpath;
					var updated = data.updated;
					// FIXME albumConfig should be cached as well
					/**@type {{design,information,sorting,error: string}}*/
					var albumConfig = data.albumconfig;
					//Gallery.config.setAlbumPermissions(currentAlbum);
					Gallery.config.setAlbumConfig(albumConfig, albumpath);
					// Both the folder and the etag have to match
					if ((decodeURIComponent(currentLocation) === albumpath) &&
						(updated === false)) {
						Gallery.imageMap = albumCache.imageMap;
					} else {
						Gallery._mapFiles(data);
					}

					// Restore the previous sorting order for this album
					if (!$.isEmptyObject(Gallery.albumMap[albumpath].sorting)) {
						Gallery.config.updateAlbumSorting(
							Gallery.albumMap[albumpath].sorting);
					}

				}, function (xhr) {
					var result = xhr.responseJSON;
					var albumPath = decodeURIComponent(currentLocation);
					var message;
					if (result === null) {
						message = t('gallery', 'There was a problem reading files from this album');
					} else {
						message = result.message;
					}
					Gallery.view.init(albumPath, message);
					Gallery._mapStructure(albumPath);
				});
		},

		/**
		 * Sorts albums and images based on user preferences
		 */
		sorter: function () {
			var sortType = 'name';
			var sortOrder = 'asc';
			var albumSortType = 'name';
			var albumSortOrder = 'asc';
			if (this.id === 'sort-date-button') {
				sortType = 'date';

			}
			var currentSort = Gallery.config.albumSorting;
			if (currentSort.type === sortType && currentSort.order === sortOrder) {
				sortOrder = 'des';
			}

			// Update the controls
			Gallery.view.sortControlsSetup(sortType, sortOrder);

			// We can't currently sort by album creation time
			if (sortType === 'name') {
				albumSortOrder = sortOrder;
			}

			// FIXME Rendering is still happening while we're sorting...

			// Clear before sorting
			Gallery.view.clear();

			// Sort the images
			Gallery.albumMap[Gallery.currentAlbum].images.sort(Gallery.utility.sortBy(sortType,
				sortOrder));
			Gallery.albumMap[Gallery.currentAlbum].subAlbums.sort(
				Gallery.utility.sortBy(albumSortType,
					albumSortOrder));

			// Save the new settings
			var sortConfig = {
				type: sortType,
				order: sortOrder,
				albumOrder: albumSortOrder
			};
			Gallery.config.updateAlbumSorting(sortConfig);

			// Refresh the view
			Gallery.view.viewAlbum(Gallery.currentAlbum);
		},

		/**
		 * Switches to the Files view
		 *
		 * @param event
		 */
		switchToFilesView: function (event) {
			event.stopPropagation();

			var subUrl = '';
			var params = {path: '/' + Gallery.currentAlbum};
			if (Gallery.token) {
				params.token = Gallery.token;
				subUrl = 's/{token}?path={path}';
			} else {
				subUrl = 'apps/files?dir={path}';
			}

			var button = $('#filelist-button');
			button.children('img').addClass('hidden');
			button.children('#button-loading').removeClass('hidden').addClass('icon-loading-small');
			OC.redirect(OC.generateUrl(subUrl, params));
		},

		/**
		 * Populates the share dialog with the needed information
		 *
		 * @param event
		 */
		share: function (event) {
			// Clicking on share button does not trigger automatic slide-up
			$('.album-info-container').slideUp();

			if (!Gallery.Share.droppedDown) {
				event.preventDefault();
				event.stopPropagation();

				var currentAlbum = Gallery.albumMap[Gallery.currentAlbum];
				$('#controls a.share').data('path', currentAlbum.path)
					.data('link', true)
					.data('item-source', currentAlbum.fileId)
					.data('possible-permissions', currentAlbum.permissions)
					.click();
			}
		},

		/**
		 * Sends an archive of the current folder to the browser
		 *
		 * @param event
		 */
		download: function (event) {
			event.preventDefault();

			var path = $('#app-content').data('albumname');
			var files = Gallery.currentAlbum;
			var downloadUrl = Gallery.utility.buildFilesUrl(path, files);

			OC.redirect(downloadUrl);
		},

		/**
		 * Shows an information box to the user
		 *
		 * @param event
		 */
		showInfo: function (event) {
			event.stopPropagation();
			Gallery.infoBox.showInfo();
		},

		/**
		 * Sends the shared files to the viewer's cloud
		 *
		 * @param event
		 */
		saveForm: function (event) {
			event.preventDefault();

			var saveElement = $('#save-external-share');
			var remote = $(this).find('input[type="text"]').val();
			var owner = saveElement.data('owner');
			var name = saveElement.data('name');
			var isProtected = saveElement.data('protected');
			Gallery._saveToServer(remote, Gallery.token, owner, name, isProtected);
		},

		/**
		 * Creates a new slideshow using the images found in the current folder
		 *
		 * @param {Array} images
		 * @param {string} startImage
		 * @param {boolean} autoPlay
		 *
		 * @returns {boolean}
		 */
		slideShow: function (images, startImage, autoPlay) {
			if (startImage === undefined) {
				OC.Notification.showTemporary(t('gallery',
					'Aborting preview. Could not find the file'));
				return false;
			}
			var start = images.indexOf(startImage);
			images = images.filter(function (image, index) {
				// If the slideshow is loaded before we get a thumbnail, we have to accept all
				// images
				if (!image.thumbnail) {
					return image;
				} else {
					if (image.thumbnail.valid) {
						return image;
					} else if (index < images.indexOf(startImage)) {
						start--;
					}
				}
			}).map(function (image) {
				var name = OC.basename(image.path);
				var previewUrl = Gallery.utility.getPreviewUrl(image.fileId, image.etag);
				var params = {
					c: image.etag,
					requesttoken: oc_requesttoken
				};
				var downloadUrl = Gallery.utility.buildGalleryUrl('files',
					'/download/' + image.fileId,
					params);

				return {
					name: name,
					path: image.path,
					file: image.fileId,
					mimeType: image.mimeType,
					permissions: image.permissions,
					url: previewUrl,
					downloadUrl: downloadUrl
				};
			});
			Gallery.activeSlideShow.setImages(images, autoPlay);
			Gallery.activeSlideShow.onStop = function () {
				$('#content').show();
				Gallery.view.removeLoading();
				if (Gallery.currentAlbum !== '') {
					// Only modern browsers can manipulate history
					if (history && history.replaceState) {
						history.replaceState('', '',
							'#' + encodeURIComponent(Gallery.currentAlbum));
					} else {
						location.hash = '#' + encodeURIComponent(Gallery.currentAlbum);
					}
				} else {
					// Only modern browsers can manipulate history
					if (history && history.replaceState) {
						history.replaceState('', '', '#');
					} else {
						location.hash = '#';
					}
				}
			};
			Gallery.activeSlideShow.show(start);
			if(!_.isUndefined(Gallery.Share)){
				Gallery.Share.hideDropDown();
			}
			$('.album-info-container').slideUp();
			// Resets the last focused element
			document.activeElement.blur();
		},

		/**
		 * Moves files and albums to a new location
		 *
		 * @param {jQuery} $item
		 * @param {string} fileName
		 * @param {string} filePath
		 * @param {jQuery} $target
		 * @param {string} targetPath
		 */
		move: function ($item, fileName, filePath, $target, targetPath) {
			var self = this;
			var dir = Gallery.currentAlbum;

			if (targetPath.charAt(targetPath.length - 1) !== '/') {
				// make sure we move the files into the target dir,
				// not overwrite it
				targetPath = targetPath + '/';
			}
			self.filesClient.move(dir + '/' + fileName, targetPath + fileName)
				.done(function () {
					self._removeElement(dir, filePath, $item);
				})
				.fail(function (status) {
					if (status === 412) {
						// TODO: some day here we should invoke the conflict dialog
						OC.Notification.showTemporary(
							t('gallery', 'Could not move "{file}", target exists', {file: fileName})
						);
					} else {
						OC.Notification.showTemporary(
							t('gallery', 'Could not move "{file}"', {file: fileName})
						);
					}
					$item.fadeTo("normal", 1);
					$target.children('.album-loader').hide();
				})
				.always(function () {
					// Nothing?
				});
		},

		/**
		 * Builds the album's model
		 *
		 * @param {{
		 * 	files:Array,
		 * 	albums:Array,
		 * 	albumconfig:Object,
		 * 	albumpath:String,
		 *	updated:Boolean
		 * 	}} data
		 * @private
		 */
		_mapFiles: function (data) {
			Gallery.imageMap = {};
			var image = null;
			var path = null;
			var fileId = null;
			var mimeType = null;
			var mTime = null;
			var etag = null;
			var size = null;
			var sharedWithUser = null;
			var owner = null;
			var permissions = 0;
			var currentLocation = data.albumpath;
			// This adds a new node to the map for each parent album
			Gallery._mapStructure(currentLocation);
			var files = data.files;
			if (files.length > 0) {
				var subAlbumCache = {};
				var albumCache = Gallery.albumMap[currentLocation]
					= new Album(
					currentLocation,
					[],
					[],
					OC.basename(currentLocation),
					data.albums[currentLocation].nodeid,
					data.albums[currentLocation].mtime,
					data.albums[currentLocation].etag,
					data.albums[currentLocation].size,
					data.albums[currentLocation].sharedwithuser,
					data.albums[currentLocation].owner,
					data.albums[currentLocation].freespace,
					data.albums[currentLocation].permissions
				);
				for (var i = 0; i < files.length; i++) {
					path = files[i].path;
					fileId = files[i].nodeid;
					mimeType = files[i].mimetype;
					mTime = files[i].mtime;
					etag = files[i].etag;
					size = files[i].size;
					sharedWithUser = files[i].sharedwithuser;
					owner = files[i].owner;
					permissions = files[i].permissions;

					image =
						new GalleryImage(
							path, path, fileId, mimeType, mTime, etag, size, sharedWithUser, owner, permissions
						);

					// Determines the folder name for the image
					var dir = OC.dirname(path);
					if (dir === path) {
						dir = '';
					}
					if (dir === currentLocation) {
						// The image belongs to the current album, so we can add it directly
						albumCache.images.push(image);
					} else {
						// The image belongs to a sub-album, so we create a sub-album cache if it
						// doesn't exist and add images to it
						if (!subAlbumCache[dir]) {
							subAlbumCache[dir] = new Album(
								dir,
								[],
								[],
								OC.basename(dir),
								data.albums[dir].nodeid,
								data.albums[dir].mtime,
								data.albums[dir].etag,
								data.albums[dir].size,
								data.albums[dir].sharedwithuser,
								data.albums[currentLocation].owner,
								data.albums[currentLocation].freespace,
								data.albums[dir].permissions);
						}
						subAlbumCache[dir].images.push(image);
						// The sub-album also has to be added to the global map
						if (!Gallery.albumMap[dir]) {
							Gallery.albumMap[dir] = {};
						}
					}
					Gallery.imageMap[image.path] = image;
				}
				// Adds the sub-albums to the current album
				Gallery._mapAlbums(albumCache, subAlbumCache);

				// Caches the information which is not already cached
				albumCache.etag = data.albums[currentLocation].etag;
				albumCache.imageMap = Gallery.imageMap;
			}
		},

		/**
		 * Adds every album leading to the current folder to a global album map
		 *
		 * Per example, if you have Root/Folder1/Folder2/CurrentFolder then the map will contain:
		 *    * Root
		 *    * Folder1
		 *    * Folder2
		 *    * CurrentFolder
		 *
		 *  Every time a new location is loaded, the map is completed
		 *
		 *
		 * @param {string} path
		 *
		 * @returns {Album}
		 * @private
		 */
		_mapStructure: function (path) {
			if (!Gallery.albumMap[path]) {
				Gallery.albumMap[path] = {};
				// Builds relationships between albums
				if (path !== '') {
					var parent = OC.dirname(path);
					if (parent === path) {
						parent = '';
					}
					Gallery._mapStructure(parent);
				}
			}
			return Gallery.albumMap[path];
		},

		/**
		 * Adds the sub-albums to the current album
		 *
		 * @param {Album} albumCache
		 * @param {{Album}} subAlbumCache
		 * @private
		 */
		_mapAlbums: function (albumCache, subAlbumCache) {
			for (var j = 0, keys = Object.keys(subAlbumCache); j <
			keys.length; j++) {
				albumCache.subAlbums.push(subAlbumCache[keys[j]]);
			}
		},

		/**
		 * Saves the folder to a remote server
		 *
		 * Our location is the remote for the other server
		 *
		 * @param {string} remote
		 * @param {string}token
		 * @param {string}owner
		 * @param {string}name
		 * @param {boolean} isProtected
		 * @private
		 */
		_saveToServer: function (remote, token, owner, name, isProtected) {
			var location = window.location.protocol + '//' + window.location.host + OC.webroot;
			var isProtectedInt = (isProtected) ? 1 : 0;
			var url = remote + '/index.php/apps/files#' + 'remote=' + encodeURIComponent(location)
				+ "&token=" + encodeURIComponent(token) + "&owner=" + encodeURIComponent(owner) +
				"&name=" +
				encodeURIComponent(name) + "&protected=" + isProtectedInt;

			if (remote.indexOf('://') > 0) {
				OC.redirect(url);
			} else {
				// if no protocol is specified, we automatically detect it by testing https and
				// http
				// this check needs to happen on the server due to the Content Security Policy
				// directive
				$.get(OC.generateUrl('apps/files_sharing/testremote'),
					{remote: remote}).then(function (protocol) {
					if (protocol !== 'http' && protocol !== 'https') {
						OC.dialogs.alert(t('files_sharing',
							'No compatible server found at {remote}',
							{remote: remote}),
							t('files_sharing', 'Invalid server url'));
					} else {
						OC.redirect(protocol + '://' + url);
					}
				});
			}
		},

		/**
		 * Removes the moved element from the UI and refreshes the view
		 *
		 * @param {string} dir
		 * @param {string}filePath
		 * @param {jQuery} $item
		 * @private
		 */
		_removeElement: function (dir, filePath, $item) {
			var images = Gallery.albumMap[Gallery.currentAlbum].images;
			var albums = Gallery.albumMap[Gallery.currentAlbum].subAlbums;
			// if still viewing the same directory
			if (Gallery.currentAlbum === dir) {
				var removed = false;
				// We try to see if an image was removed
				var movedImage = _(images).findIndex({path: filePath});
				if (movedImage >= 0) {
					images.splice(movedImage, 1);
					removed = true;
				} else {
					// It wasn't an image, so try to remove an album
					var movedAlbum = _(albums).findIndex({path: filePath});
					if (movedAlbum >= 0) {
						albums.splice(movedAlbum, 1);
						removed = true;
					}
				}

				if (removed) {
					$item.remove();
					// Refresh the photowall without checking if new files have arrived in the
					// current album
					// TODO On the next visit this album is going to be reloaded, unless we can get
					// an etag back from the move endpoint
					Gallery.view.init(Gallery.currentAlbum);
				}
			}
		}
	};
	window.Gallery = Gallery;
})(jQuery, OC, t);
