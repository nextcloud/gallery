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
	<div class="gallery-navigation" role="toolbar">
		<Actions>
			<ActionButton v-if="!isRoot"
				icon="icon-confirm"
				@click="folderUp">
				{{ t('gallery', 'Back') }}
			</ActionButton>
		</Actions>
	</div>
</template>

<script>
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
export default {
	name: 'Navigation',

	components: {
		ActionButton,
		Actions
	},

	props: {
		path: {
			type: String,
			required: true
		}
	},

	computed: {
		isRoot() {
			return this.path === '/'
		},
		parentPath() {
			const path = this.path.split('/')
			path.pop()
			return path === '/'
				? path
				: path.join('/')
		}
	},

	methods: {
		folderUp() {
			this.$router.push(this.parentPath)
		}
	}
}
</script>

<style lang="scss" scoped>
.icon-confirm {
	transform: rotate(180deg)
}
</style>
