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
		<transition name="fade">
			<div v-show="loaded"
				:class="`folder-content--grid-${fileList.length}`"
				class="folder-content"
				role="none">
				<img v-for="file in fileList"
					:key="file.id"
					:src="generateImgSrc(file)"
					alt=""
					@load="loaded = true">
			</div>
		</transition>
		<div :class="{'folder-name--empty': isEmpty}"
			class="folder-name">
			<span :class="{'icon-white': !isEmpty}"
				class="folder-name__icon  icon-folder"
				role="img" />
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
import cancelableRequest from '../utils/CancelableRequest'

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
		},

		// folder is empty
		isEmpty() {
			return this.fileList.length === 0
		}
	},

	async created() {
		// init cancellable request
		const { request, cancel } = cancelableRequest(getPictures)
		this.cancelRequest = cancel

		try {
			// get data
			const { files, folders } = await request(this.folder.filename)
			// this.cancelRequest('Stop!')
			this.$store.dispatch('updateFolders', { id: this.folder.id, files, folders })
			this.$store.dispatch('updateFiles', { folder: this.folder, files, folders })
		} catch (error) {
			if (error.response && error.response.status) {
				console.error('Failed to get folder content', this.folder, error.response)
			}
			// else we just cancelled the request
		}
	},

	beforeDestroy() {
		this.cancelRequest()
	},

	methods: {
		generateImgSrc({ id, etag }) {
			// use etag to force cache reload if file changed
			return generateUrl(`/core/preview?fileId=${id}&x=${256}&y=${256}&a=true&v=${etag}`)
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
	opacity: 1;
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

	// if no img, let's display the folder icon as default black
	&--empty {
		.folder-name__name {
			color: var(--color-main-text);
			text-shadow: 0 0 8px var(--color-main-background);
		}
		+ .cover {
			// less invasive, let's lower the bg a bit
			background-color: var(--color-text-lighter);
		}
	}
}

// Cover management if not empty
.folder {
	.folder-name:not(.folder-name--empty) {
		+ .cover {
			opacity: .3;
		}
	}

	&.active,
	&:active,
	&:hover,
	&:focus {
		.folder-name:not(.folder-name--empty) {
			&, & + .cover {
				opacity: 0;
			}
		}
	}
}

</style>
