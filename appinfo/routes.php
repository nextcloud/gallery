<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\Gallery\AppInfo;

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
			'name'     => 'page#public_index',
			'url'      => '/s/{token}/{filename}',
			'verb'     => 'GET',
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
			'name' => 'files#get_list',
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
			'name' => 'files_public#get_lists',
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
		/**
		 * API
		 */
		[
			'name' => 'files_api#preflighted_cors', // Valid for all API end points
			'url'  => '/api/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		[
			'name' => 'files_api#get_list',
			'url'  => '/api/files/list',
			'verb' => 'GET'
		],
		[
			'name' => 'files_api#download',
			'url'  => '/api/files/download/{fileId}',
			'verb' => 'GET'
		],
		[
			'name' => 'preview_api#get_thumbnails',
			'url'  => '/api/preview/thumbnails',
			'verb' => 'GET'
		],
		[
			'name' => 'preview_api#get_preview',
			'url'  => '/api/preview/{fileId}/{width}/{height}',
			'verb' => 'GET'
		],
	]
];
