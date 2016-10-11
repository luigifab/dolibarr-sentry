Send Dolibarr logs to a Sentry server
=====================================

![Sentry logo](img/sentry.png)

https://sentry.io

For [Dolibarr](https://dolibarr.org) >= 5.0.x.
Sentry support was included in Dolibarr core 3.9 and is broken in 4.x.

Install
-------

### From the GIT repository

1. Clone the repository into ```htdocs/custom```
2. Install [Composer](https://getcomposer.org) and [Bower](https://bower.io) dependencies:
   ```sh
   composer install --no-dev
   ```

### Using [Composer](https://getcomposer.org)
Require this repository from Dolibarr's composer:
```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/gpcsolutions/sentry"
    }
  ],
  "require": {
    "gpcsolutions/sentry": "dev-master"
  }
}
```

Run
```sh
composer update
```

### From an archive release

Extract the archive to ```htdocs/custom```

Contributions
-------------

Feel free to contribute and report defects at <http://github.com/GPCsolutions/sentry/issues>

Licenses
--------

### Main code

![GPLv3 logo](img/gplv3.png)

GPLv3 or (at your option) any later version.

See [COPYING](COPYING) for more information.

### Other Licenses

#### [Parsedown](http://parsedown.org/)

Used to display this README in the module's about page.
Licensed under MIT.

#### [GNU Licenses logos](https://www.gnu.org/graphics/license-logos.html)

Public domain


#### Documentation

All texts and readmes.

![GFDL logo](img/gfdl.png)
