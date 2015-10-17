# Gallery+
[![Build Status](https://travis-ci.org/interfasys/galleryplus.svg?branch=master)](https://travis-ci.org/interfasys/galleryplus)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/interfasys/galleryplus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/interfasys/galleryplus/?branch=master)
[![Codacy Badge](https://www.codacy.com/project/badge/02f02de5292e4f7393cd7e5697227a5a)](https://www.codacy.com/app/interfaSys/galleryplus)
[![Code Climate](https://codeclimate.com/github/interfasys/galleryplus/badges/gpa.svg)](https://codeclimate.com/github/interfasys/galleryplus)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/76c41e1a-ed83-46e0-bbad-af925f72e8c9/mini.png)](https://insight.sensiolabs.com/projects/76c41e1a-ed83-46e0-bbad-af925f72e8c9)

[![Code Coverage](https://scrutinizer-ci.com/g/interfasys/galleryplus/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/interfasys/galleryplus/?branch=master)

A media gallery for ownCloud which includes previews for all media types supported by your ownCloud installation.

Provides a dedicated view of all images in a grid, adds image viewing capabilities to the files app and adds a gallery view to public links.

**This branch brings new features and bug fixes from the master branch of `owncloud/gallery` to ownCloud 8.2.x**

![Screenshot](https://oc8demo.interfacloud.com/index.php/apps/galleryplus/s/GYFcC2YvieDhrnu/51282.jpg)
## Featuring
* Support for large selection of media types (depending on ownCloud setup)
* Large, zoomable previews which can be shown in fullscreen mode
* Sort images by name or date added
* Per album description and copyright statement
* A la carte features (external shares, native svg, etc.)
* Image download straight from the slideshow or the gallery
* Seamlessly jump between the gallery and the files view
* Ignore folders containing a ".nomedia" file
* Native SVG support (disabled by default)
* Mobile support

Checkout the [full changelog](CHANGELOG.md) for more.

## Maintainers

### Current
* [Olivier Paroz](https://github.com/oparoz)
* [Jan-Christoph Borchardt](https://github.com/jancborchardt) (Design)

### Alumni
* [Robin Appelman](https://github.com/icewind1991)

## Contributors

* All the people who have provided patches to [Gallery+](https://github.com/interfasys/galleryplus/pulls?q=is%3Apr+is%3Aclosed), [Gallery](https://github.com/owncloud/gallery/pulls?q=is%3Apr+is%3Aclosed) and [Pictures](https://github.com/owncloud/gallery-old/pulls?q=is%3Apr+is%3Aclosed) over the years


## Requirements

See this [wiki article](https://github.com/interfasys/galleryplus/wiki/Requirements) about the requirements for Gallery.

## Supporting the development

There are many ways in which you can help make Gallery a better product

* Report bugs (see below)
* Provide patches for both [`owncloud/core`](https://github.com/owncloud/core) and the app
* Help test new features by checking out new branches on Github
* Design interface components for new features
* Develop new features. Please consult with the maintainers before starting your journey
* Fund a feature, either via [BountySource](https://www.bountysource.com/teams/interfasys/issues?tracker_ids=9328526) or by directly hiring a maintainer or anybody else who is capable of developing and maintaining it

## Bug reporting and contributing

Everything you need to know about bug reporting and contributing [is located here](https://github.com/interfasys/galleryplus/blob/master/CONTRIBUTING.md).

## Preparation
Here is a list of steps you might want to take before using the app

### Supporting more media types
First, make sure you have installed ImageMagick and its PECL extension.
Next add a few new entries to your **config/config.php** configuration file.

```
  'preview_max_scale_factor' => 1,
  'enabledPreviewProviders' =>
  array (
    0 => 'OC\\Preview\\PNG',
    1 => 'OC\\Preview\\JPEG',
    2 => 'OC\\Preview\\GIF',
    11 => 'OC\\Preview\\Illustrator',
    12 => 'OC\\Preview\\Postscript',
    13 => 'OC\\Preview\\Photoshop',
    14 => 'OC\\Preview\\TIFF'
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

**IMPORTANT**: Make sure you've disabled the original Gallery app

### Installing from the app store

* As an admin, select "Apps" in the menu
* Go to the "disabled apps" section
* Enable Gallery+

### Installing from archive

* Go to the [the releases page](https://github.com/interfasys/galleryplus/releases)
* Download the latest release/archive to your server's **owncloud/apps/** directory
* Unpack the app
* **IMPORTANT**: Rename it to galleryplus

### Installing from Git

In your terminal go into the **owncloud/apps/** directory and then run the following command:
```
$ git clone https://github.com/interfasys/galleryplus.git
```

Now you can activate it in the apps menu. It's called Gallery+

To update the app go inside you **owncloud/apps/galleryplus/** directory and type:
```
$ git pull --rebase
```

## List of patches
None so far
