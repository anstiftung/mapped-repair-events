<h1 align="center">
  <img src="https://raw.githubusercontent.com/anstiftung/mapped-repair-events/main/webroot/img/core/logo.jpg" alt="Mapped repair events" />
</h1>

<h4 align="center">Mapped repair events. A platform for community repair.</h4>

<p align="center">
  <a href="https://github.com/anstiftung/mapped-repair-events/releases">
    <img src="https://img.shields.io/github/v/release/anstiftung/mapped-repair-events?label=stable&style=for-the-badge" alt="Latest stable version">
  </a>
    <a href="https://github.com/anstiftung/mapped-repair-events/actions">
        <img src="https://img.shields.io/github/actions/workflow/status/anstiftung/mapped-repair-events/ci.yml?branch=main&style=for-the-badge"
            alt="Build status">
  </a>
  <a href="https://codecov.io/gh/anstiftung/mapped-repair-events" target="_blank">
      <img alt="Coverage Status" src="https://img.shields.io/codecov/c/github/anstiftung/mapped-repair-events?style=for-the-badge">
  </a>
      <a href="LICENSE">
    <img src="https://img.shields.io/github/license/anstiftung/mapped-repair-events?style=for-the-badge"
         alt="Software license">
  </a>
</p>

## [API Documentation](https://anstiftung.github.io/mapped-repair-events-api-docs/)

## Installation guide

* set up vhost and start webserver and mysql-server
* clone repository from github
* import [config/sql/database.sql](https://raw.githubusercontent.com/anstiftung/mapped-repair-events/main/config/sql/init/database.sql) in your mysql database
* rename config/app\_custom.default.php to app\_custom.php and configure the database
* run `$ composer install --optimize-autoloader`
* run `$ npm --prefix ./webroot install ./webroot`
* **If you have questions, please [create a new issue](https://github.com/anstiftung/mapped-repair-events/issues/new) on github**

## Requirements
* Server with shell access and cronjobs
* Apache with `mod_rewrite`
* PHP >= 8.5
* MySQL >= 8.0
* Node.js and npm ([installation](https://www.npmjs.com/get-npm)) developer packages
* Composer ([installation](https://getcomposer.org/download/)) developer packages

## Event series
* Run `php bin/cake.php migrations migrate` after installing dependencies.
* The existing manual date rows and plus button remain available. An optional checkbox below the form enables recurrence for the first date; additional manual rows remain separate events.
* Weekly rules support multiple weekdays and intervals such as every two weeks. Monthly rules support up to ten independent position/weekday combinations, including the last weekday.
* An end date is mandatory and cannot be more than two years from today. A series is limited to 750 occurrences, allowing daily repetition over the full period. Dates are calculated on the server using `rlanvin/php-rrule` in the configured application timezone.
* Events remain independent records linked by a UUID in `series_uid`. Stable positions and excluded occurrences prevent deleted dates from returning when a series is extended.
* Editing and deletion support one occurrence or this and following future occurrences. Past occurrences remain unchanged. Individual field exceptions are preserved unless explicitly reset.
* Changing a recurrence rule starts a linked successor series while retaining existing event IDs. Series saves are transactional; subscriber notifications within seven days are bundled.

## Cronjobs
* daily, 3:00 `bash ./bin/cake BackupDatabase`
* daily, 8:00 `bash ./bin/cake SendWorknewsNotification`
* daily, 7:00 `bash ./bin/cake CleanWorknews`
* every 11th of a month, 3:30 `bash ./bin/cake BackupUserUploads`
* every 5 min `bash ./bin/cake StartQueue`

## Netzwerk Reparatur-Initiativen
* [https://www.reparatur-initiativen.de/](https://www.reparatur-initiativen.de/)
* [https://www.demokratiecafe.de/](https://www.demokratiecafe.de/)

<h1 align="center">
  <a href="https://www.anstiftung.de">
    <img src="https://anstiftung.de/images/anstiftung-logo.svg" alt="anstiftung" />
  </a>
</h1>
