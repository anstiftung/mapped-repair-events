<h1 align="center">
  <img src="https://open-source.reparatur-initiativen.de/img/core/logo.jpg" alt="Mapped repair events" />
</h1>

<h4 align="center">A platform for mapped repair events</h4>

<p align="center">
  <a href="LICENSE">
    <img src="https://img.shields.io/github/license/anstiftung/mapped-repair-events"
         alt="Software license">
  </a>
</p>

<h1></h1>

## Installation guide

* set up vhost and start webserver and mysql-server
* clone repository from github
* import [config/sql/database.sql](https://raw.githubusercontent.com/anstiftung/mapped-repair-events/master/config/sql/database.sql) in your mysql database
* rename config/app\_custom.default.php to app\_custom.php and configure the database
* run `$ composer install --optimize-autoloader`
* run `$ npm --prefix ./webroot install ./webroot`
* Enable cronjob (once a day): `bin/cake SendWorknewsNotification`
* **If you have questions, please [create a new issue](https://github.com/anstiftung/mapped-repair-events/issues/new) on github**

## Netzwerk Reparatur-Initiativen
* [https://www.reparatur-initiativen.de/](https://www.reparatur-initiativen.de/)

## Demo page
* [https://open-source.reparatur-initiativen.de/](https://open-source.reparatur-initiativen.de/)
* the demo page is without user generated content
* [Login data for demo page](https://open-source.reparatur-initiativen.de/post/test-logins)

### Login data demo page

* **Admin**: mapped-repair-events-admin@mailinator.com PW: OSTestAdmin
* **Orga**: mapped-repair-events-orga@mailinator.com PW: OSTestOrga
* **Helper**: mapped-repair-events-helper@mailinator.com PW: OSTestHelper

<h1 align="center">
  <a href="https://www.anstiftung.de">
    <img src="https://anstiftung.de/images/anstiftung-logo.svg" alt="anstiftung" />
  </a>
</h1>

