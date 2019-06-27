/* global Gallery, escapeHTML */

(function ($, Gallery) {
	"use strict";

	/**
	 * @typedef {Object} Gallery.Share.Types.ShareInfo
	 * @property {Number} share_type
	 * @property {Number} permissions
	 * @property {Number} file_source optional
	 * @property {Number} item_source
	 * @property {String} token
	 * @property {String} share_with
	 * @property {String} share_with_displayname
	 * @property {String} mail_send
	 * @property {String} displayname_file_owner
	 * @property {String} displayname_owner
	 * @property {String} uid_owner
	 * @property {String} uid_file_owner
	 * @property {String} expiration optional
	 * @property {Number} stime
	 */

	// copied and stripped out from the old core
	var Share = {
		SHARE_TYPE_USER: 0,
		SHARE_TYPE_GROUP: 1,
		SHARE_TYPE_LINK: 3,
		SHARE_TYPE_EMAIL: 4,
		SHARE_TYPE_REMOTE: 6,

		/**
		 * @deprecated use OC.Share.currentShares instead
		 */
		itemShares: [],

		/**
		 * Shares for the currently selected file.
		 * (for which the dropdown is open)
		 *
		 * Key is item type and value is an array or
		 * shares of the given item type.
		 */
		currentShares: {},

		/**
		 * Whether the share dropdown is opened.
		 */
		droppedDown: false,

		/** @type {object} **/
		_lastSuggestions: undefined,

		/** @type {int} **/
		_pendingOperationsCount: 0,

		/**
		 *
		 * @param path {String} path to the file/folder which should be shared
		 * @param shareType {Number} 0 = user; 1 = group; 3 = public link; 6 = federated cloud
		 *     share
		 * @param shareWith {String} user / group id with which the file should be shared
		 * @param publicUpload {Boolean} allow public upload to a public shared folder
		 * @param password {String} password to protect public link Share with
		 * @param permissions {Number} 1 = read; 2 = update; 4 = create; 8 = delete; 16 = share; 31
		 *     = all (default: 31, for public shares: 1)
		 * @param callback {Function} method to call back after a successful share creation
		 * @param errorCallback {Function} method to call back after a failed share creation
		 *
		 * @returns {*}
		 */
		share: function (path, shareType, shareWith, publicUpload, password, permissions, callback, errorCallback) {
			return $.ajax({
				url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares' + '?format=json',
				type: 'POST',
				data: {
					path: path,
					shareType: shareType,
					shareWith: shareWith,
					publicUpload: publicUpload,
					password: password,
					permissions: permissions
				},
				dataType: 'json'
			}).done(function (result) {
				if (callback) {
					callback(result.ocs.data);
				}
			}).fail(function (xhr) {
				var result = xhr.responseJSON;
				if (_.isFunction(errorCallback)) {
					errorCallback(result);
				} else {
					var msg = t('gallery', 'Error');
					if (result.ocs && result.ocs.meta.message) {
						msg = result.ocs.meta.message;
					}
					OC.dialogs.alert(msg, t('gallery', 'Error while sharing'));
				}
			});
		},
		/**
		 *
		 * @param {Number} shareId
		 * @param {Function} callback
		 */
		unshare: function (shareId, callback) {
			$.ajax({
				url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares/' + shareId +
				'?format=json',
				type: 'DELETE'
			}).done(function () {
				if (callback) {
					callback();
				}
			}).fail(function () {
				OC.dialogs.alert(t('gallery', 'Error while unsharing'), t('gallery', 'Error'));

			});
		},
		/**
		 *
		 * @param {Number} shareId
		 * @param {Number} permissions
		 */
		setPermissions: function (shareId, permissions) {
			$.ajax({
				url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares/' + shareId +
				'?format=json',
				type: 'PUT',
				data: {
					permissions: permissions
				}
			}).fail(function () {
				OC.dialogs.alert(t('gallery', 'Error while changing permissions'),
					t('gallery', 'Error'));
			});
		},
		/**
		 *
		 * @param {String} searchTerm
		 * @param {String} itemType
		 */
		_getSuggestions: function (searchTerm, itemType) {
			if (this._lastSuggestions &&
				this._lastSuggestions.searchTerm === searchTerm &&
				this._lastSuggestions.itemType === itemType) {
				return this._lastSuggestions.promise;
			}

			var deferred = $.Deferred();

			$.get(OC.linkToOCS('apps/files_sharing/api/v1') + 'sharees', {
				format: 'json',
				search: searchTerm,
				perPage: 200,
				itemType: itemType
			}, function (result) {
				if (result.ocs.meta.statuscode === 100) {
					var filter = function(users) {
						var usersLength;

						var i;

						//Filter out the current user
						usersLength = users.length;
						for (i = 0; i < usersLength; i++) {
							if (users[i].value.shareWith === OC.currentUser) {
								users.splice(i, 1);
								break;
							}
						}
					}

					filter(result.ocs.data.exact.users);

					var exactUsers = result.ocs.data.exact.users;
					var exactGroups = result.ocs.data.exact.groups;
					var exactRemotes = result.ocs.data.exact.remotes;
					var exactEmails = [];
					if (typeof(result.ocs.data.emails) !== 'undefined') {
						exactEmails = result.ocs.data.exact.emails;
					}
					var exactCircles = [];
					if (typeof(result.ocs.data.circles) !== 'undefined') {
						exactCircles = result.ocs.data.exact.circles;
					}

					var exactMatches = exactUsers.concat(exactGroups).concat(exactRemotes).concat(exactEmails).concat(exactCircles);

					filter(result.ocs.data.users);

					var users = result.ocs.data.users;
					var groups = result.ocs.data.groups;
					var remotes = result.ocs.data.remotes;
					var lookup = result.ocs.data.lookup;
					var emails = [];
					if (typeof(result.ocs.data.emails) !== 'undefined') {
						emails = result.ocs.data.emails;
					}
					var circles = [];
					if (typeof(result.ocs.data.circles) !== 'undefined') {
						circles = result.ocs.data.circles;
					}

					var suggestions = exactMatches.concat(users).concat(groups).concat(remotes).concat(emails).concat(circles).concat(lookup);

					deferred.resolve(suggestions, exactMatches);
				} else {
					deferred.reject(result.ocs.meta.message);
				}
			}).fail(function () {
				deferred.reject();
			});

			this._lastSuggestions = {
				searchTerm: searchTerm,
				itemType: itemType,
				promise: deferred.promise()
			};

			return this._lastSuggestions.promise;
		},
		/**
		 *
		 * @param {int} shareType
		 * @param {int} possiblePermissions
		 */
		_getPermissions: function (shareType, possiblePermissions) {
			// Default permissions are Edit (CRUD) and Share
			// Check if these permissions are possible
			var permissions = OC.PERMISSION_READ;
			if (shareType === Gallery.Share.SHARE_TYPE_REMOTE) {
				permissions =
					OC.PERMISSION_CREATE | OC.PERMISSION_UPDATE | OC.PERMISSION_READ;
			} else {
				if (possiblePermissions & OC.PERMISSION_UPDATE) {
					permissions = permissions | OC.PERMISSION_UPDATE;
				}
				if (possiblePermissions & OC.PERMISSION_CREATE) {
					permissions = permissions | OC.PERMISSION_CREATE;
				}
				if (possiblePermissions & OC.PERMISSION_DELETE) {
					permissions = permissions | OC.PERMISSION_DELETE;
				}
				if (oc_appconfig.core.resharingAllowed &&
					(possiblePermissions & OC.PERMISSION_SHARE)) {
					permissions = permissions | OC.PERMISSION_SHARE;
				}
			}

			return permissions;
		},
		/**
		 *
		 * @param {String} itemType
		 * @param {String} path
		 * @param {String} appendTo
		 * @param {String} link
		 * @param {Number} possiblePermissions
		 * @param {String} filename
		 */
		showDropDown: function (itemType, path, appendTo, link, possiblePermissions, filename) {
			// This is a sync AJAX request on the main thread...
			var data = this._loadShares(path);
			var dropDownEl;
			var self = this;
			var html = '<div id="dropdown" class="drop shareDropDown" data-item-type="' + escapeHTML(itemType) +
				'" data-item-source="' + escapeHTML(path) + '">';
			if (data !== false && data[0] && !_.isUndefined(data[0].uid_file_owner) &&
				data[0].uid_file_owner !== OC.currentUser
			) {
				html += '<span class="reshare">';
				if (oc_config.enable_avatars === true) {
					html += '<div class="avatar"></div>';
				}

				if (data[0].share_type == this.SHARE_TYPE_GROUP) {
					html += t('gallery', 'Shared with you and the group {group} by {owner}', {
						group: data[0].share_with,
						owner: data[0].displayname_owner
					});
				} else {
					html += t('gallery', 'Shared with you by {owner}',
						{owner: data[0].displayname_owner});
				}
				html += '</span><br />';
				// reduce possible permissions to what the original share allowed
				possiblePermissions = possiblePermissions & data[0].permissions;
			}

			if (possiblePermissions & OC.PERMISSION_SHARE) {
				// Determine the Allow Public Upload status.
				// Used later on to determine if the
				// respective checkbox should be checked or
				// not.
				var publicUploadEnabled = $('#gallery').data('allow-public-upload');
				if (typeof publicUploadEnabled == 'undefined') {
					publicUploadEnabled = 'no';
				}
				var allowPublicUploadStatus = false;

				$.each(data, function (key, value) {
					if (value.share_type === self.SHARE_TYPE_LINK) {
						allowPublicUploadStatus =
							(value.permissions & OC.PERMISSION_CREATE) ? true : false;
						return true;
					}
				});

				var sharePlaceholder = t('gallery', 'Share with users or groups …');
				if (oc_appconfig.core.remoteShareAllowed) {
					sharePlaceholder = t('gallery', 'Share with users, groups or remote users …');
				}

				html += '<label for="shareWith" class="hidden-visually">' + t('gallery', 'Share') +
					'</label>';
				html +=
					'<input id="shareWith" type="text" placeholder="' + sharePlaceholder + '" />';
				html += '<span class="shareWithConfirm icon-confirm svg"></span>';
				html += '<span class="shareWithLoading icon-loading-small hidden"></span>';
				html += '<ul id="shareWithList">';
				html += '</ul>';
				var linksAllowed = $('#allowShareWithLink').val() === 'yes';
				if (link && linksAllowed) {
					html += '<div id="link" class="linkShare">';
					html += '<span class="icon-loading-small hidden"></span>';
					html +=
						'<input type="checkbox" class="checkbox checkbox--right" ' +
						'name="linkCheckbox" id="linkCheckbox" value="1" />' +
						'<label for="linkCheckbox">' + t('gallery', 'Share link') + '</label>';
					html += '<br />';

					var defaultExpireMessage = '';
					if ((itemType === 'folder' || itemType === 'file') &&
						oc_appconfig.core.defaultExpireDateEnforced) {
						defaultExpireMessage =
							t('gallery',
								'The public link will expire no later than {days} days after it is created',
								{'days': oc_appconfig.core.defaultExpireDate}) + '<br/>';
					}

					html += '<label for="linkText" class="hidden-visually">' + t('gallery', 'Link') +
						'</label>';
					html += '<div id="linkText-container">';
					html += '<input id="linkText" type="text" readonly="readonly" />';
					html += '<a id="linkTextMore" class="button icon-more" href="#"></a>';
					html += '<div id="linkSocial" class="popovermenu bubble menu hidden"></div>';
					html += '</div>';

					html +=
						'<input type="checkbox" class="checkbox checkbox--right" ' +
						'name="showPassword" id="showPassword" value="1" />' +
						'<label for="showPassword" style="display:none;">' +
						t('gallery', 'Password protect') + '</label>';
					html += '<div id="linkPass">';
					html += '<label for="linkPassText" class="hidden-visually">' +
						t('gallery', 'Password') + '</label>';
					html += '<input id="linkPassText" type="password" placeholder="' +
						t('gallery', 'Choose a password for the public link') + '" />';
					html += '<span class="icon-loading-small hidden"></span>';
					html += '</div>';

					if (itemType === 'folder' && (possiblePermissions & OC.PERMISSION_CREATE) &&
						publicUploadEnabled === 'yes') {
						html += '<div id="allowPublicUploadWrapper" style="display:none;">';
						html += '<span class="icon-loading-small hidden"></span>';
						html +=
							'<input type="checkbox" class="checkbox checkbox--right" value="1" name="allowPublicUpload" id="sharingDialogAllowPublicUpload"' +
							((allowPublicUploadStatus) ? 'checked="checked"' : '') + ' />';
						html += '<label for="sharingDialogAllowPublicUpload">' +
						t('gallery', 'Allow editing') + '</label>';
						html += '</div>';
					}

					var mailPublicNotificationEnabled = $('input:hidden[name=mailPublicNotificationEnabled]').val();
					if (mailPublicNotificationEnabled === 'yes') {
						html += '<form id="emailPrivateLink">';
						html +=
							'<input id="email" style="display:none; width:62%;" value="" placeholder="' +
							t('gallery', 'Email link to person') + '" type="text" />';
						html +=
							'<input id="emailButton" style="display:none;" type="submit" value="' +
							t('gallery', 'Send') + '" />';
						html += '</form>';
					}
				}

				html += '<div id="expiration">';
				html +=
					'<input type="checkbox" class="checkbox checkbox--right" ' +
					'name="expirationCheckbox" id="expirationCheckbox" value="1" />' +
					'<label for="expirationCheckbox">' +
					t('gallery', 'Set expiration date') + '</label>';
				html += '<label for="expirationDate" class="hidden-visually">' +
					t('gallery', 'Expiration') + '</label>';
				html += '<input id="expirationDate" type="text" placeholder="' +
					t('gallery', 'Expiration date') + '" style="display:none; width:90%;" />';
				html += '<em id="defaultExpireMessage">' + defaultExpireMessage + '</em>';
				html += '</div>';
				dropDownEl = $(html);
				dropDownEl = dropDownEl.appendTo(appendTo);

				//Get owner avatars
				if (oc_config.enable_avatars === true && data !== false && data[0] !== false &&
					!_.isUndefined(data[0]) && !_.isUndefined(data[0].uid_file_owner)) {
					dropDownEl.find(".avatar").avatar(data[0].uid_file_owner, 32);
				}

				// Reset item shares
				this.itemShares = [];
				this.currentShares = {};
				if (data) {
					$.each(data, function (index, share) {
						if (share.share_type === self.SHARE_TYPE_LINK) {
							self.showLink(share.id, share.token, share.share_with);
						} else {
							if (share.share_with !== OC.currentUser) {
								if (share.share_type === self.SHARE_TYPE_REMOTE) {
									self._addShareWith(share.id,
										share.share_type,
										share.share_with,
										share.share_with_displayname,
										share.permissions,
										OC.PERMISSION_READ | OC.PERMISSION_UPDATE |
										OC.PERMISSION_CREATE,
										share.mail_send,
										false);
								} else {
									self._addShareWith(share.id,
										share.share_type,
										share.share_with,
										share.share_with_displayname,
										share.permissions,
										possiblePermissions,
										share.mail_send,
										false);
								}
							}
						}
						if (share.expiration != null) {
							var expireDate = moment(share.expiration, 'YYYY-MM-DD').format(
								'DD-MM-YYYY');
							self.showExpirationDate(expireDate, share.stime);
						}
					});
				}
				$('#shareWith').autocomplete({
					minLength: 2,
					delay: 750,
					source: function (search, response) {
						var $shareWithField = $('#dropdown #shareWith');
						var $loading = $('#dropdown .shareWithLoading');
						var $confirm = $('#dropdown .shareWithConfirm');
						$loading.removeClass('hidden');
						$confirm.addClass('hidden');
						self._pendingOperationsCount++;

						$shareWithField.removeClass('error')
							.tooltip('hide');

						self._getSuggestions(
							search.term.trim(),
							itemType
						).done(function(suggestions) {
							self._pendingOperationsCount--;
							if (self._pendingOperationsCount === 0) {
								$loading.addClass('hidden');
								$confirm.removeClass('hidden');
							}

							if (suggestions.length > 0) {
								$('#shareWith')
									.autocomplete("option", "autoFocus", true);

								response(suggestions);

								// show a notice that the list is truncated
								// this is the case if one of the search results is at least as long as the max result config option
								if (oc_config['sharing.maxAutocompleteResults'] > 0 &&
									Math.min(perPage, oc_config['sharing.maxAutocompleteResults'])
									<= Math.max(users.length, groups.length, remotes.length, emails.length, lookup.length)) {

									var message = t('gallery', 'This list is maybe truncated - please refine your search term to see more results.');
									$('.ui-autocomplete').append('<li class="autocomplete-note">' + message + '</li>');
								}

							} else {
								var title = t('gallery', 'No users or groups found for {search}', {search: $('#shareWith').val()});
								if (!oc_appconfig.core.allowGroupSharing) {
									title = t('gallery', 'No users found for {search}', {search: $('#shareWith').val()});
								}
								$('#shareWith').addClass('error')
									.attr('data-original-title', title)
									.tooltip('hide')
									.tooltip({
										placement: 'bottom',
										trigger: 'manual'
									})
									.tooltip('fixTitle')
									.tooltip('show');
								response();
							}
						}).fail(function (message) {
							self._pendingOperationsCount--;
							if (self._pendingOperationsCount === 0) {
								$('#dropdown').find('.shareWithLoading').addClass('hidden');
								$('#dropdown').find('.shareWithConfirm').removeClass('hidden');
							}

							if (message) {
								OC.Notification.showTemporary(t('gallery', 'An error occurred ("{message}"). Please try again', { message: message }));
							} else {
								OC.Notification.showTemporary(t('gallery', 'An error occurred. Please try again'));
							}
						});
					},
					focus: function (event) {
						event.preventDefault();
					},
					select: function (event, selected) {
						// Ensure that the keydown handler for the input field
						// is not called; otherwise it would try to add the
						// recipient again, which would fail.
						event.stopImmediatePropagation();
						var $dropDown = $('#dropdown');
						var itemSource = $dropDown.data('item-source');
						var expirationDate = '';
						if ($('#expirationCheckbox').is(':checked') === true) {
							expirationDate = $("#expirationDate").val();
						}
						var shareType = selected.item.value.shareType;
						var shareWith = selected.item.value.shareWith;
						$(this).val(shareWith);
						var permissions = self._getPermissions(shareType, possiblePermissions);

						var $input = $(this);
						var $loading = $dropDown.find('.shareWithLoading');
						var $confirm = $dropDown.find('.shareWithConfirm');
						$loading.removeClass('hidden');
						$confirm.addClass('hidden');
						$input.val(t('gallery', 'Adding user...'));
						$input.prop('disabled', true);
						self._pendingOperationsCount++;
						Gallery.Share.share(
							itemSource,
							shareType,
							shareWith,
							0,
							null,
							permissions,
							function (data) {
								// Adding a share changes the suggestions.
								self._lastSuggestions = undefined;

								var posPermissions = possiblePermissions;
								if (shareType === Gallery.Share.SHARE_TYPE_REMOTE) {
									posPermissions = permissions;
								}
								Gallery.Share._addShareWith(data.id, shareType, shareWith,
									selected.item.label,
									permissions, posPermissions);

								$input.val('');
								$input.prop('disabled', false);

								self._pendingOperationsCount--;
								if (self._pendingOperationsCount === 0) {
									$loading.addClass('hidden');
									$confirm.removeClass('hidden');
								}
							},
							function (result) {
								var message = t('gallery', 'Error');
								if (result && result.ocs && result.ocs.meta && result.ocs.meta.message) {
									message = result.ocs.meta.message;
								}
								OC.Notification.showTemporary(message);

								$input.val(shareWith);
								$input.prop('disabled', false);

								self._pendingOperationsCount--;
								if (self._pendingOperationsCount === 0) {
									$loading.addClass('hidden');
									$confirm.removeClass('hidden');
								}
							});
						return false;
					}
				}).data("ui-autocomplete")._renderItem = function (ul, item) {
					// customize internal _renderItem function to display groups and users
					// differently
					var insert = $("<a>");
					var text = item.label;
					if (item.value.shareType === Gallery.Share.SHARE_TYPE_GROUP) {
						text = text + ' (' + t('gallery', 'group') + ')';
					} else if (item.value.shareType === Gallery.Share.SHARE_TYPE_REMOTE) {
						text = text + ' (' + t('gallery', 'remote') + ')';
					}
					insert.text(text);
					if (item.value.shareType === Gallery.Share.SHARE_TYPE_GROUP) {
						insert = insert.wrapInner('<strong></strong>');
					}
					return $("<li>")
						.addClass(
							(item.value.shareType ===
							Gallery.Share.SHARE_TYPE_GROUP) ? 'group' : 'user')
						.append(insert)
						.appendTo(ul);
				};

				var shareFieldKeydownHandler = function(event) {
					if (event.keyCode !== 13) {
						return true;
					}

					self._confirmShare(itemType, possiblePermissions);

					return false;
				};

				$('#shareWith').on('keydown', shareFieldKeydownHandler);

				$('#shareWith').on('input', function () {
					if ($(this).val().length < 2) {
						$(this).removeClass('error').tooltip('hide');
					}
				});

				/* trigger search after the field was re-selected */
				$('#shareWith').on('focus', function() {
					$(this).autocomplete('search');
				});

				$('.shareWithConfirm').on('click', function () {
					self._confirmShare(itemType, possiblePermissions);
				});

				if (link && linksAllowed && $('#email').length != 0) {
					$('#email').autocomplete({
						minLength: 1,
						source: function (search, response) {
							$.get(OC.filePath('core', 'ajax', 'share.php'), {
								fetch: 'getShareWithEmail',
								search: search.term
							}, function (result) {
								if (result.status == 'success' && result.data.length > 0) {
									response(result.data);
								}
							});
						},
						select: function (event, item) {
							$('#email').val(item.item.email);
							return false;
						}
					})
						.data("ui-autocomplete")._renderItem = function (ul, item) {
						return $('<li>')
							.append('<a>' + escapeHTML(item.displayname) + "<br>" +
							escapeHTML(item.email) + '</a>')
							.appendTo(ul);
					};
				}

			} else {
				html += '<input id="shareWith" type="text" placeholder="' +
					t('gallery', 'Resharing is not allowed') +
					'" style="width:90%;" disabled="disabled"/>';
				html += '</div>';
				dropDownEl = $(html);
				dropDownEl.appendTo(appendTo);
			}
			dropDownEl.attr('data-item-source-name', filename);
			$('#dropdown').slideDown(OC.menuSpeed, function () {
				Gallery.Share.droppedDown = true;
			});
			if ($('html').hasClass('lte9')) {
				$('#dropdown input[placeholder]').placeholder();
			}
			$('#shareWith').focus();
		},
		_confirmShare: function (itemType, possiblePermissions) {
			var self = this;
			var $shareWithField = $('#dropdown #shareWith');
			var $loading = $('#dropdown .shareWithLoading');
			var $confirm = $('#dropdown .shareWithConfirm');

			$loading.removeClass('hidden');
			$confirm.addClass('hidden');
			this._pendingOperationsCount++;

			$shareWithField.prop('disabled', true);

			// Disabling the autocompletion does not clear its search timeout;
			// removing the focus from the input field does, but only if the
			// autocompletion is not disabled when the field loses the focus.
			// Thus, the field has to be disabled before disabling the
			// autocompletion to prevent an old pending search result from
			// being processed once the field is enabled again.
			$shareWithField.autocomplete('close');
			$shareWithField.autocomplete('disable');

			var itemSource = $('#dropdown').data('item-source');
			var expirationDate = '';
			if ($('#expirationCheckbox').is(':checked') === true) {
				expirationDate = $("#expirationDate").val();
			}

			var restoreUI = function() {
				self._pendingOperationsCount--;
				if (self._pendingOperationsCount === 0) {
					$loading.addClass('hidden');
					$confirm.removeClass('hidden');
				}

				$shareWithField.prop('disabled', false);
				$shareWithField.focus();
			};

			this._getSuggestions(
				$shareWithField.val(),
				itemType
			).done(function(suggestions, exactMatches) {
				if (suggestions.length === 0) {
					restoreUI();

					$shareWithField.autocomplete('enable');

					// There is no need to show an error message here; it will
					// be automatically shown when the autocomplete is activated
					// again (due to the focus on the field) and it finds no
					// matches.

					return;
				}

				if (exactMatches.length !== 1) {
					restoreUI();

					$shareWithField.autocomplete('enable');

					return;
				}

				var shareType = exactMatches[0].value.shareType;
				var shareWith = exactMatches[0].value.shareWith;
				var permissions = self._getPermissions(shareType, possiblePermissions);

				var actionSuccess = function(data) {
					var updatedPossiblePermissions = possiblePermissions;
					if (shareType === Gallery.Share.SHARE_TYPE_REMOTE) {
						updatedPossiblePermissions = permissions;
					}
					Gallery.Share._addShareWith(data.id, shareType, shareWith,
						exactMatches[0].label,
						permissions, updatedPossiblePermissions);

					// Adding a share changes the suggestions.
					self._lastSuggestions = undefined;

					$shareWithField.val('');

					restoreUI();

					$shareWithField.autocomplete('enable');
				};

				var actionError = function(result) {
					restoreUI();

					$shareWithField.autocomplete('enable');

					var message = t('gallery', 'Error');
					if (result && result.ocs && result.ocs.meta && result.ocs.meta.message) {
						message = result.ocs.meta.message;
					}
					OC.Notification.showTemporary(message);
				};

				Gallery.Share.share(
					itemSource,
					shareType,
					shareWith,
					0,
					null,
					permissions,
					actionSuccess,
					actionError
				);
			}).fail(function (message) {
				restoreUI();

				$shareWithField.autocomplete('enable');

				// There is no need to show an error message here; it will be
				// automatically shown when the autocomplete is activated again
				// (due to the focus on the field) and getting the suggestions
				// fail.
			});
		},
		/**
		 *
		 * @param callback
		 */
		hideDropDown: function (callback) {
			this.currentShares = null;
			$('#dropdown').slideUp(OC.menuSpeed, function () {
				Gallery.Share.droppedDown = false;
				$('#dropdown').remove();
				if (typeof FileActions !== 'undefined') {
					$('tr').removeClass('mouseOver');
				}
				if (callback) {
					callback.call();
				}
			});
		},
		/**
		 *
		 * @param id
		 * @param token
		 * @param password
		 */
		showLink: function (id, token, password) {
			var $linkCheckbox = $('#linkCheckbox');
			this.itemShares[this.SHARE_TYPE_LINK] = true;
			$linkCheckbox.attr('checked', true);
			$linkCheckbox.data('id', id);
			var $linkText = $('#linkText');

			if (Gallery.appName === 'files') {
				var link = parent.location.protocol + '//' + location.host +
					OC.generateUrl('/s/') + token;
			} else {
				var link = parent.location.protocol + '//' + location.host +
					OC.generateUrl('/apps/gallery/s/') + token;
			}

			$linkText.val(link);
			$linkText.slideDown(OC.menuSpeed);
			$linkText.css('display', 'block');
			if (oc_appconfig.core.enforcePasswordForPublicLink === false || password === null) {
				$('#showPassword+label').show();
			}
			if (password != null) {
				$('#linkPass').slideDown(OC.menuSpeed);
				$('#showPassword').attr('checked', true);
				$('#linkPassText').attr('placeholder', '**********');
			}
			$('#expiration').show();
			$('#emailPrivateLink #email').show();
			$('#emailPrivateLink #emailButton').show();
			$('#allowPublicUploadWrapper').show();
			$('#linkTextMore').show();
			$('#linkSocial').hide();
			$('#linkSocial').html('');

			var ul = $('<ul/>');

			OC.Share.Social.Collection.each(function(model) {
				var url = model.get('url');
				url = url.replace('{{reference}}', link);

				var li = $('<li>' +
					'<a href="#" class="menuitem pop-up" data-url="' + url + '" data-window="'+model.get('newWindow')+'">' +
					'<span class="icon ' + model.get('iconClass') + '"></span>' +
					'<span>' + model.get('name') + '</span>' +
					'</a>');
				li.appendTo(ul);
			});
			ul.appendTo('#linkSocial');

			if (OC.Share.Social.Collection.length === 0) {
				$('#linkTextMore').hide();
				$linkText.addClass('no-menu-item');
			} else {
				$linkText.removeClass('no-menu-item');
			}
		},
		/**
		 *
		 */
		hideLink: function () {
			$('#linkText').slideUp(OC.menuSpeed);
			$('#defaultExpireMessage').hide();
			$('#showPassword+label').hide();
			$('#linkSocial').hide();
			$('#linkTextMore').hide();
			$('#linkPass').slideUp(OC.menuSpeed);
			$('#emailPrivateLink #email').hide();
			$('#emailPrivateLink #emailButton').hide();
			$('#allowPublicUploadWrapper').hide();
		},
		/**
		 * Displays the expiration date field
		 *
		 * @param {String} date current expiration date
		 * @param {Date|Number|String} [shareTime] share timestamp in seconds, defaults to now
		 */
		showExpirationDate: function (date, shareTime) {
			var $expirationDate = $('#expirationDate');
			var $expirationCheckbox = $('#expirationCheckbox');
			var now = new Date();
			// min date should always be the next day
			var minDate = new Date();
			minDate.setDate(minDate.getDate() + 1);
			var datePickerOptions = {
				minDate: minDate,
				maxDate: null
			};
			// TODO: hack: backend returns string instead of integer
			shareTime = this._parseTime(shareTime);
			if (_.isNumber(shareTime)) {
				shareTime = new Date(shareTime * 1000);
			}
			if (!shareTime) {
				shareTime = now;
			}
			$expirationCheckbox.attr('checked', true);
			$expirationDate.val(date);
			$expirationDate.slideDown(OC.menuSpeed);
			$expirationDate.css('display', 'block');
			$expirationDate.datepicker({
				dateFormat: 'dd-mm-yy'
			});
			if (oc_appconfig.core.defaultExpireDateEnforced) {
				$expirationCheckbox.attr('disabled', true);
				shareTime = OC.Util.stripTime(shareTime).getTime();
				// max date is share date + X days
				datePickerOptions.maxDate =
					new Date(shareTime + oc_appconfig.core.defaultExpireDate * 24 * 3600 * 1000);
			}
			if (oc_appconfig.core.defaultExpireDateEnabled) {
				$('#defaultExpireMessage').slideDown(OC.menuSpeed);
			}
			$.datepicker.setDefaults(datePickerOptions);
		},
		/**
		 * Get the default Expire date
		 *
		 * @return {String} The expire date
		 */
		getDefaultExpirationDate: function () {
			var expireDateString = '';
			if (oc_appconfig.core.defaultExpireDateEnabled) {
				var date = new Date().getTime();
				var expireAfterMs = oc_appconfig.core.defaultExpireDate * 24 * 60 * 60 * 1000;
				var expireDate = new Date(date + expireAfterMs);
				var month = expireDate.getMonth() + 1;
				var year = expireDate.getFullYear();
				var day = expireDate.getDate();
				expireDateString = year + "-" + month + '-' + day + ' 00:00:00';
			}
			return expireDateString;
		},
		/**
		 * Loads all shares associated with a path
		 *
		 * @param path
		 *
		 * @returns {Gallery.Share.Types.ShareInfo|Boolean}
		 * @private
		 */
		_loadShares: function (path) {
			var data = false;
			var url = OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares' + '?format=json';
			$.ajax({
				url: url,
				type: 'GET',
				data: {
					path: path,
					shared_with_me: true
				},
				async: false
			}).done(function (result) {
				data = result.ocs.data;
				$.ajax({
					url: url,
					type: 'GET',
					data: {
						path: path,
						reshares: true
					},
					async: false
				}).done(function (result) {
					data = _.union(data, result.ocs.data);
				})

			});

			if (data === false) {
				OC.dialogs.alert(t('gallery', 'Error while retrieving shares'),
					t('gallery', 'Error'));
			}

			return data;
		},
		/**
		 *
		 * @param shareId
		 * @param shareType
		 * @param shareWith
		 * @param shareWithDisplayName
		 * @param permissions
		 * @param possiblePermissions
		 * @param mailSend
		 *
		 * @private
		 */
		_addShareWith: function (shareId, shareType, shareWith, shareWithDisplayName, permissions, possiblePermissions, mailSend) {
			var shareItem = {
				share_id: shareId,
				share_type: shareType,
				share_with: shareWith,
				share_with_displayname: shareWithDisplayName,
				permissions: permissions
			};
			if (shareType === this.SHARE_TYPE_GROUP) {
				shareWithDisplayName = shareWithDisplayName + " (" + t('gallery', 'group') + ')';
			}
			if (shareType === this.SHARE_TYPE_REMOTE) {
				shareWithDisplayName = shareWithDisplayName + " (" + t('gallery', 'remote') + ')';
			}
			if (!this.itemShares[shareType]) {
				this.itemShares[shareType] = [];
			}
			this.itemShares[shareType].push(shareWith);

			var editChecked = '',
				createChecked = '',
				updateChecked = '',
				deleteChecked = '',
				shareChecked = '';
			if (permissions & OC.PERMISSION_CREATE) {
				createChecked = 'checked="checked"';
				editChecked = 'checked="checked"';
			}
			if (permissions & OC.PERMISSION_UPDATE) {
				updateChecked = 'checked="checked"';
				editChecked = 'checked="checked"';
			}
			if (permissions & OC.PERMISSION_DELETE) {
				deleteChecked = 'checked="checked"';
				editChecked = 'checked="checked"';
			}
			if (permissions & OC.PERMISSION_SHARE) {
				shareChecked = 'checked="checked"';
			}
			var html = '<li style="clear: both;" ' +
				'data-id="' + escapeHTML(shareId) + '"' +
				'data-share-type="' + escapeHTML(shareType) + '"' +
				'data-share-with="' + escapeHTML(shareWith) + '"' +
				'title="' + escapeHTML(shareWith) + '">';
			var showCrudsButton;
			html +=
				'<a href="#" class="unshare"><img class="svg" alt="' + t('gallery', 'Unshare') +
				'" title="' + t('gallery', 'Unshare') + '" src="' +
				OC.imagePath('core', 'actions/delete') + '"/></a>';
			if (oc_config.enable_avatars === true) {
				html += '<div class="avatar"></div>';
			}
			html += '<span class="username">' + escapeHTML(shareWithDisplayName) + '</span>';
			var mailNotificationEnabled = $('input:hidden[name=mailNotificationEnabled]').val();
			if (mailNotificationEnabled === 'yes' &&
				shareType !== this.SHARE_TYPE_REMOTE) {
				var checked = '';
				if (mailSend === 1) {
					checked = 'checked';
				}
				html +=
					'<input id="mail-' + escapeHTML(shareWith) + '" type="checkbox" class="mailNotification checkbox checkbox--right" ' +
					'name="mailNotification" ' +
					checked + ' />';
				html +=
					'<label for="mail-' + escapeHTML(shareWith) + '">' + t('gallery', 'notify by email') + '</label>';
			}
			if (oc_appconfig.core.resharingAllowed &&
				(possiblePermissions & OC.PERMISSION_SHARE)) {
				html += '<input id="canShare-' + escapeHTML(shareWith) +
					'" type="checkbox" class="permissions checkbox checkbox--right" name="share" ' +
					shareChecked + ' data-permissions="' + OC.PERMISSION_SHARE + '" />';
				html += '<label for="canShare-' + escapeHTML(shareWith) + '">' +
					t('gallery', 'can share') + '</label>';
			}
			if (possiblePermissions & OC.PERMISSION_CREATE ||
				possiblePermissions & OC.PERMISSION_UPDATE ||
				possiblePermissions & OC.PERMISSION_DELETE) {
				html += '<input id="canEdit-' + escapeHTML(shareWith) +
					'" type="checkbox" class="permissions checkbox checkbox--right" name="edit" ' +
					editChecked + ' />';
				html += '<label for="canEdit-' + escapeHTML(shareWith) + '">' +
					t('gallery', 'can edit') + '</label>';
			}
			if (shareType !== this.SHARE_TYPE_REMOTE) {
				showCrudsButton = '<a href="#" class="showCruds"><img class="svg" alt="' +
					t('gallery', 'access control') + '" src="' +
					OC.imagePath('core', 'actions/triangle-s') + '"/></a>';
			}
			html += '<div class="cruds" style="display:none;">';
			if (possiblePermissions & OC.PERMISSION_CREATE) {
				html += '<input id="canCreate-' + escapeHTML(shareWith) +
					'" type="checkbox" class="permissions checkbox checkbox--right" name="create" ' +
					createChecked + ' data-permissions="' + OC.PERMISSION_CREATE + '"/>';
				html += '<label for="canCreate-' + escapeHTML(shareWith) + '">' +
					t('gallery', 'create') + '</label>';
			}
			if (possiblePermissions & OC.PERMISSION_UPDATE) {
				html += '<input id="canUpdate-' + escapeHTML(shareWith) +
					'" type="checkbox" class="permissions checkbox checkbox--right" name="update" ' +
					updateChecked + ' data-permissions="' + OC.PERMISSION_UPDATE + '"/>';
				html += '<label for="canUpdate-' + escapeHTML(shareWith) + '">' +
					t('gallery', 'change') + '</label>';
			}
			if (possiblePermissions & OC.PERMISSION_DELETE) {
				html += '<input id="canDelete-' + escapeHTML(shareWith) +
					'" type="checkbox" class="permissions checkbox checkbox--right" name="delete" ' +
					deleteChecked + ' data-permissions="' + OC.PERMISSION_DELETE + '"/>';
				html += '<label for="canDelete-' + escapeHTML(shareWith) + '">' +
					t('gallery', 'delete') + '</label>';
			}
			html += '</div>';
			html += '</li>';
			html = $(html).appendTo('#dropdown #shareWithList');
			if (oc_config.enable_avatars === true) {
				if (shareType === this.SHARE_TYPE_USER) {
					html.find('.avatar').avatar(escapeHTML(shareWith), 32);
				} else {
					//Add sharetype to generate different seed if there is a group and use with
					// the same name
					html.find('.avatar').imageplaceholder(
						escapeHTML(shareWith) + ' ' + shareType);
				}
			}
			// insert cruds button into last label element
			var lastLabel = html.find('>label:last');
			if (lastLabel.exists()) {
				lastLabel.append(showCrudsButton);
			}
			else {
				html.find('.cruds').before(showCrudsButton);
			}
			if (!this.currentShares[shareType]) {
				this.currentShares[shareType] = [];
			}
			this.currentShares[shareType].push(shareItem);
		},
		/**
		 * Parses a string to an valid integer (unix timestamp)
		 * @param time
		 * @returns {*}
		 * @internal Only used to work around a bug in the backend
		 * @private
		 */
		_parseTime: function (time) {
			if (_.isString(time)) {
				// skip empty strings and hex values
				if (time === '' || (time.length > 1 && time[0] === '0' && time[1] === 'x')) {
					return null;
				}
				time = parseInt(time, 10);
				if (isNaN(time)) {
					time = null;
				}
			}
			return time;
		}
	};

	Gallery.Share = Share;
})(jQuery, Gallery);

$(document).ready(function () {

	if (typeof monthNames != 'undefined') {
		// min date should always be the next day
		var minDate = new Date();
		minDate.setDate(minDate.getDate() + 1);
		$.datepicker.setDefaults({
			monthNames: monthNames,
			monthNamesShort: $.map(monthNames, function (v) {
				return v.slice(0, 3) + '.';
			}),
			dayNames: dayNames,
			dayNamesMin: $.map(dayNames, function (v) {
				return v.slice(0, 2);
			}),
			dayNamesShort: $.map(dayNames, function (v) {
				return v.slice(0, 3) + '.';
			}),
			firstDay: firstDay,
			minDate: minDate
		});
	}
	$(document).on('click', 'a.share', function (event) {
		event.stopPropagation();
		if ($(this).data('item-type') !== undefined && $(this).data('path') !== undefined) {
			var itemType = $(this).data('item-type');
			var path = $(this).data('path');
			var appendTo = $(this).parents('#controls, #slideshow')[0];
			var link = false;
			var possiblePermissions = $(this).data('possible-permissions');
			if ($(this).data('link') !== undefined && $(this).data('link') == true) {
				link = true;
			}
			if (Gallery.Share.droppedDown) {
				if (path != $('#dropdown').data('path')) {
					Gallery.Share.hideDropDown(function () {
						Gallery.Share.showDropDown(itemType, path, appendTo, link,
							possiblePermissions);
					});
				} else {
					Gallery.Share.hideDropDown();
				}
			} else {
				Gallery.Share.showDropDown(itemType, path, appendTo, link, possiblePermissions);
			}
		}
	});

	$(this).click(function (event) {
		var target = $(event.target);
		var isMatched = !target.is('.drop, .ui-datepicker-next, .ui-datepicker-prev, .ui-icon')
			&& !target.closest('#ui-datepicker-div').length &&
			!target.closest('.ui-autocomplete').length;
		if (Gallery.Share.droppedDown && isMatched &&
			$('#dropdown').has(event.target).length === 0) {
			Gallery.Share.hideDropDown();
		}
	});

	$(document).on('click', '#dropdown .showCruds', function () {
		$(this).closest('li').find('.cruds').toggle();
		return false;
	});

	$(document).on('click', '#dropdown .unshare', function () {
		var $li = $(this).closest('li');
		var shareType = $li.data('share-type');
		var shareWith = $li.attr('data-share-with');
		var shareId = $li.data('id');
		var $button = $(this);

		if (!$button.is('a')) {
			$button = $button.closest('a');
		}

		if ($button.hasClass('icon-loading-small')) {
			// deletion in progress
			return false;
		}
		$button.empty().addClass('icon-loading-small');
		Gallery.Share.unshare(shareId, function () {
			$li.remove();
			var index = Gallery.Share.itemShares[shareType].indexOf(shareWith);
			Gallery.Share.itemShares[shareType].splice(index, 1);
			// updated list of shares
			Gallery.Share.currentShares[shareType].splice(index, 1);
		});

		return false;
	});

	$(document).on('change', '#dropdown .permissions', function () {
		var $li = $(this).closest('li');
		var checkboxes = $('.permissions', $li);
		if ($(this).attr('name') == 'edit') {
			var checked = $(this).is(':checked');
			// Check/uncheck Create, Update, and Delete checkboxes if Edit is checked/unck
			$(checkboxes).filter('input[name="create"]').attr('checked', checked);
			$(checkboxes).filter('input[name="update"]').attr('checked', checked);
			$(checkboxes).filter('input[name="delete"]').attr('checked', checked);
		} else {
			// Uncheck Edit if Create, Update, and Delete are not checked
			if (!$(this).is(':checked')
				&& !$(checkboxes).filter('input[name="create"]').is(':checked')
				&& !$(checkboxes).filter('input[name="update"]').is(':checked')
				&& !$(checkboxes).filter('input[name="delete"]').is(':checked')) {
				$(checkboxes).filter('input[name="edit"]').attr('checked', false);
				// Check Edit if Create, Update, or Delete is checked
			} else if (($(this).attr('name') == 'create'
				|| $(this).attr('name') == 'update'
				|| $(this).attr('name') == 'delete')) {
				$(checkboxes).filter('input[name="edit"]').attr('checked', true);
			}
		}
		var permissions = OC.PERMISSION_READ;
		$(checkboxes).filter(':not(input[name="edit"])').filter(':checked').each(
			function (index, checkbox) {
				permissions |= $(checkbox).data('permissions');
			});

		Gallery.Share.setPermissions($li.data('id'), permissions);
	});

	$(document).on('change', '#dropdown #linkCheckbox', function () {
		var $dropDown = $('#dropdown');
		var path = $dropDown.data('item-source');
		var shareId = $('#linkCheckbox').data('id');
		var shareWith = '';
		var publicUpload = 0;
		var $loading = $dropDown.find('#link .icon-loading-small');
		var $button = $(this);

		if (!$loading.hasClass('hidden')) {
			// already in progress
			return false;
		}

		if (this.checked) {
			// Reset password placeholder
			$('#linkPassText').attr('placeholder',
				t('gallery', 'Choose a password for the public link'));
			// Reset link
			$('#linkText').val('');
			$('#showPassword').prop('checked', false);
			$('#linkPass').hide();
			$('#linkSocial').hide();
			$('#linkTextMore').hide();
			$('#sharingDialogAllowPublicUpload').prop('checked', false);
			$('#expirationCheckbox').prop('checked', false);
			$('#expirationDate').hide();
			var expireDateString = '';
			// Create a link
			if (oc_appconfig.core.enforcePasswordForPublicLink === false) {
				expireDateString = Gallery.Share.getDefaultExpirationDate();
				$loading.removeClass('hidden');
				$button.addClass('hidden');
				$button.prop('disabled', true);
				Gallery.Share.share(
					path,
					Gallery.Share.SHARE_TYPE_LINK,
					shareWith,
					publicUpload,
					null,
					OC.PERMISSION_READ,
					function (data) {
						$loading.addClass('hidden');
						$button.removeClass('hidden');
						$button.prop('disabled', false);
						Gallery.Share.showLink(data.id, data.token, null);
					});
			} else {
				$('#linkPass').slideToggle(OC.menuSpeed);
				$('#linkPassText').focus();
			}
			if (expireDateString !== '') {
				Gallery.Share.showExpirationDate(expireDateString);
			}
		} else {
			// Delete private link
			Gallery.Share.hideLink();
			$('#expiration').slideUp(OC.menuSpeed);
			if ($('#linkText').val() !== '') {
				$loading.removeClass('hidden');
				$button.addClass('hidden');
				$button.prop('disabled', true);
				Gallery.Share.unshare(shareId, function () {
					$loading.addClass('hidden');
					$button.removeClass('hidden');
					$button.prop('disabled', false);
					$('#linkCheckbox').data('id', undefined);
					Gallery.Share.itemShares[Gallery.Share.SHARE_TYPE_LINK] = false;
				});
			}
		}
	});

	$(document).on('click', '#dropdown #linkText', function () {
		$(this).focus();
		$(this).select();
	});

	// Handle the Allow Public Upload Checkbox
	$(document).on('click', '#sharingDialogAllowPublicUpload', function () {

		// Gather data
		var $dropDown = $('#dropdown');
		var shareId = $('#linkCheckbox').data('id');
		var allowPublicUpload = $(this).is(':checked');
		var $button = $(this);
		var $loading = $dropDown.find('#allowPublicUploadWrapper .icon-loading-small');

		if (!$loading.hasClass('hidden')) {
			// already in progress
			return false;
		}

		// Update the share information
		$button.addClass('hidden');
		$button.prop('disabled', true);
		$loading.removeClass('hidden');
		//(path, shareType, shareWith, publicUpload, password, permissions)
		$.ajax({
			url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares/' + shareId +
			'?format=json',
			type: 'PUT',
			data: {
				publicUpload: allowPublicUpload
			}
		}).done(function () {
			$loading.addClass('hidden');
			$button.removeClass('hidden');
			$button.prop('disabled', false);
		});
	});

	$(document).on('click', '#dropdown #showPassword', function () {
		$('#linkPass').slideToggle(OC.menuSpeed);
		if (!$('#showPassword').is(':checked')) {
			var shareId = $('#linkCheckbox').data('id');
			var $loading = $('#showPassword .icon-loading-small');

			$loading.removeClass('hidden');
			$.ajax({
				url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares/' + shareId +
				'?format=json',
				type: 'PUT',
				data: {
					password: null
				}
			}).done(function () {
				$loading.addClass('hidden');
				$('#linkPassText').attr('placeholder',
					t('gallery', 'Choose a password for the public link'));
			});
		} else {
			$('#linkPassText').focus();
		}
	});

	$(document).on('focusout keyup', '#dropdown #linkPassText', function (event) {
		var linkPassText = $('#linkPassText');
		if (linkPassText.val() != '' && (event.type == 'focusout' || event.keyCode == 13)) {
			var dropDown = $('#dropdown');
			var $loading = dropDown.find('#linkPass .icon-loading-small');
			var shareId = $('#linkCheckbox').data('id');

			$loading.removeClass('hidden');
			linkPassText.removeClass('warning');
			$.ajax({
				url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares/' + shareId +
				'?format=json',
				type: 'PUT',
				data: {
					password: $('#linkPassText').val()
				}
			}).done(function (data) {
				$loading.addClass('hidden');
				linkPassText.val('');
				linkPassText.attr('placeholder', t('gallery', 'Password protected'));

				if (oc_appconfig.core.enforcePasswordForPublicLink) {
					Gallery.Share.showLink(data.id, data.token, "password set");
				}
			}).fail(function (xhr) {
				var result = xhr.responseJSON;
				$loading.addClass('hidden');
				linkPassText.val('');
				linkPassText.addClass('warning');
				linkPassText.attr('placeholder', result.ocs.meta.message);
			});
		}
	});

	$(document).on('click', '#dropdown #expirationCheckbox', function () {
		if (this.checked) {
			Gallery.Share.showExpirationDate('');
		} else {
			var shareId = $('#linkCheckbox').data('id');
			$.ajax({
				url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares/' + shareId +
				'?format=json',
				type: 'PUT',
				data: {
					expireDate: ''
				}
			}).done(function () {
				$('#expirationDate').slideUp(OC.menuSpeed);
				if (oc_appconfig.core.defaultExpireDateEnforced === false) {
					$('#defaultExpireMessage').slideDown(OC.menuSpeed);
				}
			}).fail(function () {
				OC.dialogs.alert(t('gallery', 'Error unsetting expiration date'),
					t('gallery', 'Error'));
			});
		}
	});

	$(document).on('change', '#dropdown #expirationDate', function () {
		var shareId = $('#linkCheckbox').data('id');

		$(this).tooltip('hide');
		$(this).removeClass('error');

		$.ajax({
			url: OC.linkToOCS('apps/files_sharing/api/v1', 2) + 'shares/' + shareId +
			'?format=json',
			type: 'PUT',
			data: {
				expireDate: $(this).val()
			}
		}).done(function () {
			if (oc_appconfig.core.defaultExpireDateEnforced === 'no') {
				$('#defaultExpireMessage').slideUp(OC.menuSpeed);
			}
		}).fail(function (xhr) {
			var result = xhr.responseJSON;
			var expirationDateField = $('#dropdown #expirationDate');
			if (result && !result.ocs.meta.message) {
				expirationDateField.attr('original-title',
					t('gallery', 'Error setting expiration date'));
			} else {
				expirationDateField.attr('original-title', result.ocs.meta.message);
			}
			expirationDateField.tooltip({placement: 'top'});
			expirationDateField.tooltip('show');
			expirationDateField.addClass('error');
		});
	});


	$(document).on('submit', '#dropdown #emailPrivateLink', function (event) {
		event.preventDefault();
		var link = $('#linkText').val();
		var itemType = $('#dropdown').data('item-type');
		var itemSource = $('#dropdown').data('item-source');
		var fileName = $('.last').children()[0].innerText;
		var email = $('#email').val();
		var expirationDate = '';
		if ($('#expirationCheckbox').is(':checked') === true) {
			expirationDate = $("#expirationDate").val();
		}
		if (email != '') {
			$('#email').prop('disabled', true);
			$('#email').val(t('gallery', 'Sending…'));
			$('#emailButton').prop('disabled', true);

			$.post(OC.filePath('core', 'ajax', 'share.php'), {
					action: 'email',
					toaddress: email,
					link: link,
					file: fileName,
					itemType: itemType,
					itemSource: itemSource,
					expiration: expirationDate
				},
				function (result) {
					$('#email').prop('disabled', false);
					$('#emailButton').prop('disabled', false);
					if (result && result.status == 'success') {
						$('#email').css('font-weight', 'bold').val(t('gallery', 'Email sent'));
						setTimeout(function () {
							$('#email').css('font-weight', 'normal').val('');
						}, 2000);
					} else {
						OC.dialogs.alert(result.data.message, t('gallery', 'Error while sharing'));
					}
				});
		}
	});

	$(document).on('click', '#dropdown input[name=mailNotification]', function () {
		var $li = $(this).closest('li');
		var itemType = $('#dropdown').data('item-type');
		var itemSource = $('a.share').data('item-source');
		var action = '';
		if (this.checked) {
			action = 'informRecipients';
		} else {
			action = 'informRecipientsDisabled';
		}
		var shareType = $li.data('share-type');
		var shareWith = $li.attr('data-share-with');
		$.post(OC.filePath('core', 'ajax', 'share.php'), {
			action: action,
			recipient: shareWith,
			shareType: shareType,
			itemSource: itemSource,
			itemType: itemType
		}, function (result) {
			if (result.status !== 'success') {
				OC.dialogs.alert(t('gallery', result.data.message), t('gallery', 'Warning'));
			}
		});
	});

	$(document).on('click', '#dropdown .pop-up', function(event) {
		event.preventDefault();
		event.stopPropagation();

		var url = $(event.currentTarget).data('url');
		var newWindow = $(event.currentTarget).data('window');
		$(event.currentTarget).tooltip('hide');
		if (url) {
			if (newWindow === true) {
				var width = 600;
				var height = 400;
				var left = (screen.width / 2) - (width / 2);
				var top = (screen.height / 2) - (height / 2);

				window.open(url, 'name', 'width=' + width + ', height=' + height + ', top=' + top + ', left=' + left);
			} else {
				window.location.href = url;
			}
		}
	});

	$(document).on('click', '#dropdown .icon-more', function(event) {
		event.preventDefault();
		event.stopPropagation();

		var children = event.currentTarget.parentNode.children;

		$.each(children, function (key, value) {
			if (value.classList.contains('popovermenu')) {
				$(value).toggle();
			}
		});
	});
});
