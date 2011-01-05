# Maintenance Plugin for CakePHP #

## Feature ##

* Redirect maintenace page
* Allow IP setting
* Allow action setting

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
                  'allowedIp' => array('127.0.0.1'), // allowed IP address when maintanance status
                  'allowedAction' => array('posts' => array('index')) // allowed action when maintanance status
                )
             );
        }

## Usage ##

### Application Maintenace ###

When you set maintenance statu to your applicatoin,

#### bootstrap ####

Add the following code in bootstrap.php

    <?php
        Configure::write('Maintenance.enable', true);

#### file ####

Or, put app/tmp/maintanance file.

### Timer ###

If you want set maintenance start datetime or end datetime, Write `start_datetime,end_datetime` in app/tmp/maintanance

Example1: Maintenance 2011/1/1 ~ 2011/1/3

    2011-01-01 00:00:00,2011-01-03 23:59:59

Example2: Maintenance 2011/12/28 ~

    2011-12-28 00:00:00,

Example3: Maintenance ~ 2011/3/31 12:00

    ,2011-03-31 12:00:00

