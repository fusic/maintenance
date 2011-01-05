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

    public $maintenanceUrl;
    public $allowed = array();
    public $allowIp = array();

    public function __construct() {
        parent::__construct();
    }

    public function initialize(&$controller, $settings = array()) {
        if (empty($settings['maintenanceUrl'])) {
            return true;
        }

        $this->controller = $controller;
        $this->_set($settings);

        $maintenanceUrl = Router::url($this->maintenanceUrl, true);
        $clientIp = $this->RequestHandler->getClientIP();
        $maintenanceEnable = Configure::read('Maintenance.enable');
        if ($maintenanceEnable === true) {
            // maintenance status

            if ( !$this->isAllowed($this->controller->params)
                 && strstr(Router::url('', true), $controller->webroot)
                 && Router::url('', true) != Router::url($maintenanceUrl, true)
                 && !in_array($clientIp, (array)$this->allowIp)) {
                $controller->redirect($maintenanceUrl);
            }
        } else {
            // on service

            if (Router::url('', true) == $maintenanceUrl) {
                $controller->redirect('/');
            }
        }
    }

    /**
     * isAllowed
     *
     * @param $params
     * @return
     */
    public function isAllowed($params){
        if (!array_key_exists($params['controller'], $this->allowed)) {
            return false;
        }
        $actions = (array) $this->allowed[$params['controller']];
        if ($actions === array('*')) {
            return true;
        }
        return (in_array($params['action'], $actions));
    }
  }
