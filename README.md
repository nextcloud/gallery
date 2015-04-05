# Gallery [![Build Status](https://travis-ci.org/owncloud/galleryplus.svg?branch=dev)](https://travis-ci.org/owncloud/galleryplus)
Media gallery for ownCloud which includes previews for all media types supported by your ownCloud installation.

Provides a dedicated view of all images in a grid, adds image viewing capabilities to the files app and adds a gallery view to public links.

![Screenshot](https://oc8demo.interfacloud.com/index.php/s/pvetv4RaIbFhDRb/download)
## Featuring
* Support for large selection of media types (depending on ownCloud setup)
* Large, zoomable previews
* Sort images by name or date
* Per album description and copyright statement
* A la carte features
* Image download straight from the slideshow or the gallery
* Seamlessly jump between the gallery and the files view
* Ignore folders containing a ".nomedia" file
* Native SVG support
* Mobile support

Checkout the [full changelog](CHANGELOG.md) for more.

## Maintainers

### Current
* [Olivier Paroz (@oparoz)](https://github.com/oparoz)

### Alumni
* [Robin Appelman (@icewind1991)](https://github.com/icewind1991)
* [Jan-Christoph Borchardt](https://github.com/jancborchardt) (Design)
* All the people who have [provided patches](https://github.com/owncloud/gallery/pulls?q=is%3Apr+is%3Aclosed) over the years

## Requirements

### Browser compatibility
This list is based on the current knowledge of the maintainers and the help they can get.
It will evolve if and when people provide patches to fix all known current issues

#### Fully supported
* Desktop: Firefox, Chrome
* Mobile: Safari, Chrome on Android 5+ and iOS 8.x, BlackBerry 10, Firefox

#### Partially supported
May not look as nice, but should work

* Desktop: Internet Explorer 11+
* Mobile: Opera, Chrome on Android 4

#### Not supported
* Desktop: Internet Explorer prior to 11, Safari, Opera
* Mobile: Windows Phone

### Server requirements

#### Required
* ownCloud >= 8.1
* [See ownCloud's requirements](https://doc.owncloud.org/server/8.1/admin_manual/installation/source_installation.html#prerequisites)

#### Recommended
* FreeBSD or Linux server
* PHP 5.5 with caching enabled
* EXIF PHP module
* A recent version ImageMagick with SVG and Raw support
* MySQL or MariaDB instead of Sqlite
* A powerful server with lots of RAM

## Supporting the development

There are many ways in which you can help make Gallery a better product

* Report bugs (see below)
* Provide patches for both [`owncloud/core`](https://github.com/owncloud/core) and the app
* Help test new features by checking out new branches on Github
* Design interface components for new features
* Develop new features. Please consult with the maintainers before starting your journey
* Fund a feature, either via [BountySource](https://www.bountysource.com/teams/interfasys/issues?tracker_ids=9328526) or by directly hiring a maintainer or anybody else who is capable of developing and maintaining it

## Bugs

### Before reporting bugs

* Read the section about server and browser requirements
* Make sure you've disabled the original Pictures app
* Read the "Known issues" section below
* Get the latest version of the app from [the releases page](https://github.com/owncloud/galleryplus/releases)
* [Check if they have already been reported](https://github.com/owncloud/galleryplus/issues)

### Known issues

#### Within deep folders

* You may stop receiving images as you run into [this issue](https://github.com/owncloud/galleryplus/issues/27)
* It may take longer to initialise the view as we're parsing every parent folder to look for configuration files

#### Configurations

* If you have write access on a share belonging to another ownCloud instance, editing the configuration file in your folder will also modify the original folder

### When reporting bugs

* Enable debug mode by putting this at the bottom of **config/config.php**

```
DEFINE('DEBUG', true);
```

* Turn on debug level debug by adding **`loglevel" => 0,`** to your **config/config.php** and reproduce the problem
* Check **data/owncloud.log**

Please provide the following details so that your problem can be fixed:

* **data/owncloud.log** (important!)
* ownCloud version
* App version
* Browser version
* PHP version

## Preparation
Here is a list of steps you might want to take before using the app

### Supporting more media types
First, make sure you have installed ImageMagick and its PECL extension.
Next add a few new entries to your **config/config.php** configuration file.

```
  'preview_max_scale_factor' => 1,
  'enabledPreviewProviders' =>
  array (
    0 => 'OC\\Preview\\Image',
    1 => 'OC\\Preview\\Illustrator',
    2 => 'OC\\Preview\\Postscript',
    3 => 'OC\\Preview\\Photoshop',
    4 => 'OC\\Preview\\TIFF',
  ),
```

Look at the sample configuration (config.sample.php) in your config folder if you need more information about how the config file works.
That's it. You should be able to see more media types in your slideshows and galleries as soon as you've installed the app.

### Improving performance

#### Assets pipelining
Make sure to enable "asset pipelining", so that all the Javascript and CSS resources can be mixed together.
This can greatly reduce the loading time of the app.

Read about it in the [Administration Manual](https://doc.owncloud.org/server/8.0/admin_manual/configuration_server/js_css_asset_management_configuration.html)

## Installation

**IMPORTANT**: Make sure you've disabled the original Pictures app

### Installing from archive

* Go to the [the releases page](https://github.com/owncloud/galleryplus/releases)
* Download the latest release/archive to your server's **owncloud/apps/** directory
* Unpack the app
* **IMPORTANT**: Rename it to galleryplus

### Installing from Git

In your terminal go into the **owncloud/apps/** directory and then run the following command:
```
$ git clone -b dev https://github.com/owncloud/galleryplus.git
```

Now you can activate it in the apps menu. It's called Gallery

To update the app go inside you **owncloud/apps/galleryplus/** directory and type:
```
$ git pull --rebase origin dev
```

## List of patches
None so far