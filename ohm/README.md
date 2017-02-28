# Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

# Prerequisities

- Apache + SSL
- PHP 5.6
- MySQL

Helpful links:

- [Developer setup at Lumen Learning](https://github.com/lumenlearning/dev-setup)
- [MacOS Sierra - Apache Setup: MySQL, APC & More...](https://getgrav.org/blog/macos-sierra-apache-mysql-vhost-apc)
- [MacOS Sierra - Apache Setup: Multiple PHP Versions](https://getgrav.org/blog/macos-sierra-apache-multiple-php-versions)

# Installing MyOpenMath

1. Create a database in phpMyAdmin   
1. Clone this repo to your web server
  * `git clone https://github.com/lumenlearning/myopenmath.git`
1. Copy `config/local.php.example` to `config/local.php`
1. Edit `config/local.php` and enter credentials for the database you created
1. Go to localhost `http://localhost/myopenmath/dbsetup.php`
1. Enter in the info for the Database Settings
1. Click the `Set up database` button at the end of the page
  * This will initialize the database.
1. You should then see the list of tables that were created
  * Scroll to the bottom of the page and click the `Go to IMathAS login page` link.
1. Enter the username and password which should be `root`

If something breaks, look at the Debugging Steps.

# Manual deployment

When using Amazon's EB CLI (`eb deploy`) from your laptop, the latest commit is used for deployments.

To deploy from the current working directory, an `.ebignore` file should be created in the root of
the repository with the following contents:

    # Dev
    /.git
    /.idea
    /.elasticbeanstalk
    .DS_Store

# Configuration

LuMOM uses environment-specific configuration files.

- This is managed by setting the `CONFIG_ENV` environment variable.
- All configuration files are located in `config.php` and the `config/` directory.

__If the `CONFIG_ENV` environment variable is not set, it will default to the value of `development`.__

# Themes (environment-specific)

LuMOM uses environment-specific styling for some pages and components.

- Theme files are located in the the following directories:
  - `lumen/`
  - `ohm/`
  - `themes/`
  - `wamap/`

## Local development at Lumen Learning

To view domain-specific login pages (lumenlearning.com, wamap.org), you will need to edit your `config/local.php`
and uncomment the appropriate `require('*.php')` line at the end of the file.

# Debugging Steps

These are tips for when anything breaks or doesn't work.

* Open localhost in in private/Incognito
* Clear the cookies and/or opening the site using an incognito window (which clears the cookies)
* Turn on Output Buffering (Mamp php.ini)
* Delete Cookies
* Stop and restart Mamp
* Index.php (remove empty lines before the php tag)
* Ensure you have read/write permissions to the web/assets directory. This can be done by going to your command line program (such as Terminal on Mac), navigating to the root project folder, and $ sudo chmod 777 web/assets
* Change line 428 in Appcontroller.php from
    return $this->redirect('dashboard'); to
    return array('status'=>true, 'message'=>"");
* In terminal, navigate to /myopenmath/course/files/ and enter 'chmod 1777'

# Psysh Integration

- Follow psysh installation instructions (http://psysh.org/#install)
- In the console, type `psysh` to start a new shell prompt
- Then copy and paste the code below in at the prompt

        require(__DIR__ . '/vendor/autoload.php');
        require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

        $config = require(__DIR__ . '/config/web.php');
        error_reporting(E_ALL ^ E_NOTICE);
        $app = new yii\web\Application($config);
