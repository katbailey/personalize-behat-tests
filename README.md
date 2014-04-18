# Acquia Lift

[![Build Status](https://magnum.travis-ci.com/cpliakas/personalize.png?token=PH71WkhMufTnsVvCU5rV&branch=kickstart)](https://magnum.travis-ci.com/cpliakas/personalize)

For a digital marketer who needs to deliver focused website content, Acquia Lift
is a solution for testing and targeting that increases visitor engagement and
conversion rates across community, content and commerce. Unlike WCM solutions
such as Adobe, SiteCore, and targeting services such as Optimizely, and
Monetate, our product can be set up in 5 minutes, learns on its own, can deliver
benefits within a day, has one unified interface and content repository, and is
extensible using thousands of Drupal or custom integrations.

This repository is a distribution that downloads and installs the Acquia Lift
modules and their dependencies so that developers can experiment with and hack
on the modules without having to focus on setup.

## Installation

* Clone the repository and change into the working copy
* Create a database, configure the web server to serve the `docroot` directory
* Copy the `build.properties.dist` file to `build.properties`
* Modify `build.properties` according to your local environment

```ini
base.url=http://localhost
db.url=mysql://username:password@host/db

#sites.subdir=default
#site.mail=test@example.com
#account.name=admin
#account.pass=admin
#account.mail=test@example.com
```

* Run `ant` on the command line

_NOTE_: You can also build and install the distro by manually running `drush
make` and `drush site-install` commands with options that reflect your
development environment.

## Development

@todo

#### Writing Behavior Tests

Behavior tests are contained in the `test` directory. Refer to the
[Behat](http://behat.org/) project for more details on writing tests. The Apache
Ant targets included with this distribution will automatically install the test
suite (Behat + Mink + Selenium).

#### Running Behavior Tests

* Run `ant run-tests` to re-install the distro and run the behavior tests
  * Pass the `-Ddrush.nomake=1` option to prevent rebuilding the docroot
  * Pass the `-Ddrush.noinstall=1` option to prevent reinstalling the distro
