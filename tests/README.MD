# Testing the Gallery app

Read more about automated testing in [the wiki](https://github.com/owncloud/gallery/wiki/Behavioural%2C-functional-and-unit-testing-suite/)

## Requirements

* PHP tidy enabled
* PHP imagick extension
* `core` cloned via git
* `core` submodule initialised
* `core` has to be installed
* Gallery app enabled (`sudo -u<myuser> ./occ app:enable gallery`)
* composer installed (see below)
* vendor folder and composer.lock file removed

## PHP

This repository is using [Codeception](http://codeception.com/) which makes it possible to use a single tool for behavioural, api, integration and unit testing.
If you're a PHPUnit veteran, don't fret! You can write unit and integration tests using the syntax you love and they will be executed just fine.

### Installing Codeception

There are several methods to install Codeception, depending on the test suites you want to run.
If using the phar, you'll be limited to unit and integration testing because it doesn't come with an integrated web server for the api and acceptance tests to connect to.


#### Using the phar package

Go to Gallery's root directory and type:

`# wget http://codeception.com/codecept.phar .`

That's it. You can check that it's properly "installed" by typing:

`# php codecept.phar --version`

#### Installing via Composer

If you intend on adding and running more than the unit tests, then it's best to install all the required packages via composer.
The first step is to [install composer](https://getcomposer.org/doc/00-intro.md)

This usually works well:

As root

`# curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer`

Next you'll need to add a Github token to composer so that you can easily download as many dependencies as you need.

1. Generate a personal token in your Github profile. You don't need to associate any Github permissions to this token.

2. Type the following command: `# composer config --global github-oauth.github.com <your-token> `

As the www user

You can now install all the packages needed to test the app:

`# php -dmemory_limit=1G /usr/local/bin/composer install --prefer-source -o`

Test your installation using the following command:

`# php vendor/bin/codecept -- version`

*Note: The binary is located in a different location than if you're using the phar*

And remember to **not** push your `vendor` folder if submitting pull requests.

Refer to the Codeception [quick guide](http://codeception.com/quickstart) if you need to customise your installation.

#### Webdriver

In order to be able to test the Javascript in acceptance tests, we need to install one more component: PhantomJS. Use your distribution installer to get it or download a binary for Windows or Mac from the [official website](http://phantomjs.org/download.html).

*Note: It's also required for Javascript tests in `core`, so this is not specific to this repository.*

### Running tests

**WARNING - RUNNING TESTS WILL DELETE ALL YOUR DATA**

#### Unit

Let's create a unit test class compatible with the PHPUnit syntax:

`# php vendor/bin/codecept g:phpunit unit service/MyServiceTest`

*Note: g stands for generate*

You can now add your tests with your favourite editor The file is located in `tests/unit/service/MyServiceTest.php`

To run it, simply do:

`# php vendor/bin/codecept run tests/unit/service/MyServiceTest.php`

If you want to run all the unit tests, it's:

`# php vendor/bin/codecept run unit`

#### Integration

You can use the same commands to generate your files, but your class needs to extend `GalleryIntegrationTest` in order to have access to the variables which are set at initialisation time. Things like the userId, shared folder, token, etc.

`# php vendor/bin/codecept g:phpunit integration service/MyServiceTest`

You can then run all tests using:

`# php vendor/bin/codecept run integration`

#### API and acceptance

Those suites use a different, scenario based syntax, but don't worry, it's easy to grasp.

Files storing unique scenarios are called **Cepts**

**Generating a Cept**

`# php vendor/bin/codecept g:cept acceptance SignIn`

An example of a sign in test:

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

It's possible to group scenarios in a **Cest** which organises them in a class.

**Generating a Cest**

`# php vendor/bin/codecept g:cest acceptance PageCrud`

It's structure:

```
<?php
class PageCrudCest
{
    function _before(AcceptanceTester $I)
    {
        // will be executed at the beginning of each test
        $I->amOnPage('/');
    }

    function createPage(AcceptanceTester $I)
    {
       // todo: write test
    }

    function viewPage(AcceptanceTester $I)
    {
       // todo: write test
    }    

    function updatePage(AcceptanceTester $I)
    {
        // todo: write test
    }
    
    function deletePage(AcceptanceTester $I)
    {
       // todo: write test
    }
}
?>
```

I invite you to read the official documentation in order to understand the BDD style syntax used by Codeception and to discover all the useful commands you're going to be able to fire in order to test Gallery:

* [Testing WebServices](http://codeception.com/docs/10-WebServices)
* [Acceptance Testing](http://codeception.com/docs/03-AcceptanceTests)

Before running acceptance tests, you need to start PhantomJS manually

`# phantomjs --webdriver=4444 &`

then you simply do:

`# php vendor/bin/codecept run acceptance`

Don't forget to kill the phantomjs when you're done.

*Note: A web server is also automatically launched on port 8000 for the duration of the tests so that you can test the behaviour of the API and the app.*

### Notes

If you want to get more details about what is going on during your tests, simply add `-vvv` or `--debug` at the end of the command line

### Troubleshooting

#### Files can't be deleted during API test

If you start getting some strange errors about files which can't be deleted when running the API tests or if some the responses don't match what is expected, it could be because there is already an instance of the phpbuiltinserver running.
This can happen when a fatal error has happened during a previous run and Codeception wasn't able to shut down the server.

Get the process ID

`# ps aux | grep phpbuiltinserver`

Kill the process

`# kill -9 <process id>`

#### Accessing the built in server's logs

Look for them in the `tests/_output` folder, along with a summary of the failed tests
