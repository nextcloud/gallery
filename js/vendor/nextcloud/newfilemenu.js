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

	/**
	 * Construct a new NewFileMenu instance
	 * @constructs NewFileMenu
	 *
	 * @memberof Gallery
	 */
	var NewFileMenu = OC.Backbone.View.extend({
		tagName: 'div',
		// Menu is opened by default because it's rendered on "add-button" click
		className: 'newFileMenu popovermenu bubble menu open menu-left',

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
			this._menuItems = [];
			OC.Plugins.attach('Gallery.NewFileMenu', this);
		},

		template: function (data) {
			return Gallery.Templates.newfilemenu(data);
		},

		/**
		 * Event handler whenever the upload button has been clicked within the menu
		 */
		_onClickAction: function (event) {
			var $target = $(event.target);
			if (!$target.hasClass('menuitem')) {
				$target = $target.closest('.menuitem');
			}
			var action = $target.attr('data-action');
			// note: clicking the upload label will automatically
			// set the focus on the "file_upload_start" hidden field
			// which itself triggers the upload dialog.
			// Currently the upload logic is still in file-upload.js and filelist.js
			if (action === 'upload') {
				OC.hideMenus(null);
			} else {
				event.preventDefault();
				this.$el.find('.menuitem.active').removeClass('active');
				$target.addClass('active');
				var actionItem;
				for (var i = 0, len = this._menuItems.length; i < len; i++) {
					if (this._menuItems[i].id === action) {
						actionItem = this._menuItems[i];
						break; // Return as soon as the object is found
					}
				}
				if (actionItem !== null) {
					actionItem.actionHandler();
				}
				OC.hideMenus(null);
			}
		},


		/**
		 * Add a new item menu entry in the “New” file menu (in
		 * last position). By clicking on the item, the
		 * `actionHandler` function is called.
		 *
		 * @param {Object} actionSpec item’s properties
		 */
		addMenuEntry: function (actionSpec) {
			this._menuItems.push({
				'id': actionSpec.id,
				'displayName': actionSpec.displayName,
				'iconClass': actionSpec.iconClass,
				'actionHandler': actionSpec.actionHandler,
			});
		},

		/**
		 * Renders the menu with the currently set items
		 */
		render: function () {
			this.$el.html(this.template({
				uploadMaxHumanFileSize: 'TODO',
				uploadLabel: t('gallery', 'Upload'),
				items: this._menuItems
			}));
		},

		/**
		 * Displays the menu under the given element
		 *
		 * @param {Object} $target target element
		 */
		showAt: function ($target) {
			this.render();
			OC.showMenu(null, this.$el);
		}
	});

	Gallery.NewFileMenu = NewFileMenu;
})(jQuery, Gallery);
