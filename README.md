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
* PHP >= 8.4
* MySQL >= 8.0
* Node.js and npm ([installation](https://www.npmjs.com/get-npm)) developer packages
* Composer ([installation](https://getcomposer.org/download/)) developer packages

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
