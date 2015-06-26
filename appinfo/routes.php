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
		// Landing page for public galleries. If a filename is given the file is downloaded
		[
			'name' => 'page#public_index',
			'url'  => '/s/{token}/{filename}',
			'verb' => 'GET',
			'defaults' => ['filename' => null]
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
		[
			'name' => 'page#slideshow',
			'url'  => '/slideshow',
			'verb' => 'GET'
		],
		/**
		 * Services
		 */
		// Gallery config, as well as supported media types
		[
			'name' => 'config#get_config',
			'url'  => '/config',
			'verb' => 'GET'
		],
		// All the images of which a preview can be generated
		[
			'name' => 'files#get_files',
			'url'  => '/files',
			'verb' => 'GET'
		],
		// Batch creation of thumbnails
		[
			'name' => 'preview#get_thumbnails',
			'url'  => '/thumbnails',
			'verb' => 'GET'
		],
		// Large preview of a file
		[
			'name' => 'preview#get_preview',
			'url'  => '/preview/{fileId}',
			'verb' => 'GET'
		],
		/**
		 * Public services
		 */
		[
			'name' => 'public_config#get_config',
			'url'  => '/config.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_preview#get_media_types',
			'url'  => '/mediatypes.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_files#get_files',
			'url'  => '/files.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_preview#get_thumbnails',
			'url'  => '/thumbnails.public',
			'verb' => 'GET'
		],
		[
			'name' => 'public_preview#get_preview',
			'url'  => '/preview.public/{fileId}',
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
