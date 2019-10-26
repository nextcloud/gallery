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
	<a class="file"
		:href="davPath"
		:aria-label="ariaLabel"
		@click.prevent="openViewer">
		<img :srcset="previewUrls"
			:src="davPath"
			:alt="basename"
			:aria-describedby="ariaUuid">
		<p :id="ariaUuid" class="hidden-visually">{{ basename }}</p>
		<div class="cover" role="none" />
	</a>
</template>

<script>
import { generateRemoteUrl, generateUrl } from '@nextcloud/router'

import { getCurrentUser } from '@nextcloud/auth'

const sizes = [64, 256, 1024, 4096]

export default {
	name: 'File',
	inheritAttrs: false,

	props: {
		basename: {
			type: String,
			required: true
		},
		filename: {
			type: String,
			required: true
		},
		id: {
			type: Number,
			required: true
		}
	},

	computed: {
		previewUrls() {
			return sizes.map((size, index) => generateUrl(`/core/preview?fileId=${this.id}&x=${size}&y=${size}`) + ` ${size}w`)
		},
		davPath() {
			return generateRemoteUrl(`dav/files/${getCurrentUser().uid}`) + this.filename
		},
		ariaUuid() {
			return `image-${this.id}`
		},
		ariaLabel() {
			return t('gallery', 'Open the full size {name} image', { name: this.basename })
		}
	},

	methods: {
		openViewer() {
			OCA.Viewer.file = this.filename
		}
	}

}
</script>

<style lang="scss">
.file {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;

	img {
		width: 100%;
	}

	.cover {
		position: absolute;
		top: 0;
		left: 0;
		width: 0;
		height: 0;
		transition: opacity var(--animation-quick) ease-in-out;
		opacity: 0;
		background-color: var(--color-main-text);
	}

	&.active,
	&:active,
	&:hover,
	&:focus {
		.cover {
			display: block;
			width: 100%;
			height: 100%;
			opacity: .2;
		}
	}
}

</style>
