<!--
 - @copyright Copyright (c) 2019 John Molakvoæ <skjnldsv@protonmail.com>
 -
 - @author John Molakvoæ <skjnldsv@protonmail.com>
 -
 - @license GNU AGPL version 3 or any later version
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU Affero General Public License as
 - published by the Free Software Foundation, either version 3 of the
 - License, or (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -
 -->

<template>
	<!-- Errors handlers-->
	<EmptyContent v-if="error === 404" illustration-name="folder">
		{{ t('gallery', 'This folder does not exists') }}
	</EmptyContent>
	<EmptyContent v-else-if="error">
		{{ t('gallery', 'An error occurred') }}
	</EmptyContent>
	<EmptyContent v-else-if="!loading && isEmpty" illustration-name="empty">
		{{ t('gallery', 'This folder does not contain pictures') }}
	</EmptyContent>

	<!-- Folder content -->
	<transition-group v-else
		id="gallery-grid"
		role="grid"
		name="list"
		tag="div">
		<Navigation key="navigation" :path="path" />
		<Folder v-for="folder in folderList" :key="folder.id" :folder="folder" />
		<File v-for="file in fileList" :key="file.id" v-bind="file" />
	</transition-group>
</template>

<script>
import getFolder from '../services/FolderInfo'
import getPictures from '../services/FileList'
// import searchPhotos from '../services/PhotoSearch'

import EmptyContent from './EmptyContent'
import Folder from '../components/Folder'
import File from '../components/File'
import Navigation from '../components/Navigation'

export default {
	name: 'Grid',
	components: {
		EmptyContent,
		File,
		Folder,
		Navigation
	},
	props: {
		path: {
			type: String,
			default: ''
		},
		loading: {
			type: Boolean,
			required: true
		}
	},

	data() {
		return {
			error: null
		}
	},

	computed: {
		// global lists
		files() {
			return this.$store.getters.files
		},
		folders() {
			return this.$store.getters.folders
		},

		// current folder id from current path
		folderId() {
			return this.$store.getters.folderId(this.path)
		},

		// files list of the current folder
		folderContent() {
			return this.folders[this.folderId]
		},
		fileList() {
			return this.folderContent
				&& this.folderContent
					.map(id => this.files[id])
					.filter(file => !!file)
		},

		// subfolders of the current folder
		subFolders() {
			return this.folderId
				&& this.files[this.folderId]
				&& this.files[this.folderId].folders
		},
		folderList() {
			return this.subFolders
				&& this.subFolders
					.map(id => this.files[id])
					.filter(file => !!file)
		},

		// is current folder empty?
		isEmpty() {
			return !this.haveFiles && !this.haveFolders
		},
		haveFiles() {
			return !!this.fileList && this.fileList.length !== 0
		},
		haveFolders() {
			return !!this.folderList && this.folderList.length !== 0
		}
	},

	watch: {
		path() {
			this.fetchFolderContent()
		}
	},

	beforeMount() {
		this.fetchFolderContent()
	},

	methods: {
		async fetchFolderContent() {
			// if we don't already have some cached data let's show a loader
			if (!this.files[this.folderId]) {
				this.$emit('update:loading', true)
			}
			this.error = null

			try {
				// get current folder
				const folder = await getFolder(this.path)
				this.$store.dispatch('addPath', { path: this.path, id: folder.id })

				// get content
				const { files, folders } = await getPictures(this.path)
				this.$store.dispatch('updateFolders', { id: folder.id, files, folders })
				this.$store.dispatch('updateFiles', { folder, files, folders })
			} catch (error) {
				if (error.response && error.response.status === 404) {
					this.error = 404
					setTimeout(() => {
						this.$router.push({ name: 'root' })
					}, 3000)
				} else {
					this.error = error
				}
				console.error(error)
			} finally {
				// done loading even with errors
				this.$emit('update:loading', false)
			}
		}
	}

}
</script>

<style lang="scss">
#gallery-grid {
	display: grid;
	align-items: center;
	justify-content: center;
	gap: 8px;
	grid-template-columns: repeat(10, 1fr);
}

.list-move {
	transition: transform var(--animation-quick);
}

$previous: 0px;
@each $size, $config in get('sizes') {
	$count: map-get($config, 'count');
	$marginTop: map-get($config, 'marginTop');
	$marginW: map-get($config, 'marginW');
	@media (min-width: $previous) and (max-width: $size) {
		#gallery-grid {
			margin: $marginTop $marginW $marginW $marginW;
			grid-template-columns: repeat($count, 1fr);
		}
	}
	$previous: $size;
}
</style>
