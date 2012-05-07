<?php
class MaintenanceComponent extends Component {

    public $maintenanceUrl = null;
    public $allowedIp = array();
    public $allowedAction = array();
    public $statusFilePath = null;
    public $redirectStatus = 307;

    public $controller;

    public function initialize($controller) {
        if (empty($this->maintenanceUrl)) {
            return true;
        }
        if (empty($this->statusFilePath)) {
            $this->statusFilePath = TMP . 'maintenance';
        }

        $this->controller = $controller;

        $maintenanceUrl = Router::url($this->maintenanceUrl, true);
        $clientIp = $controller->request->clientIp();
        $maintenanceEnable = $this->isMaintenance();
        if ($maintenanceEnable === true) {
            // maintenance status

            if ( !$this->isAllowedAction($controller->request->params)
                 && strstr(Router::url('', true), $controller->request->webroot)
                 && Router::url('', true) != Router::url($maintenanceUrl, true)
                 && !in_array($clientIp, (array)$this->allowedIp)) {
                $controller->redirect($maintenanceUrl, $this->redirectStatus);
            }
        } else {
            // on service

            if (Router::url('', true) == $maintenanceUrl) {
                $controller->redirect('/');
            }
        }
    }

    /**
     * doMaintenance
     * Set maintenance status now
     *
     * @return
     */
    public function doMaintenance(){
        return $this->setTimer();
    }

    /**
     * setTimer
     * Set maintenance timer
     *
     * @param $start_time, $end_time
     * @return
     */
    public function setTimer($start = '', $end = ''){
        $start_date = date_parse($start);
        $end_date = date_parse($end);
        if ($start && !empty($start_date['errors'])) {
            return false;
        }
        if ($end && !empty($end_date['errors'])) {
            return false;
        }
        if (mktime($start_date['hour'], $start_date['minute'], $start_date['second'], $start_date['month'], $start_date['day'], $start_date['year']) > mktime($end_date['hour'], $end_date['minute'], $end_date['second'], $end_date['month'], $end_date['day'], $end_date['year'])) {
            return false;
        }
        if (!$this->awake()) {
            return false;
        }
        $file = new File($this->statusFilePath);
        return $file->write($start . ',' . $end);
    }

    /**
     * awake
     * Awake maintenance status
     *
     * @return
     */
    public function awake(){
        if (file_exists($this->statusFilePath)) {
            $file = new File($this->statusFilePath);
            return $file->delete();
        }
        return true;
    }

    /**
     * isMaintenance
     *
     * @return Boolean
     */
    public function isMaintenance(){
        if (Configure::read('Maintenance.enable')) {
            return true;
        }
        if (file_exists($this->statusFilePath)) {
            $file = new File($this->statusFilePath);
            $status = $file->read();
            if (!preg_match('/^[^,]*,[^,]*$/', $status)) {
                return true;
            }
            $dates = explode(',', $status);
            $start_date = date_parse($dates[0]);
            $end_date = date_parse($dates[1]);
            $now = time();
            if (!empty($start_date['errors']) && !empty($end_date['errors'])) {
                return true;
            }
            if (!empty($start_date['errors'])
                && $now < mktime($end_date['hour'], $end_date['minute'], $end_date['second'], $end_date['month'], $end_date['day'], $end_date['year'])) {
                return true;
            }
            if (!empty($end_date['errors'])
                && $now > mktime($start_date['hour'], $start_date['minute'], $start_date['second'], $start_date['month'], $start_date['day'], $start_date['year'])) {
                return true;
            }
            if ($now > mktime($start_date['hour'], $start_date['minute'], $start_date['second'], $start_date['month'], $start_date['day'], $start_date['year'])
                && $now < mktime($end_date['hour'], $end_date['minute'], $end_date['second'], $end_date['month'], $end_date['day'], $end_date['year'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * isAllowedAction
     *
     * @param $params
     * @return Boolean
     */
    public function isAllowedAction($params){
        if (!array_key_exists($params['controller'], $this->allowedAction)) {
            return false;
        }
        $actions = (array) $this->allowedAction[$params['controller']];
        if ($actions === array('*')) {
            return true;
        }
        return (in_array($params['action'], $actions));
    }
  }
