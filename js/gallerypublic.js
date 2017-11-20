/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Cédric de Saint Martin <cedric@desaintmartin.fr>
 *
 * @copyright Olivier Paroz 2017
 */
/* global OCA, Gallery */
(function ($, OC, t) {
	"use strict";
	OCA.Files.Files.getDownloadUrl = function (filename, dir) {
		var path = dir || this.getCurrentDirectory();
		if (_.isArray(filename)) {
			filename = JSON.stringify(filename);
		}
		var params = {
			path: path
		};
		if (filename) {
			params.files = filename;
		}
		return OC.generateUrl('/s/' + Gallery.token + '/download') + '?' + OC.buildQueryString(params);
	};
})(jQuery, OC, t);
