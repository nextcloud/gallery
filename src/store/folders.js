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

const state = {
	folders: {
		'/Photos': [1546]
	}
}

const mutations = {
	updateFolders(state, { path, files, folders }) {
		// copy old list
		const oldList = state.folders[path].slice(0)

		if (!oldList) {
			state.folders[path] = []
		}

		if (files.length > 0) {
			state.folders[path].push(...files.map(file => file.id))
		}

		if (oldList) {
			const removedFiles = oldList.filter(id => !files.includes(id))
			console.info('removedFiles', removedFiles)
		}
	}
}

const getters = {
	folders: state => state.folders
}

const actions = {
	/**
	 * Update files and folders
	 *
	 * @param {Object} context vuex context
	 * @param {Object} data destructuring object
	 * @param {string} data.path current path
	 * @param {Array} data.files list of files
	 * @param {Array} data.folders list of folders
	 */
	updateFolders(context, { path, files, folders }) {
		context.commit('updateFolders', { path, files, folders })

	}
}

export default { state, mutations, getters, actions }
