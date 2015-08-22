# Testing the Gallery+ app

Read more about automated testing in [the wiki](https://github.com/owncloud/gallery/wiki/Behavioural%2C-functional-and-unit-testing-suite/)

## Requirements

* `core` cloned via git
* `core` submodule initialised
* `core` has to be installed
* **Disable gallery** `sudo -u<myuser> ./occ app:disable gallery`
* composer installed (see below)

## PHP

This repository is using [Codeception](http://codeception.com/) which makes it possible to use a single tool for behavioural, api, integration and unit testing.
If you're a PHPUnit veteran, don't fret! You can write unit and integration tests using the syntax you love and they will be executed just fine.

### Install Codeception

There are several methods to install Codeception, depending on the test suites you want to run.
If using the phar, you'll be limited to unit and integration testing because it doesn't come with an integrated web server on which api and acceptance tests can be run.


#### Install the phar

Go to the app's root directory and type:

`# wget http://codeception.com/codecept.phar .`

That's it. You can check that it's properly "installed" by typing:

`# php codecept.phar -- version`

#### Install via Composer

If you intend on adding and running more than the unit tests, then it's best to install all the required packages via composer.
The first step is to [install composer](https://getcomposer.org/doc/00-intro.md)

This usually works well:

`# curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer`

Now you can install all the packages which we need to test the app:

`# php -dmemory_limit=1G /usr/local/bin/composer install --prefer-source -o`

Test your installation using the following command:

`# php vendor/bin/codecept -- version`

*Note: The binary is located in a different location than if you're using the phar*

And remember to not push your `vendor` folder if submitting pull requests.

Refer to the Codeception [quick guide](http://codeception.com/quickstart) if you need to customise your installation.

#### Webdriver

In order to be able to test the Javascript in acceptance tests, we need to install one more component: PhantomJS. Use your distribution installer to get it or download a binary for Windows or Mac from the [official website](http://phantomjs.org/download.html).

*Note: It's also required for Javascript tests in `core`, so this is not specific to this repository.*

### Running tests

#### Unit

Let's create a unit test using the PHPUnit syntax:

`# php vendor/bin/codecept g:phpunit unit service/MyServiceTest`

*Note: g stands for generate*

You can now add your tests with your favourite editor The file is located in `tests/unit/service/MyServiceTest.php`

To run it, simply do:

`# php vendor/bin/codecept run tests/unit/service/MyServiceTest.php`

If you want to run all the unit tests, it's:

`# php vendor/bin/codecept run unit`

#### Integration

You can use the same commands to generate your files, but you need to extend `GalleryIntegrationTest` in order to have access to the variables which are set at initialisation time. Things like the userId, shared folder, token.

`# php vendor/bin/codecept g:phpunit integration service/MyServiceTest`

You can then run all tests using:

`# php vendor/bin/codecept run integration`

#### API and acceptance

I invite you to read the official documentation in order to understand the BDD style syntax used by Codeception, but don't worry, it's easy to grasp

```
<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('sign in');
$I->amOnPage('/login');
$I->fillField('username', 'davert');
$I->fillField('password', 'qwerty');
$I->click('LOGIN');
$I->see('Welcome, Davert!');
?>
```

* [Testing WebServices](http://codeception.com/docs/10-WebServices)
* [Acceptance Testing](http://codeception.com/docs/03-AcceptanceTests)

Before running acceptance tests, you need to start PhantomJS manually

`# phantomjs --webdriver=4444 &`

then you simply do:

`# php vendor/bin/codecept run acceptance`

Don't forget to kill the phantomjs when you're done.

*Note: A web server is also automatically launched on port 8000 for the duration of the tests so that you can test the behaviour of the API and the app.*

### Notes

If you want to get more details about what is going on during your tests, simply add `-vvv` or `--debug`
