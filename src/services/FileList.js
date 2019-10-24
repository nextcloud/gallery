/**
 * @copyright Copyright (c) 2019 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import client from './DavClient'

/**
 * List files from a folder and filter out unwanted mimes
 *
 * @param {String} path the path relative to the user root
 * @returns {Array} the file list
 */
export default async function(path) {
	// getDirectoryContents doesn't accept / for root
	const fixedPath = path === '/' ? '' : path

	// fetch listing
	const response = await client.getDirectoryContents(fixedPath, {
		data: `<?xml version="1.0"?>
			<d:propfind xmlns:d="DAV:"
				xmlns:oc="http://owncloud.org/ns"
				xmlns:nc="http://nextcloud.org/ns"
				xmlns:ocs="http://open-collaboration-services.org/ns">
				<d:prop>
					<d:getlastmodified />
					<d:getetag />
					<d:getcontenttype />
					<oc:fileid />
					<d:getcontentlength />
					<nc:has-preview />
					<oc:favorite />
					<d:resourcetype />
				</d:prop>
			</d:propfind>`,
		details: true
	})

	const list = response.data
		.map(entry => {
			return Object.assign({
				id: parseInt(entry.props.fileid),
				isFavorite: entry.props.favorite !== '0',
				hasPreview: entry.props['has-preview'] !== 'false'
			}, entry)
		})

	const folders = []
	const files = []
	for (let entry of list) {
		if (entry.type === 'directory') {
			folders.push(entry)
		} else if (entry.mime === 'image/jpeg') {
			files.push(entry)
		}
	}

	return { folders, files }
}
