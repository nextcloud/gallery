# Album configuration
It's possible to configure an album via a simple text file, using the [Yaml](https://en.wikipedia.org/wiki/YAML) markup.

## Features

The following features are currently implemented:

* Adding a link to a file containing a description
* Typing a simple copyright statement directly in the configuration file
* Adding a link to a file containing a copyright statement
* Defining a sort type and order 
* Defining if sub-albums will inherit the configuration

## Configuration

### File format
UTF-8, without BOM. A file created from within the web GUI works.
From 8.1, it will be possible to edit the files from within the GUI.

### Structure
It's advised to add a comment in the file, so that people stumbling upon that file know what it's for.
Comments start with #.

Spacing is created using 2 spaces. **Do not use tabs**
```
---
# Gallery+ configuration file
information:
  description: readme.md
  copyright: Copyright 2003-2015 interfaSys sàrl, Switzerland
  copyright_link: copyright.md
  inherit: yes
sorting:
  type: date
  order: des
  inherit: yes
```

### Supported variables

* `description`: a markdown file. See [this page](http://www.markitdown.net/markdown) for the syntax
* `copyright`: a markdown formatted string. You can add links to external resources if you need to
* `copyright_link`: any file (in the album itself)
* `sorting`: `date` or `name`. `date` only works for files
* `sort_order`: `asc` or `des`
* `inherit`: set it to yes if you want sub-folders to inherit this part of the configuration

### Possible future extensions

* Different sorting parameters for albums

## Sorting
* in case only the sort `type` variable has been set, the default sort order will be used
* in case only the sort `order` variable has been found, the sort configuration will be ignored and the script will keep looking for a valid configuration in upper folders
