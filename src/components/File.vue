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
	<a class="file" :href="davPath" @click="openViewer">
		<img :srcset="previewUrls"
			:src="davPath"
			alt="Elva habillée en fée">
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
		}
	},

	methods: {
		openViewer() {
			
		}
	}

}
</script>

<style lang="scss">
.file {
	display: flex;
	justify-content: center;
	align-items: center;
	img {
		width: 100%;
	}
}
</style>
