# Gallery Plus [BETA]
Media gallery for ownCloud which includes preview for all media types supported by your ownCloud installation.

Provides a dedicated view of all images in a grid, adds image viewing capabilities to the files app and adds a gallery view to public links.

**Note**: You need to have shell access to your ownCloud installation in order to be able to fully benefit from this version for ownCloud 7

![Screenshot](http://i.imgur.com/fxIai8t.jpg)
## Featuring
* Support for large selection of media type (depending on ownCloud setup)
* Large, zoomable previews
* Native SVG support
* Image download straight from the slideshow or the gallery

Checkout the [full changelog](CHANGELOG.md) for more.

## Browser compatibility
* Firefox, Chrome, IE 10+, Opera, Safari
* iOS, Opera Mobile

## Installation
Place this app in your apps folder or get the stable7 branch via the shell

```
$ git clone -b stable7 https://github.com/interfasys/galleryplus.git`
```

Now you can activate it in the apps menu. It's called Gallery+

While in beta, you need to install the smarter-logger.patch. You'll find it in the patches folder

```
$ patch -p1 < apps/galleryplus/patches/bitmap_preview.patch
```

And finally, in order to be able to visualise previews of more media types, follow the instructions below

## Supporting more media types
First, make sure you have installed ImageMagick and its PECL extension
Then, patch your ownCloud installation

```
$ patch -p1 < apps/galleryplus/patches/bitmap_preview.patch
```

Next add a few new entries to your configuration file

```
$ nano config/config.php
```

And add the following

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

That's it. you should be able to see more media types in your slideshows and galleries

## List of patches
1. smarter-logger.patch - Logger patch to be able to easily have access to objects, arrays, etc.
2. bitmap_preview.patch - Adds support for Photoshop, Illustrator, TIFF, Postscript

