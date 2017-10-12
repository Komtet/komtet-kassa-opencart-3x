<?php

class ControllerExtensionReportKomtetKassa extends Controller {
	const LIMIT = 50;

	public function index() {
		$this->load->language('extension/report/komtet_kassa');
		$this->document->setTitle($this->language->get('heading_title'));

		$page = isset($this->request->get['page']) ? intval($this->request->get['page']) : 1;

		$total = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "komtet_kassa_report")->row['total'];

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = self::LIMIT;
		$pagination->url = $this->url->link('extension/report/komtet_kassa', 'token=' . $this->session->data['token'] . '&page={page}', true);

		$items = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . "komtet_kassa_report` " .
			"ORDER BY id DESC " .
			"LIMIT " . ($page - 1) * self::LIMIT . ", " . self::LIMIT
		)->rows;
		$items = array_map(function ($item) {
			$item['success'] = intval($item['success']) == 1;
			$qs = 'token=' . $this->session->data['token'] . '&order_id=' . $item['order_id'];
			$item['order_url'] = $this->url->link('sale/order/info', $qs, true);
			return $item;
		}, $items);

		$this->response->setOutput($this->load->view('extension/report/komtet_kassa', array(
			'breadcrumbs' => array(
				array(
					'text' => $this->language->get('text_home'),
					'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
				),
				array(
					'text' => $this->language->get('heading_title'),
					'href' => $this->url->link('extension/report/komtet_kassa', 'token=' . $this->session->data['token'], true)
				)
			),
			'header' => $this->load->controller('common/header'),
			'column_left' => $this->load->controller('common/column_left'),
			'footer' => $this->load->controller('common/footer'),
			'pagination' => $pagination->render(),
			'items' => $items,
			'heading_title' => $this->language->get('heading_title'),
			'lang_order_id' => $this->language->get('lang_order_id'),
			'lang_status' => $this->language->get('lang_status'),
			'lang_error' => $this->language->get('lang_error'),
			'lang_no_data' => $this->language->get('lang_no_data'),
			'lang_failed' => $this->language->get('lang_failed'),
			'lang_success' => $this->language->get('lang_success'),
		)));
	}
}
