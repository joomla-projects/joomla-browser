# Joomla Browser (Codeception Module)
[![Latest Stable Version](https://poser.pugx.org/joomla-projects/joomla-browser/v/stable)](https://packagist.org/packages/joomla-projects/joomla-browser) [![Total Downloads](https://poser.pugx.org/joomla-projects/joomla-browser/downloads)](https://packagist.org/packages/joomla-projects/joomla-browser) [![Latest Unstable Version](https://poser.pugx.org/joomla-projects/joomla-browser/v/unstable)](https://packagist.org/packages/joomla-projects/joomla-browser) [![License](https://poser.pugx.org/joomla-projects/joomla-browser/license)](https://packagist.org/packages/joomla-projects/joomla-browser)

## Table of Contents

* [The JoomlaBrowser](#the-joomla-browser)
* [Using the JoomlaBrowser to test Joomla Sites](#using-instructions)
  * [Download](#download)
  * [Loading the Module in Codeception](loading-the-module-in-codeception)

## The Joomla Browser
Joomla Browser is a Codeception.com Module. It allows to build `system tests` for a Joomla site much faster providing a set of predefined tasks. 

In between the available functions you can find:
* install joomla
* do administrator login
* enable plugin
* select option in chosen

Check the full list at: [the documentation](https://github.com/joomla-projects/joomla-browser/blob/develop/docs/api.php)


## Joomla Browser in action
If you want to see a working example of JoomlaBrowser check weblinks tests: https://github.com/joomla-extensions/weblinks#tests

## Using Instructions
Update Composer.json file in your project, and download

### Download

```
composer require joomla-projects/joomla-browser:dev-develop
```

### Loading the Module in Codeception

Finally make the following changes in Acceptance.suite.yml to add JoomlaBrowser as a Module. 

Your original `acceptance.suite.yml`probably looks like:

```yaml
modules:
    enabled:
        - WebDriver
        - AcceptanceHelper
    config:
        WebDriver:
            url: 'http://localhost/joomla-cms3/'     # the url that points to the joomla cms
            browser: 'firefox'
            window_size: 1024x768
            capabilities:
              unexpectedAlertBehaviour: 'accept'
        AcceptanceHelper:
            ...
```

You should remove the WebDriver module and replace it with the JoomlaBrowser module:

```yaml
    config:
        JoomlaBrowser:
            url: 'http://localhost/joomla-cms/'     # the url that points to the joomla installation at /tests/system/joomla-cms
            browser: 'firefox'
            window_size: 1024x768
            capabilities:
              unexpectedAlertBehaviour: 'accept'
            username: 'admin'
            password: 'admin'
            database host: 'localhost'             # place where the Application is Hosted #server Address
            database user: 'root'                  # MySQL Server user ID, usually root
            database password: '1234'                  # MySQL Server password, usually empty or root
            database name: 'dbname'            # DB Name, at the Server
            database type: 'mysqli'                # type in lowercase one of the options: MySQL\MySQLi\PDO
            database prefix: 'jos_'                # DB Prefix for tables
            install sample data: 'Yes'              # Do you want to Download the Sample Data Along with Joomla Installation, then keep it Yes
            sample data: 'Default English (GB) Sample Data'    # Default Sample Data
            admin email: 'admin@mydomain.com'      # email Id of the Admin
            language: 'English (United Kingdom)'   # Language in which you want the Application to be Installed
            joomla folder: '/home/.../path to Joomla Folder' # Path to Joomla installation where we execute the tests
        AcceptanceHelper:
            ...
```

# Releasing
## Generate the docs
type: `vendor/bin/robo generate:api-documentation` in your command line tool. This will generate 