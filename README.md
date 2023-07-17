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
    <a href="LICENSE">
    <img src="https://img.shields.io/github/license/anstiftung/mapped-repair-events?style=for-the-badge"
         alt="Software license">
  </a>
</p>

## Installation guide

* set up vhost and start webserver and mysql-server
* clone repository from github
* import [config/sql/database.sql](https://raw.githubusercontent.com/anstiftung/mapped-repair-events/main/config/sql/database.sql) in your mysql database
* rename config/app\_custom.default.php to app\_custom.php and configure the database
* run `$ composer install --optimize-autoloader`
* run `$ npm --prefix ./webroot install ./webroot`
* Enable cronjob (once a day): `bin/cake SendWorknewsNotification`
* **If you have questions, please [create a new issue](https://github.com/anstiftung/mapped-repair-events/issues/new) on github**

## Requirements
* Server with shell access and cronjobs
* Apache with `mod_rewrite`
* PHP >= 8.2
* MySQL >= 8.0
* Node.js and npm ([installation](https://www.npmjs.com/get-npm)) developer packages
* Composer ([installation](https://getcomposer.org/download/)) developer packages

## Netzwerk Reparatur-Initiativen
* [https://www.reparatur-initiativen.de/](https://www.reparatur-initiativen.de/)

<h1 align="center">
  <a href="https://www.anstiftung.de">
    <img src="https://anstiftung.de/images/anstiftung-logo.svg" alt="anstiftung" />
  </a>
</h1>
