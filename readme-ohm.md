# Online Homework Manager (OHM)

A [MyOpenMath](https://github.com/lumenlearning/myopenmath) (MOM) fork.

**NOTE:** Some development and changes flow in both directions between OHM and MyOpenMath.

# Code of Conduct

Lumen Learning's
[Code of Conduct](https://github.com/lumenlearning/code_of_conduct)
is one of the ways we put our values into practice.
Our commitment to the standards outlined in the Code of Conduct help us build great teams,
craft great code,
and maintain a safe,
pleasant work environment.

## User privacy

To **mask** sensitive user information from being recorded by FullStory, use
one of the appropriate CSS classes listed in:
[ohm/tracking/sensitive_info_highlight.css](ohm/tracking/sensitive_info_highlight.css)

To **exclude** sensitive user information, use the `fs-exclude` CSS class.

- Reference: [How do I protect my users' privacy in FullStory?](https://help.fullstory.com/hc/en-us/articles/360020623574)

# Getting Started

## Get OHM running

### SSL CERTS

This step is only needed the first time you are setting up locally.

1. Install `mkcert`. You can use `brew install mkcert` (homebrew) or `port install mkcert` (macports)
2. Create a directory to store your certificates.
3. In the above directory, run `mkcert -install` and `mkcert localhost 127.0.0.1 ::1`.
4. Copy the two resulting files into the project subdirectory `/docker/ssl`.
   - Name the .key.pem file `docker/ssl/lumenlearning.key`
   - Name the .pem file `docker/ssl/lumenlearning.crt`

### Running OHM locally

0. Ensure you have installed:

- Homebrew: https://brew.sh/
- Docker: https://www.docker.com/get-started)
- PHP: (run these commands in a terminal)
  1. `brew install php@7.4`
  2. `brew link php@7.4`

Note: If you have issues with Macbook M1 installation, see this [confluence page](https://lumenlearning.atlassian.net/l/cp/0DPenfEX).

1. Copy local.php.example to local.php:
   ```
   cd ohm/
   cp config/local.php.example config/local.php
   ```
1. Modify the `$dbserver` in `local.php` to 127.0.0.1
1. Run `composer install` at root directory as well as in `ohm/lumenapi` directory
1. Start Docker Desktop
1. Go into project root directory and run `docker-compose up`
1. In a browser, go to https://localhost/setupdb.php and wait for DB migrations to complete. If fails, https://localhost/upgrade.php
1. Hopefully app is up and running at localhost!

### Get Vue Running

For OHM1 development only.

1. Execute `npm run serve` from the `assess2/vue-src`
   (You might need to `nvm use stable` if this step fails)
2. See [Vue's specific README.md](assess2/vue-src/README.md) file

# Reference

## User Permissions

In the `imas_users` table, each user record has one of these values in the
`rights` column to define their role and access level within OHM.

| Value      | Meaning          |
| ---------- | ---------------- |
| 5          | Guest            |
| 10         | Student          |
| 12         | Pending          |
| 15         | Tutor/TA/Proctor |
| 20         | Teacher          |
| 40         | LimCourseCreator |
| 75         | GroupAdmin       |
| 100        | Admin            |
| 11, 76, 77 | LTI credentials  |

# Running migrations

After initial setup, this needs to be done manually any time there are
new migrations.

Go to https://localhost/upgrade.php in your web browser.

# Testing

Requirements:

- Composer with all dependencies installed.
  - `$ brew install composer` or [getcomposer.org](https://getcomposer.org/)
  - `$ composer install`
- A local server already running with this project root available at https://localhost
  - `composer start` will start a local server for you.
- PHP must have `xdebug` enabled.

Important: After all the above requirements are met, you must still follow
all instructions listed under `Installation and setup` above. Specifically,
you must have gone through `install.php` all the way through setting up the
OHM db schema.

## MyOpenMath (core OHM)

MOM currently uses PHPUnit for testing. To run those tests:

    $ composer install
    $ composer test-mom

## OHM

Additional requirements:

- PHP 7.4

PHP 7.4 is required for tests and test coverage reports.

    $ composer install
    $ composer migrate-ohm
    $ composer seed-ohm
    $ composer test-ohm

Tests will be available at
[ohm/tests/\_output/coverage/index.html](ohm/tests/_output/coverage/index.html).

# Long-term goals / wish list

- URLs are sometimes mangled when running a local server with `php -S`.
- Correct namespacing in OHM-specific code. (PSR-4)
  - Move/rename OHM-specific namespaced directories so they make more sense.
