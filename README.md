# Maintenance Plugin for CakePHP #

## Feature ##

* Redirect maintenace page
* Allow IP

## Installaion ##

First, put `maintenance’ directory on app/plugins in your CakePHP application.

Second, add the following code in app_controller.php

    <?php
        class AppController extends Controller {
            var $components = array('Maintenace.Maintenance');
        }

## Usage ##

## Application Maintenace ##

Add the following code in bootstrap.php

    <?php
        Configure::write('Maintenance.enable', true);
