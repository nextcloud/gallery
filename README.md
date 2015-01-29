# Gallery+ [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/interfasys/galleryplus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/interfasys/galleryplus/?branch=master) [![Code Climate](https://codeclimate.com/github/interfasys/galleryplus/badges/gpa.svg)](https://codeclimate.com/github/interfasys/galleryplus) [![Build Status](https://travis-ci.org/interfasys/galleryplus.svg?branch=master)](https://travis-ci.org/interfasys/galleryplus)
Media gallery for ownCloud which includes preview for all media types supported by your ownCloud installation.

Provides a dedicated view of all images in a grid, adds image viewing capabilities to the files app and adds a gallery view to public links.

![Screenshot](http://i.imgur.com/fxIai8t.jpg)
## Featuring
* Support for large selection of media type (depending on ownCloud setup)
* Large, zoomable previews
* Native SVG support
* Image download straight from the slideshow or the gallery
* Seamlessly jump between the gallery and the files app

Checkout the [full changelog](CHANGELOG.md) for more.

### Browser compatibility
* Desktop: Firefox, Chrome, IE 10+, Opera, Safari
* Mobile: Safari, Chrome, BlackBerry 10, Firefox, Opera

### Server requirements
* **PHP 5.4+**
* Recommended: a recent version ImageMagick

## Preparation
Here is a list of steps you might want to take before using the app

### Supporting more media types
First, make sure you have installed ImageMagick and its PECL extension.
Next add a few new entries to your configuration file.

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

If you want support for Raw picture files, you'll need to patch your installation of ownCloud 8.0
```
$ patch -p1 -l < apps/galleryplus/patches/raw-preview.pull.13652.patch
```

and you'll need to add `OC\\Preview\\Raw` to the configuration file

Look at the sample configuration in your config folder if you need more information about how the config file works.
That's it. you should be able to see more media types in your slideshows and galleries as soon as you've installed the app.

### Improving performance
Some of ownCloud's internal operations make the gallery app very slow
* Looking for files which can be shown
* Generating a preview only when the user needs one and each time a large preview is needed

The searching part hasn't been addressed yet, but things are in motion to fix preview caching for ownCloud 8.1. In the mean time, you can patch your ownCloud 7 to benefit from these improvements.

```
$ patch -p1 -l < apps/galleryplus/patches/max-preview.pull.13674.patch
$ patch -p1 -l < apps/galleryplus/patches/bitmap-max-preview.pull.13635.patch
```

You'll only benefit from the speed improvement the 2nd time you ask for a preview because things will be set up during the first call.
The next step will be to be able to generate these previews by clicking on a button per example, so that things are ready when visiting the gallery app.


## Installation
Download and unpack this app into your apps folder or get it straight from GitHub via the shell.
**It's important to make sure that the folder is called galleryplus.**

```
$ git clone -b stable8 https://github.com/interfasys/galleryplus.git
```

Now you can activate it in the apps menu. It's called Gallery+

## List of patches
1. max-preview.pull.13674.patch
2. bitmap-max-preview.pull.13635.patch
3. raw-preview.pull.13652.patch
