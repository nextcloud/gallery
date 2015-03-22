<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\AppInfo;

/**
 * Application routes
 *
 * The name is the lowercase name of the controller without the controller
 * part, the stuff after the hash is the method.
 *
 * e.g. page#index -> PageController->index()
 */
return [
	'routes' => [
		/**
		 * Main routes
		 */
		[
			'name' => 'page#index',
			'url'  => '/',
			'verb' => 'GET'
		],
		// Landing page for public galleries
		[
			'name' => 'page#public_index',
			'url'  => '/s/{token}',
			'verb' => 'GET'
		],
		// Landing page after password entry, for public galleries
		[
			'name'    => 'page#public_index',
			'url'     => '/s/{token}',
			'verb'    => 'POST',
			'postfix' => 'post'
		],
		// An error page which can handle different error codes
		[
			'name' => 'page#error_page',
			'url'  => '/error',
			'verb' => 'GET'
		],
		// The same page, but for POST
		[
			'name'    => 'page#error_page',
			'url'     => '/error',
			'verb'    => 'POST',
			'postfix' => 'post'
		],
		/**
		 * Services
		 */
		// Supported media types. Only called by the slideshow
		[
			'name' => 'service#get_types',
			'url'  => '/mimetypes',
			'verb' => 'GET'
		],
		// All the images of which a preview can be generated
		[
			'name' => 'service#get_files',
			'url'  => '/files',
			'verb' => 'GET'
		],
		// Batch creation of thumbnails
		[
			'name' => 'service#get_thumbnails',
			'url'  => '/thumbnails',
			'verb' => 'GET'
		],
		// Large preview of a file
		[
			'name' => 'service#show_preview',
			'url'  => '/preview',
			'verb' => 'GET'
		],
		// Download the file
		[
			'name' => 'service#download_preview',
			'url'  => '/download',
			'verb' => 'GET'
		],
		/**
		 * Public services
		 */
		[
			'name' => 'public_service#get_types',
			'url'  => '/mimetypes.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_service#get_files',
			'url'  => '/files.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_service#get_thumbnails',
			'url'  => '/thumbnails.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_service#show_preview',
			'url'  => '/preview.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_service#download_preview',
			'url'  => '/download.public',
			'verb' => 'GET'
		],
		// API, for later
		/*[
			 'name' => 'api#get_types',
			 'url'  => '/api/1.0/types',
			 'verb' => 'GET'
		 ],*/
	]
];
