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
		{{ t('gallery', 'This folder is empty') }}
	</EmptyContent>

	<!-- Folder content -->
	<div v-else id="gallery-grid">
		<File v-for="file in files" :key="file.id" v-bind="file" />
	</div>
</template>

<script>
import getPictures from '../services/FileList'

import EmptyContent from './EmptyContent'
import File from '../components/File'

export default {
	name: 'Grid',
	components: {
		EmptyContent,
		File
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
		folders() {
			return this.$store.getters.folders
		},
		files() {
			return this.$store.getters.files
		},
		fileList() {
			return this.folders[this.path]
		},
		isEmpty() {
			return !this.fileList || this.fileList.length === 0
		}
	},

	watch: {
		path() {
			this.fetchFolderContent()
		}
	},

	beforeMount() {
		this.fetchFolderContent(this.path)
	},

	methods: {
		async fetchFolderContent() {
			this.$emit('update:loading', true)
			this.error = null

			try {
				const { files, folders } = await getPictures(this.path)
				this.$store.dispatch('updateFolders', { path: this.path, files, folders })
				this.$store.dispatch('updateFiles', files)
			} catch (error) {
				if (error.response && error.response.status === 404) {
					this.error = 404
				} else {
					this.error = error
				}
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
	grid-template-columns: repeat(8, 1fr);
	grid-auto-rows: minmax(100px, auto);
	justify-content: center;
	align-items: center;
	gap: 8px;
	margin: 8px;
}
</style>
