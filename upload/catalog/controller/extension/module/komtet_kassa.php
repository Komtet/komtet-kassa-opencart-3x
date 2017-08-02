<?php

class ControllerExtensionModuleKomtetKassa extends Controller {
	public function index() {
		if (!array_key_exists('HTTP_X_HMAC_SIGNATURE', $this->request->server)) {
			header('HTTP/1.1 401 Unauthorized');
			exit();
		}

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			header('HTTP/1.1 405 Method Not Allowed');
			header('Allow: POST');
			exit();
		}

		$secretKey = $this->config->get('module_komtet_kassa_secret_key');
		if (empty($secretKey)) {
			error_log('Unable to handle request: module_komtet_kassa_secret_key is not defined');
			header('HTTP/1.1 500 Internal Server Error');
			exit();
		}

		$url = $this->url->link('extension/module/komtet_kassa', '', true);
		$data = file_get_contents('php://input');
		$signature = hash_hmac('md5', $this->request->server['REQUEST_METHOD'] . $url . $data, $secretKey);
		if ($signature != $this->request->server['HTTP_X_HMAC_SIGNATURE']) {
			header('HTTP/1.1 403 Forbidden');
			exit();
		}

		$data = json_decode($data, true);
		foreach (array('external_id', 'state') as $key) {
			if (!array_key_exists($key, $data)) {
				header('HTTP/1.1 400 Bad Request');
				header('Content-Type: text/plain');
				echo $key." is required\n";
				exit();
			}
		}

		$orderID = $data['external_id'];
		$success = $data['state'] == 'done';
		$errorDescription = !$success ? $data['error_description'] : '';

		$this->db->query(sprintf(
			"INSERT INTO `%skomtet_kassa_report` (`order_id`, `success`, `error`) VALUES ('%s', '%s', '%s')",
			DB_PREFIX,
			$orderID,
			$success ? 1 : 0,
			$errorDescription
		));
	}

	public function onAddOrderHistoryAfter($route, $args) {
		$this->load->library('komtetkassa');
		$this->komtetkassa->printCheck($args[0]);
	}
}
