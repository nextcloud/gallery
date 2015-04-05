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
  description: This is an **album description** which is only shown if there is no `description_link`
  description_link: readme.md
  copyright: Copyright 2003-2015 [interfaSys sàrl](http://www.interfasys.ch), Switzerland
  copyright_link: copyright.md
  inherit: yes
sorting:
  type: date
  order: des
  inherit: yes
```

### Supported variables

* `description`: a markdown formatted string which will be displayed in the info box
* `description_link`: a markdown file located within the album and which will be parsed and displayed in the info box instead of the description
* `copyright`: a markdown formatted string. You can add links to external resources if you need to.
* `copyright_link`: any file (i.e. copyright.html), in the album itself, which will be downloaded when the user clicks on the link
* `sorting`: `date` or `name`. `date` only works for files
* `sort_order`: `asc` or `des`
* `inherit`: set it to yes if you want sub-folders to inherit this part of the configuration

See [this page](http://www.markitdown.net/markdown) for the markdown syntax

### Possible future extensions

* Different sorting parameters for albums
* Enabling experimental features

## Sorting
* in case only the sort `type` variable has been set, the default sort order will be used
* in case only the sort `order` variable has been found, the sort configuration will be ignored and the script will keep looking for a valid configuration in upper folders

## Tips
* If you share a folder publicly, don't forget to add all the files you link to inside the shared folder as the user won't have access to files stored in the parent folder
* Since people can download a whole folder as an archive, it's usually best to include all files within a shared folder as opposed to adding text directly in the configuration file
