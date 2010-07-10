<?php
/**
 * Site Maintenance component for CakePHP.
 *
 * Copyright (c) by Shintaro Sugimoto
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class MaintenanceComponent extends Object {
    var $components = array('RequestHandler');

    public function __construct() {
        parent::__construct();
    }

    public function initialize($controller, $settings = array()) {
        $defaults = array('maintenanceUrl' => '/', 'allowIp' => '127.0.0.1');
        $settings = Set::merge($defaults, $settings);

        $maintenanceUrl = Router::url($settings['maintenanceUrl'], true);
        $clientIp = $this->RequestHandler->getClientIP();
        $maintenanceEnable = Configure::read('Maintenance.enable');
        if ($maintenanceEnable === true) {
            if ( Router::url('', true) != $maintenanceUrl && !in_array($clientIp, (array)$settings['allowIp'])) {
                $controller->redirect($maintenanceUrl);
            }
        } else {
            if (Router::url('', true) == $maintenanceUrl) {
                $controller->redirect('/');
            }
        }
    }

}
