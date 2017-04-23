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
var galleryMenuHideAlbum = {
	attach: function (menu) {
		menu.addMenuEntry({
			'id': 'hideAlbum',
			'displayName': t('gallery', 'Hide Album'),
			'iconClass': 'icon-close',
			'actionHandler': function () {
				FileList.createFile('.nomedia')
					.then(function() {
						window.location.reload();
					})
					.fail(function() {
						OC.Notification.showTemporary(t('gallery', 'Could not hide album'));
					});
			}
		});
	}
};
OC.Plugins.register('Gallery.NewFileMenu', galleryMenuHideAlbum);
