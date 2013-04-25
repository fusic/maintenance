<?php
App::uses('File', 'Utility');

class MaintenanceComponent extends Component {

	public $maintenanceUrl = null;

	public $allowedIp = array();

	public $allowedAction = array();

	public $statusFilePath = null;

	public $redirectStatus = 307;

	public $controller;

	public function initialize(Controller $controller) {
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
			if (!$this->isAllowedAction($controller->request->params)
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
	public function doMaintenance() {
		return $this->setTimer();
	}

/**
 * setTimer
 * Set maintenance timer
 *
 * @param $start_time, $end_time
 * @return
 */
	public function setTimer($start = '', $end = '') {
		$startDate = date_parse($start);
		$endDate = date_parse($end);
		if ($start && !empty($startDate['errors'])) {
			return false;
		}
		if ($end && !empty($endDate['errors'])) {
			return false;
		}
		if (mktime($startDate['hour'], $startDate['minute'], $startDate['second'], $startDate['month'], $startDate['day'], $startDate['year']) > mktime($endDate['hour'], $endDate['minute'], $endDate['second'], $endDate['month'], $endDate['day'], $endDate['year'])) {
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
	public function awake() {
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
	public function isMaintenance() {
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
			$startDate = date_parse($dates[0]);
			$endDate = date_parse($dates[1]);
			$now = time();
			if (!empty($startDate['errors']) && !empty($endDate['errors'])) {
				return true;
			}
			if (!empty($startDate['errors'])
				&& $now < mktime($endDate['hour'], $endDate['minute'], $endDate['second'], $endDate['month'], $endDate['day'], $endDate['year'])) {
				return true;
			}
			if (!empty($endDate['errors'])
				&& $now > mktime($startDate['hour'], $startDate['minute'], $startDate['second'], $startDate['month'], $startDate['day'], $startDate['year'])) {
				return true;
			}
			if ($now > mktime($startDate['hour'], $startDate['minute'], $startDate['second'], $startDate['month'], $startDate['day'], $startDate['year'])
				&& $now < mktime($endDate['hour'], $endDate['minute'], $endDate['second'], $endDate['month'], $endDate['day'], $endDate['year'])) {
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
	public function isAllowedAction($params) {
		if (!array_key_exists($params['controller'], $this->allowedAction)) {
			return false;
		}
		$actions = (array)$this->allowedAction[$params['controller']];
		if ($actions === array('*')) {
			return true;
		}
		return (in_array($params['action'], $actions));
	}

}
