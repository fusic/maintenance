# Maintenance plugin for CakePHP2.x #

![maintenance.png](documents/maintenance.png)

* When you use this　plugin in CakePHP1.3, please see another branches[1.3]. 

## Feature ##

* Redirect maintenance page
* Allow IP setting
* Allow action setting

## Installaion ##

First, put `maintenance’ directory on app/Plugin in your CakePHP application.

Second, load this plugin in Config/bootstrap.php

```php
    <?php
        CakePlugin::load('Maintenance');
```

Finally, add the following code in Controller/AppController.php

```php
    <?php
        class AppController extends Controller {
            public $components = array(
               'Maintenance.Maintenance' => array(
                  'maintenanceUrl' => array(
                     'controller' => 'public',
                     'action' => 'maintenance'),
                  'allowedIp' => array('127.0.0.1'), // allowed IP address when maintanance status
                  'allowedAction' => array('posts' => array('index'),
                                           'users' => array('*')) // allowed action when maintanance status
                )
             );
        }
```

When maintenance status, this setting allow 

* full access from `127.0.0.1`
* access `index` action within `posts` controller.
* access all action within `users` controller.

And

* redirect to `maintenanceUrl`.

## Usage ##

### Application Maintenace ###

When you set maintenance status to your applicatoin,

#### bootstrap ####

Add the following code in bootstrap.php

```php
    <?php
        Configure::write('Maintenance.enable', true);
```

#### file ####

Or, put app/tmp/maintanance file.

### Timer ###

If you want set maintenance start datetime or end datetime, Write `start_datetime,end_datetime` in app/tmp/maintanance file.

#### Example1: Maintenance 2011/1/1 ~ 2011/1/3 ####

    2011-01-01 00:00:00,2011-01-03 23:59:59

#### Example2: Maintenance 2011/12/28 ~ ####

    2011-12-28 00:00:00,

#### Example3: Maintenance ~ 2011/3/31 12:00 ####

    ,2011-03-31 12:00:00

### Methods ###

#### Maintenance::doMaintenance() ####
Set maintenance status now.

#### Maintenance::setTimer(start_time, end_time) ####
Set maintenance timer.

#### Maintenance::awake() ####
Awake maintenance status.


## License ##

The MIT Lisence

Copyright (c) 2010 Fusic Co., Ltd. (http://fusic.co.jp)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## Author ##

Shintaro Sugimoto
