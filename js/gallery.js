/* global Album, GalleryImage */
(function ($, OC, t) {
	"use strict";
	var Gallery = {
		currentAlbum: null,
		config: {},
		/** Map of the whole gallery, built as we navigate through folders */
		albumMap: {},
		/** Used to pick an image based on the URL */
		imageMap: {},
		appName: 'gallery',
		token: undefined,
		activeSlideShow: null,
		buttonsWidth: 350,
		browserToolbarHeight: 150,

		/**
		 * Refreshes the view and starts the slideshow if required
		 *
		 * @param {string} path
		 * @param {string} albumPath
		 */
		refresh: function (path, albumPath) {
			if (Gallery.currentAlbum !== albumPath) {
				Gallery.view.init(albumPath);
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
			return $.getJSON(url).then(function (/**{{albuminfo:Object, files:Array}}*/ data) {
				/**@type {{
				 * 	fileid: number,
				 * 	permissions: number,
				 * 	path: string,
				 * 	etag: string
				 * 	information,
				 * 	sorting,
				 * 	error: string
				 * }}*/
				var albumInfo = data.albuminfo;
				Gallery.config.setAlbumConfig(albumInfo);
				// Both the folder and the etag have to match
				if ((decodeURIComponent(currentLocation) === albumInfo.path)
					&& (albumInfo.etag === albumEtag)) {
					Gallery.imageMap = albumCache.imageMap;
				} else {
					Gallery._mapFiles(data);
				}

				// Restore the previous sorting order for this album
				if (!$.isEmptyObject(Gallery.albumMap[albumInfo.path].sorting)) {
					Gallery.config.updateAlbumSorting(Gallery.albumMap[albumInfo.path].sorting);
				}

			}, function () {
				// Triggered if we couldn't find a working folder
				Gallery.view.element.empty();
				Gallery.showEmpty();
				Gallery.currentAlbum = null;
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
			Gallery.albumMap[Gallery.currentAlbum].subAlbums.sort(Gallery.utility.sortBy(albumSortType,
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
			button.children('#button-loading').addClass('loading');
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

				var albumPermissions = Gallery.config.albumPermissions;
				$('a.share').data('path', albumPermissions.path).data('link', true)
					.data('possible-permissions', albumPermissions.permissions).
					click();
				if (!$('#linkCheckbox').is(':checked')) {
					$('#linkText').hide();
				}
			}
		},

		/**
		 * Sends an archive of the current folder to the browser
		 *
		 * @param event
		 */
		download: function (event) {
			event.preventDefault();

			var path = $('#content').data('albumname');
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
		 * Lets the user add the shared files to his ownCloud
		 */
		showSaveForm: function () {
			$(this).hide();
			$('.save-form').css('display', 'inline');
			$('#remote_address').focus();
		},

		/**
		 * Sends the shared files to the viewer's ownCloud
		 *
		 * @param event
		 */
		saveForm: function (event) {
			event.preventDefault();

			var saveElement = $('#save');
			var remote = $(this).find('input[type="text"]').val();
			var owner = saveElement.data('owner');
			var name = saveElement.data('name');
			var isProtected = saveElement.data('protected');
			Gallery._saveToOwnCloud(remote, Gallery.token, owner, name, isProtected);
		},

		/**
		 * Hide the search button while we wait for core to fix the templates
		 */
		hideSearch: function () {
			$('form.searchbox').hide();
		},

		/**
		 * Shows an empty gallery message
		 */
		showEmpty: function () {
			var emptyContentElement = $('#emptycontent');
			var message = '<div class="icon-gallery"></div>';
			message += '<h2>' + t('gallery',
				'No pictures found') + '</h2>';
			message += '<p>' + t('gallery',
				'Upload pictures in the files app to display them here') + '</p>';
			emptyContentElement.html(message);
			emptyContentElement.removeClass('hidden');
			$('#controls').addClass('hidden');
			$('#loading-indicator').hide();
		},

		/**
		 * Shows an empty gallery message
		 */
		showEmptyFolder: function () {
			var emptyContentElement = $('#emptycontent');
			var message = '<div class="icon-gallery"></div>';
			message += '<h2>' + t('gallery',
				'Nothing in here') + '</h2>';
			message += '<p>' + t('gallery',
				'No media files found in this folder') + '</p>';
			emptyContentElement.html(message);
			emptyContentElement.removeClass('hidden');
		},

		/**
		 * Shows the infamous loading spinner
		 */
		showLoading: function () {
			$('#emptycontent').addClass('hidden');
			$('#controls').removeClass('hidden');
		},

		/**
		 * Shows thumbnails
		 */
		showNormal: function () {
			$('#emptycontent').addClass('hidden');
			$('#controls').removeClass('hidden');
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
				var downloadUrl = Gallery.utility.buildGalleryUrl('files', '/download/' + image.fileId,
					params);

				return {
					name: name,
					path: image.path,
					file: image.fileId,
					mimeType: image.mimeType,
					url: previewUrl,
					downloadUrl: downloadUrl
				};
			});
			Gallery.activeSlideShow.setImages(images, autoPlay);
			Gallery.activeSlideShow.onStop = function () {
				$('#content').show();
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

			// Resets the last focused element
			document.activeElement.blur();
		},

		/**
		 * Builds the album's model
		 *
		 * @param {{albuminfo:Object, files:Array}} data
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
			var albumInfo = data.albuminfo;
			var currentLocation = albumInfo.path;
			// This adds a new node to the map for each parent album
			Gallery._mapStructure(currentLocation);
			var files = data.files;
			if (files.length > 0) {
				var subAlbumCache = {};
				var albumCache = Gallery.albumMap[currentLocation]
					= new Album(currentLocation, [], [], OC.basename(currentLocation));
				for (var i = 0; i < files.length; i++) {
					path = files[i].path;
					fileId = files[i].fileid;
					mimeType = files[i].mimetype;
					mTime = files[i].mtime;
					etag = files[i].etag;

					image = new GalleryImage(path, path, fileId, mimeType, mTime, etag);

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
							subAlbumCache[dir] = new Album(dir, [], [],
								OC.basename(dir));
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
				albumCache.etag = albumInfo.etag;
				albumCache.imageMap = Gallery.imageMap;
			}
		},

		/**
		 * Adds every album leading the current folder to a global album map
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
		 * Saves the folder to a remote ownCloud installation
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
		_saveToOwnCloud: function (remote, token, owner, name, isProtected) {
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
									'No ownCloud installation (7 or higher) found at {remote}',
									{remote: remote}),
								t('files_sharing', 'Invalid ownCloud url'));
						} else {
							OC.redirect(protocol + '://' + url);
						}
					});
			}
		}
	};
	window.Gallery = Gallery;
})(jQuery, OC, t);
