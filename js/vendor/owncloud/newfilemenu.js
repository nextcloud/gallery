/*
 * Copyright (c) 2014-2016
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING file.
 *
 */

/* global Handlebars, Gallery */
(function ($, Gallery) {
	"use strict";
	var TEMPLATE_MENU =
		'<ul>' +
		'<li>' +
		'<label for="file_upload_start" class="menuitem" data-action="upload" title="{{uploadMaxHumanFilesize}}"><span class="svg icon icon-upload"></span><span class="displayname">{{uploadLabel}}</span></label>' +
		'</li>' +
		'</ul>';

	/**
	 * Construct a new NewFileMenu instance
	 * @constructs NewFileMenu
	 *
	 * @memberof Gallery
	 */
	var NewFileMenu = OC.Backbone.View.extend({
		tagName: 'div',
		className: 'newFileMenu popovermenu bubble hidden open menu',

		events: {
			'click .menuitem': '_onClickAction'
		},

		initialize: function () {
			var self = this;
			var $uploadEl = $('#file_upload_start');
			if ($uploadEl.length) {
				$uploadEl.on('fileuploadstart', function () {
					self.trigger('actionPerformed', 'upload');
				});
			} else {
				console.warn('Missing upload element "file_upload_start"');
			}
		},

		template: function (data) {
			if (!Gallery.NewFileMenu._TEMPLATE) {
				Gallery.NewFileMenu._TEMPLATE = Handlebars.compile(TEMPLATE_MENU);
			}
			return Gallery.NewFileMenu._TEMPLATE(data);
		},

		/**
		 * Event handler whenever the upload button has been clicked within the menu
		 */
		_onClickAction: function () {
			// note: clicking the upload label will automatically
			// set the focus on the "file_upload_start" hidden field
			// which itself triggers the upload dialog.
			// Currently the upload logic is still in file-upload.js and filelist.js
			OC.hideMenus();
		},

		/**
		 * Renders the menu with the currently set items
		 */
		render: function () {
			this.$el.html(this.template({
				uploadMaxHumanFileSize: 'TODO',
				uploadLabel: t('gallery', 'Upload')
			}));
		},

		/**
		 * Displays the menu under the given element
		 *
		 * @param {Object} $target target element
		 */
		showAt: function ($target) {
			this.render();
			var targetOffset = $target.offset();
			this.$el.css({
				left: targetOffset.left,
				top: targetOffset.top + $target.height()
			});
			this.$el.removeClass('hidden');

			OC.showMenu(null, this.$el);
		}
	});

	Gallery.NewFileMenu = NewFileMenu;
})(jQuery, Gallery);
