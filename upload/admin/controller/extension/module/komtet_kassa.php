<?php

class ControllerExtensionModuleKomtetKassa extends Controller {
	const ROUTE = 'extension/module/komtet_kassa';
	const SETTING_CODE = 'module_komtet_kassa';
	const SETTING_PREFIX = 'module_komtet_kassa_';

	private $metadata = array(
		'settings' => array(
			'module_komtet_kassa_server_url' => 'https://kassa.komtet.ru',
			'module_komtet_kassa_shop_id' => '',
			'module_komtet_kassa_secret_key' => '',
			'module_komtet_kassa_queue_id' => '',
			'module_komtet_kassa_tax_system' => 0,
			'module_komtet_kassa_vat_rate_product' => 18,
			'module_komtet_kassa_vat_rate_shipping' => 18,
			'module_komtet_kassa_payment_codes' => [],
			'module_komtet_kassa_statuses_sell' => [],
			'module_komtet_kassa_statuses_return' => [],
			'module_komtet_kassa_should_print' => 1,
			'module_komtet_kassa_status' => 0,
		),
		'events' => array(
			array(
				'code' => 'module_komtet_kassa_add_order_history_after',
				'trigger' => 'catalog/model/checkout/order/addOrderHistory/after',
				'action' => 'extension/module/komtet_kassa/onAddOrderHistoryAfter'
			)
		)
	);

	public function install() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting(self::SETTING_CODE, $this->metadata['settings']);

		$this->load->model('extension/event');
		foreach ($this->metadata['events'] as $event) {
			$this->model_extension_event->addEvent($event['code'], $event['trigger'], $event['action']);
		}

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "komtet_kassa_report` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`order_id` int(11) NOT NULL,
				`success` tinyint(1) NOT NULL,
				`error` TEXT DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		");

		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'extension/report');
		$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'extension/report/komtet_kassa');
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting(self::SETTING_CODE);

		$this->load->model('extension/event');
		foreach ($this->metadata['events'] as $event) {
			$this->model_extension_event->deleteEvent($event['code']);
		}

		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "komtet_kassa_report`;");

		$this->load->model('user/user_group');
		$this->model_user_user_group->removePermission($this->user->getId(), 'access', 'extension/report/komtet_kassa');
	}

	public function index() {
		$this->load->language(self::ROUTE);
		$headingTitle = $this->language->get('heading_title');
		$this->document->setTitle($headingTitle);

		$data = array();

		$data['heading_title'] = $headingTitle;
		$data['text_edit'] = $this->language->get('text_edit');

		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
			),
			array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link(self::ROUTE, 'token=' . $this->session->data['token'], true)
			)
		);

		if (!$this->user->hasPermission('modify', self::ROUTE)) {
			$data['denied'] = true;
		} else {
			$this->load->library('komtetkassa');
			$this->load->model('localisation/order_status');
			$this->load->model('extension/extension');
			$this->load->model('setting/setting');

			$data['denied'] = false;
			$data['action'] = $this->url->link(self::ROUTE, 'token=' . $this->session->data['token'], true);
			$data['cancel'] = $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

			foreach (array(
				'server_url',
				'shop_id',
				'secret_key',
				'queue_id',
				'tax_system',
				'vat_rate_product',
				'vat_rate_shipping',
				'payment_codes',
				'statuses_sell',
				'statuses_return',
				'should_print',
				'status'
			) as $i) {
				$key = 'setting_'.$i;
				$data[$key] = $this->language->get($key);
			}
			$data['text_enabled'] = $this->language->get('text_enabled');
			$data['text_disabled'] = $this->language->get('text_disabled');

			$data['errors'] = array();
			$data['settings'] = array();

			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				$errorRequired = $this->language->get('error_required');
				foreach (array_keys($this->metadata['settings']) as $key) {
					$settingsKey = str_replace(self::SETTING_PREFIX, '', $key);
					if (!isset($this->request->post[$key]) || $this->request->post[$key] == '') {
						$data['errors'][$settingsKey] = $errorRequired;
					} else {
						$data['settings'][$settingsKey] = $this->request->post[$key];
					}
				}
				if (empty($data['errors'])) {
					$this->model_setting_setting->editSetting(self::SETTING_CODE, $this->request->post);
					$this->session->data['success'] = $this->language->get('text_success');
					$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
				}
			}

			foreach ($this->model_setting_setting->getSetting(self::SETTING_CODE) as $key => $value) {
				$key = str_replace(self::SETTING_PREFIX, '', $key);
				if (!array_key_exists($key, $data['settings'])) {
					$data['settings'][$key] = $value;
				}
			}

			$data['tax_systems'] = array_map(function ($item) use ($data) {
				return array(
					'label' => $this->language->get(sprintf('setting_tax_system_entry_%s', $item)),
					'value' => $item,
					'enabled' => $item == $data['settings']['tax_system']
				);
			}, $this->komtetkassa->getTaxSystems());

			$data['product_vat_rates'] = array_map(function ($item) use ($data) {
				return array(
					'label' => $item,
					'value' => $item,
					'enabled' => $item == $data['settings']['vat_rate_product']
				);
			}, $this->komtetkassa->getVatRates());

			$data['shipping_vat_rates'] = array_map(function ($item) use ($data) {
				return array(
					'label' => $item,
					'value' => $item,
					'enabled' => $item == $data['settings']['vat_rate_shipping']
				);
			}, $this->komtetkassa->getVatRates());

			$data['payment_codes'] = array_map(function ($item) use ($data) {
				$lang = 'payment_' . $item;
				$this->load->language('extension/payment/' . $item);

				return array(
					'label' => $this->language->get('heading_title'),
					'value' => $item,
					'enabled' => in_array($item, $data['settings']['payment_codes']),
				);
			}, $this->model_extension_extension->getInstalled('payment'));

			$orderStatuses = $this->model_localisation_order_status->getOrderStatuses();

			$data['statuses_sell'] = array_map(function ($item) use ($data) {
				return array(
					'label' => $item['name'],
					'value' => $item['order_status_id'],
					'enabled' => in_array($item['order_status_id'], $data['settings']['statuses_sell'])
				);
			}, $orderStatuses);

			$data['statuses_return'] = array_map(function ($item) use ($data) {
				return array(
					'label' => $item['name'],
					'value' => $item['order_status_id'],
					'enabled' => in_array($item['order_status_id'], $data['settings']['statuses_return'])
				);
			}, $orderStatuses);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$this->response->setOutput($this->load->view(self::ROUTE, $data));
	}
}
