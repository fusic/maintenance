# Maintenance Plugin for CakePHP #

## Feature ##

* Redirect maintenace page
* Allow IP

## Instration ##

First, put `maintenance’ directory on app/plugins in your CakePHP application.
Second, add the following code in app_controller.php

        <?php
            class AppController extends Controller {
                var $components = array('Maintenace.Maintenance');
            }




