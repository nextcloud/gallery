# Album configuration
It's possible to configure a Gallery album via a simple text file, using the [Yaml](https://en.wikipedia.org/wiki/YAML) markup language.

## Features

The following features are currently implemented:

* Adding a link to a file containing a description
* Typing a simple copyright statement directly in the configuration file
* Adding a link to a file containing a copyright statement
* Defining a sort type and order 
* Defining if sub-albums will inherit the configuration
* Enabling a specific feature

## Configuration

### File format
UTF-8, without BOM. A file created from within the web GUI works.

From 8.1, it will be possible to edit the files from within the GUI.

### Structure
It's advised to add a comment in the file, so that people stumbling upon that file know what it's for.
Comments start with #.

Spacing is created using 2 spaces. **Do not use tabs**

Take a look at the format documentation if you're getting error messages:
http://symfony.com/doc/current/components/yaml/yaml_format.html

```
---
# Gallery configuration file
information:
  description: This is an **album description** which is only shown if there is no `description_link`
  description_link: readme.md
  copyright: Copyright 2003-2015 [interfaSys sàrl](http://www.interfasys.ch), Switzerland
  copyright_link: copyright.md
  inherit: yes
sorting:
  type: date
  order: des
  inherit: yes
features:
  external_shares: yes
```

### Supported variables

* `description`: a markdown formatted string which will be displayed in the info box. It can spread over multiple lines using the Yaml markers
* `description_link`: a markdown file located within the album and which will be parsed and displayed in the info box instead of the description
* `copyright`: a markdown formatted string. You can add links to external resources if you need to.
* `copyright_link`: any file (i.e. copyright.html), in the album itself, which will be downloaded when the user clicks on the link
* `sorting`: `date` or `name`. `date` only works for files
* `sort_order`: `asc` or `des`
* `inherit`: set it to yes if you want sub-folders to inherit this part of the configuration
* `external_shares`: set it to yes in your root configuration file if you want to be able to load images coming from external clouds

See [this page](http://www.markitdown.net/markdown) for the markdown syntax

_Note: Do not add links to your `copyright` string if you intend on adding a `copyright link`_
_Warning: External shares are 20-50 times slower than local shares. Be prepared to wait a long time before being able to see all the images contained in a shared album_

### Possible future extensions

* Different sorting parameters for albums

## Sorting
* in case only the sort `type` variable has been set, the default sort order will be used
* in case only the sort `order` variable has been found, the sort configuration will be ignored and the script will keep looking for a valid configuration in upper folders

## Tips
* If you share a folder publicly, don't forget to add all the files you link to inside the shared folder as the user won't have access to files stored in the parent folder
* Since people can download a whole folder as an archive, it's usually best to include all files within a shared folder as opposed to adding text directly in the configuration file

## Examples

### Sorting only

Applies to the current folder only

```
---
# Gallery configuration file
sorting:
  type: date
  order: asc
```

### Short description and link to copyright document

Applies to the current folder and all of its sub-folders

This also shows you the syntax you can use to spread a description over multiple lines
```
---
# Gallery configuration file
information:
  description: | # La Maison Bleue, Winter '16
    This is our Winter 2016 collection shot in **Kyoto**
    Visit out [website](http://www.secretdesigner.ninja) for more information
  copyright: Copyright 2015 La Maison Bleue, France
  copyright_link: copyright_2015_lmb.html
  inherit: yes
```

### Load images from external clouds

**Features can only be defined in the root folder**

You can add standard configuration items to the same configuration file

```
---
# Gallery configuration file
features:
  external_shares: yes
...
```

