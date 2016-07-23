## Bugs 

### In short

* Make sure you have properly installed and configured both your cloud server and Gallery
* Make sure your issue was not [reported](https://github.com/owncloud/gallery/issues) before and that it isn't a [known issue](https://github.com/owncloud/gallery/wiki/Known-issues)
* Use the **issue template** shown to you when opening a new issue. The 1st part is to report bugs and the 2nd one to open a feature request

### Detailed explanation

* Read the section in the wiki about [server and browser requirements](https://github.com/owncloud/gallery/wiki/Requirements)
* Read the [known issue](https://github.com/owncloud/gallery/wiki/Known-issues) section in the wiki
* Get the latest version of the app from [the releases page](https://github.com/owncloud/gallery/releases)
* [Check if they have already been reported](https://github.com/owncloud/gallery/issues)
* Please search the existing issues first, it is likely that your issue was already reported or even fixed
  - Click on "issues" in the column on the right and type any word in the top search/command bar
  - You can also filter by appending e. g. "state:open" or "state:closed" to the search string
  - More info on [search syntax within GitHub](https://help.github.com/articles/searching-issues)
* Report the issue using the **issue template** shown to you when opening a new issue. The 1st part is to report bugs and the 2nd one to open a feature request

Help us maximize the effort we can spend fixing issues and adding new features, by not reporting duplicate issues.

### When reporting bugs

* Enable debug mode by adding the following line to your **config/config.php** file

```
'debug' => true,
```

* Change the log level in order to be able to catch debugging messages, by adding **`loglevel" => 0,`** to your **config/config.php** and reproduce the problem
* Check **data/owncloud.log** for any clues

Please provide the following details so that your problem can be fixed:

* **Cloud server log** (data/owncloud.log)
* **Browser log** (Hit F12 to gain access)
* Cloud server version
* App version
* Browser version
* PHP version

## Contributing to Source Code

Thanks for wanting to contribute source code to Gallery. That's great!

Before we are able to merge your code into the Gallery app, you need to agree to release your code under the AGPL license.

* Please familiarise yourself with the App development process for your cloud server in order to understand how the AppFramework works and don't hesitate to contact a maintainer in order to obtain more information or tips
* It's required to add PHPUnit tests to your pull requests in order to make sure your patches work as intended
* Don't use server features which are still in development unless you have to. This repository tracks the master branch, but the goal is to make the app work with older versions of the server as well.
* Use `[<future_version>]` in the commit comment of all commits which only work with a future version of the server. Something like: `[11.0] Adding this cool new feature`
 
We're looking forward to your contributions!

## Translations
Please submit translations via Transifex.

* [ownCloud](https://www.transifex.com/projects/p/owncloud/)
* [Nextcloud](https://www.transifex.com/projects/p/nextcloud/)