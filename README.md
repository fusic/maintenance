# Maintenance Plugin for CakePHP #

## Feature ##

* Redirect maintenace page
* Allow IP

## Installaion ##

First, put `maintenance’ directory on app/plugins in your CakePHP application.

Second, add the following code in app_controller.php

    <?php
        class AppController extends Controller {
            var $components = array(
               'Maintenance.Maintenance' => array(
                  'maintenanceUrl' => array(
                     'controller' => 'public',
                     'action' => 'maintenance'),
                  'allowIp' => array('127.0.0.1'), // allowed IP address when maintanance status
                  'allowed' => array('posts' => array('index')) // allowed action when maintanance status
                )
             );
        }

## Usage ##

## Application Maintenace ##

Add the following code in bootstrap.php when maintanance status

    <?php
        Configure::write('Maintenance.enable', true);
