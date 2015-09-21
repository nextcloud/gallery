/* global Gallery, commonmark, DOMPurify */
(function ($, t, Gallery) {
	"use strict";
	/**
	 * Shows some information about the current album
	 *
	 * @constructor
	 */
	var InfoBox = function () {
		this.infoContentContainer = $('.album-info-container');
		this.infoContentSpinner = this.infoContentContainer.children('.album-info-loader');
		this.infoContentElement = this.infoContentContainer.children('.album-info-content');
		this.markdownReader = new commonmark.Parser();
		this.htmlWriter = new commonmark.HtmlRenderer();
		this._initCustomSanitizer();
	};

	InfoBox.prototype = {
		infoContentContainer: null,
		infoContentSpinner: null,
		infoContentElement: null,
		albumInfo: null,
		markdownReader: null,
		htmlWriter: null,
		allowedTags: null,

		/**
		 * Shows an information box to the user
		 */
		showInfo: function () {
			if (this.infoContentContainer.is(':visible')) {
				this.infoContentContainer.slideUp();
			} else {
				this.albumInfo = Gallery.config.albumInfo;

				if (!this.albumInfo.infoLoaded) {
					this.infoContentSpinner.addClass('icon-loading');
					this.infoContentElement.empty();
					this.infoContentElement.height(100);
					this.infoContentContainer.slideDown();
					if (!$.isEmptyObject(this.albumInfo.descriptionLink)) {
						var path = '/' + this.albumInfo.filePath;
						var file = this.albumInfo.descriptionLink;
						var descriptionUrl = Gallery.utility.buildFilesUrl(path, file);
						var thisInfoBox = this;
						$.get(descriptionUrl).done(function (data) {
								thisInfoBox._addContent(data);
							}
						).fail(function () {
							thisInfoBox._addContent(t('gallery',
								'Could not load the description'));
						});
					} else {
						this._addContent(this.albumInfo.description);
					}
					Gallery.config.infoLoaded = true;
				} else {
					this.infoContentContainer.slideDown();
				}
				this.infoContentContainer.scrollTop(0);
			}
		},

		/**
		 * Adds our album information to the infoBox
		 *
		 * @param {string} content
		 * @private
		 */
		_addContent: function (content) {
			try {
				content = this._parseMarkdown(content);
			} catch (exception) {
				content = t('gallery',
					'Could not load the description: ' + exception.message);
			}
			this.infoContentElement.append(content);
			this.infoContentElement.find('a').attr("target", "_blank");
			this._showCopyright();
			this._adjustHeight();
		},

		/**
		 * Parses markdown content and sanitizes the HTML
		 *
		 * @param {string} content
		 * @private
		 */
		_parseMarkdown: function (content) {
			return DOMPurify.sanitize(this.htmlWriter.render(this.markdownReader.parse(content), {
				smart: true,
				safe: true
			}), {
				ALLOWED_TAGS: this.allowedTags
			});
		},

		/**
		 * Adjusts the height of the element to match the content
		 * @private
		 */
		_adjustHeight: function () {
			this.infoContentSpinner.removeClass('icon-loading');
			var newHeight = this.infoContentContainer[0].scrollHeight;
			this.infoContentContainer.animate({
				height: newHeight + 40
			}, 500);
			this.infoContentContainer.scrollTop(0);
		},

		/**
		 * Adds copyright information to the information box
		 * @private
		 */
		_showCopyright: function () {
			if (!$.isEmptyObject(this.albumInfo.copyright) ||
				!$.isEmptyObject(this.albumInfo.copyrightLink)) {
				var copyright;
				var copyrightTitle = $('<h4/>');
				copyrightTitle.append(t('gallery', 'Copyright'));
				this.infoContentElement.append(copyrightTitle);

				if (!$.isEmptyObject(this.albumInfo.copyright)) {
					try {
						copyright = this._parseMarkdown(this.albumInfo.copyright);
					} catch (exception) {
						copyright =
							t('gallery',
								'Could not load the copyright notice: ' + exception.message);
					}
				} else {
					copyright = '<p>' + t('gallery', 'Copyright notice') + '</p>';
				}

				if (!$.isEmptyObject(this.albumInfo.copyrightLink)) {
					this._addCopyrightLink(copyright);
				} else {
					this.infoContentElement.append(copyright);
					this.infoContentElement.find('a').attr("target", "_blank");
				}
			}
		},

		/**
		 * Adds a link to a copyright document
		 *
		 * @param {string} copyright
		 * @private
		 */
		_addCopyrightLink: function (copyright) {
			var path = '/' + this.albumInfo.filePath;
			var file = this.albumInfo.copyrightLink;
			var copyrightUrl = Gallery.utility.buildFilesUrl(path, file);
			var copyrightElement = $(copyright);
			copyrightElement.find('a').removeAttr("href");
			copyright = copyrightElement.html();
			var copyrightLink = $('<a>', {
				html: copyright,
				title: t('gallery', 'Link to copyright document'),
				href: copyrightUrl,
				target: "_blank"
			});
			this.infoContentElement.append(copyrightLink);
		},

		/**
		 * Adds custom tags and rules to DomPurify
		 *
		 * @link https://github.com/cure53/DOMPurify/blob/master/demos/hooks-scheme-whitelist.html
		 * @private
		 */
		_initCustomSanitizer: function () {
			this.allowedTags =
				['p', 'b', 'em', 'i', 'pre', 'sup', 'sub', 'strong', 'strike', 'br', 'hr',
					'h1', 'h2', 'h3', 'li', 'ul', 'ol', 'a', 'img', 'blockquote', 'code'
				];

			// allowed URI schemes
			var whitelist = ['http', 'https'];

			// build fitting regex
			var regex = new RegExp('^(' + whitelist.join('|') + '):', 'gim');

			// Add a hook to enforce URI scheme whitelist
			DOMPurify.addHook('afterSanitizeAttributes', function (node) {

				// build an anchor to map URLs to
				var anchor = document.createElement('a');

				// check all href attributes for validity
				if (node.hasAttribute('href')) {
					anchor.href = node.getAttribute('href');
					if (anchor.protocol && !anchor.protocol.match(regex)) {
						node.removeAttribute('href');
					}
				}
				// check all action attributes for validity
				if (node.hasAttribute('action')) {
					anchor.href = node.getAttribute('action');
					if (anchor.protocol && !anchor.protocol.match(regex)) {
						node.removeAttribute('action');
					}
				}
				// check all xlink:href attributes for validity
				if (node.hasAttribute('xlink:href')) {
					anchor.href = node.getAttribute('xlink:href');
					if (anchor.protocol && !anchor.protocol.match(regex)) {
						node.removeAttribute('xlink:href');
					}
				}
			});
		}
	};

	Gallery.InfoBox = InfoBox;
})(jQuery, t, Gallery);
