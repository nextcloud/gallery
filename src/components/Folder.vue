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
	<router-link class="folder"
		:to="folder.filename"
		:aria-label="ariaLabel">
		<div :class="`folder-content--grid-${fileList.length}`" class="folder-content" role="none">
			<img v-for="file in fileList"
				:key="file.id"
				:src="generateImgSrc(file.id)"
				alt="">
		</div>
		<div class="folder-name">
			<span class="folder-name__icon  icon-folder-white" role="img" />
			<p :id="ariaUuid" class="folder-name__name">
				{{ folder.basename }}
			</p>
		</div>
		<div class="cover" role="none" />
	</router-link>
</template>

<script>
import { generateUrl } from '@nextcloud/router'

import getPictures from '../services/FileList'
import cancelableRequest from '../services/CancelableRequest'

export default {
	name: 'Folder',
	inheritAttrs: false,

	props: {
		folder: {
			type: Object,
			required: true
		}
	},

	data() {
		return {
			loaded: false,
			cancelRequest: () => {}
		}
	},

	computed: {
		ariaUuid() {
			return `folder-${this.folder.id}`
		},
		ariaLabel() {
			return t('gallery', 'Open the "{name}" sub-directory', { name: this.folder.basename })
		},

		// global lists
		files() {
			return this.$store.getters.files
		},
		folders() {
			return this.$store.getters.folders
		},

		// files list of the current folder
		folderContent() {
			return this.folders[this.folder.id]
		},
		fileList() {
			return this.folderContent
				? this.folderContent
					.slice(0, 4) // only get the 4 first images
					.map(id => this.files[id])
					.filter(file => !!file)
				: []
		}
	},

	async created() {
		// init cancellable request
		const { request, cancel } = cancelableRequest(getPictures)
		this.cancelRequest = cancel

		// get data
		const { files, folders } = await request(this.folder.filename)
		// this.cancelRequest('Stop!')
		this.$store.dispatch('updateFolders', { id: this.folder.id, files, folders })
		this.$store.dispatch('updateFiles', { folder: this.folder, files, folders })
	},

	beforeDestroy() {
		this.cancelRequest()
	},

	methods: {
		generateImgSrc(id) {
			return generateUrl(`/core/preview?fileId=${id}&x=${256}&y=${256}&a=true`) + ` ${256}w`
		},

		fetch() {
		}
	}

}
</script>

<style lang="scss" scoped>
@import '../mixins/FileFolder.scss';

.folder-content {
	position: absolute;
	display: grid;
	width: 100%;
	height: 100%;
	// folder layout if less than 4 pictures
	&--grid-1 {
		grid-template-columns: 1fr;
		grid-template-rows: 1fr;
	}
	&--grid-2 {
		grid-template-columns: 1fr;
		grid-template-rows: 1fr 1fr;
	}
	&--grid-3 {
		grid-template-columns: 1fr 1fr;
		grid-template-rows: 1fr 1fr;
		img:first-child {
			grid-column: span 2;
		}
	}
	&--grid-4 {
		grid-template-columns: 1fr 1fr;
		grid-template-rows: 1fr 1fr;
	}
	img {
		width: 100%;
		height: 100%;

		object-fit: cover;
	}
}

.folder-name {
	position: absolute;
	z-index: 3;
	display: flex;
	overflow: hidden;
	flex-direction: column;
	width: 100%;
	height: 100%;
	transition: opacity var(--animation-quick) ease-in-out;
	opacity: 0;
	&__icon {
		height: 40%;
		margin-top: 30%;
		background-size: 40%;
	}
	&__name {
		text-align: center;
		color: var(--color-main-background);
		text-shadow: 0 0 8px var(--color-main-text);
		font-size: 18px;
	}
}

.folder {
	&.active,
	&:active,
	&:hover,
	&:focus {
		.folder-name {
			opacity: 1;
		}
	}
}

</style>
