## Bugs 

### In short

* Make sure you have properly installed and configured both ownCloud and Gallery
* Make sure your issue was not [reported](https://github.com/owncloud/gallery/issues) before and that it isn't a [known issue](https://github.com/owncloud/gallery/wiki/Known-issues)
* Use the [**issue template**](https://raw.githubusercontent.com/owncloud/gallery/master/issue_template.md) when reporting issues.

### Detailed explanation

* Read the section in the wiki about [server and browser requirements](https://github.com/owncloud/gallery/wiki/Requirements)
* Read the [known issue](https://github.com/owncloud/gallery/wiki/Known-issues) section in the wiki
* Get the latest version of the app from [the releases page](https://github.com/owncloud/gallery/releases)
* [Check if they have already been reported](https://github.com/owncloud/gallery/issues)
* Please search the existing issues first, it is likely that your issue was already reported or even fixed.
  - Click on "issues" in the column on the right and type any word in the top search/command bar.
  - You can also filter by appending e. g. "state:open" or "state:closed" to the search string.
  - More info on [search syntax within GitHub](https://help.github.com/articles/searching-issues)
* Report the issue using the [ownCloud template](https://raw.githubusercontent.com/owncloud/gallery/master/issue_template.md), it includes all the information we need to track down the issue.

Help us maximize the effort we can spend fixing issues and adding new features, by not reporting duplicate issues.

### When reporting bugs

* Enable debug mode by putting this at the bottom of **config/config.php**

```
DEFINE('DEBUG', true);
```

* Turn on debug level debug by adding **`loglevel" => 0,`** to your **config/config.php** and reproduce the problem
* Check **data/owncloud.log**

Please provide the following details so that your problem can be fixed:

* **Owncloud log** (data/owncloud.log)
* **Browser log** (Hit F12 to gain access)
* ownCloud version
* App version
* Browser version
* PHP version

## Contributing to Source Code

Thanks for wanting to contribute source code to Gallery. That's great!

Before we are able to merge your code into the Gallery app, you need to agree to release your code under the AGPL license or to have signed the ownCloud [contributor agreement](https://owncloud.org/about/contributor-agreement/)

* Please familiarise yourself with the [App development process](https://owncloud.org/dev) in order to understand how the AppFramework works and don't hesitate to contact a maintainer in order to obtain more information or tips
* It's required to add PHPUnit tests to your pull requests in order to make sure your patches work as intended
* Don't use bleeding edge `core` features unless you have to. This repository tracks ownCloud master, but the goal is to make it work with older versions of ownCloud as well.
* Use `[<future_version>]` in the commit comment of all commits which only work with a future version of owncloud. Something like: `[9.0] Adding this cool new feature`
 
We're looking forward to your contributions!

## Translations
Please submit translations via [Transifex][transifex].

[transifex]: https://www.transifex.com/projects/p/owncloud/
