# Federal Timeline
Place this app in **nextcloud/apps/**

No further steps required (except activating the app in the Admin panel).

The aim of this app is to make it easier for organized federations to view and sort through all their documents in a chronological and bureaucratic way, that is classified by the relevant instance (secretariats, committees, ...). It has yet to be translated in english and other languages, for now it is french only.

[FR] [Billet de présentation / Manuel d'utilisation](https://zestedesavoir.com/billets/1826/federal-timeline-v1-0/)

## UNLICENCE or COPYING ?
COPYING only applies to ```/Makefile``` because it is from the default app skeleton. All the rest belongs to me, you, and anyone who cares, and is released into public domain via UNLICENCE.

## Building the app

The app can be built by using the provided Makefile by running:

    make

This requires the following things to be present:
* make
* which
* tar: for building the archive
* curl: used if phpunit and composer are not installed to fetch them from the web
* npm: for building and testing everything JS, only required if a package.json is placed inside the **js/** folder

The make command will install or update Composer dependencies if a composer.json is present and also **npm run build** if a package.json is present in the **js/** folder. The npm **build** script should use local paths for build systems and package managers, so people that simply want to build the app won't need to install npm libraries globally, e.g.:

**package.json**:
```json
"scripts": {
    "test": "node node_modules/gulp-cli/bin/gulp.js karma",
    "prebuild": "npm install && node_modules/bower/bin/bower install && node_modules/bower/bin/bower update",
    "build": "node node_modules/gulp-cli/bin/gulp.js"
}
```


## Publish to App Store

First get an account for the [App Store](http://apps.nextcloud.com/) then run:

    make && make appstore

The archive is located in build/artifacts/appstore and can then be uploaded to the App Store.

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
