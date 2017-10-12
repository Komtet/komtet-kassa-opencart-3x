<?php
require_once __DIR__.'/komtet-kassa-sdk/autoload.php';

use Komtet\KassaSdk\Check;
use Komtet\KassaSdk\Client;
use Komtet\KassaSdk\Exception\SdkException;
use Komtet\KassaSdk\Payment;
use Komtet\KassaSdk\Position;
use Komtet\KassaSdk\QueueManager;
use Komtet\KassaSdk\TaxSystem;
use Komtet\KassaSdk\Vat;

class KomtetKassa {
	private $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

	public function getTaxSystems() {
		return array(
			TaxSystem::COMMON,
			TaxSystem::SIMPLIFIED_IN,
			TaxSystem::SIMPLIFIED_IN_OUT,
			TaxSystem::UTOII,
			TaxSystem::UST,
			TaxSystem::PATENT
		);
	}

	public function getVatRates() {
		return array(
			Vat::RATE_NO,
			Vat::RATE_0,
			Vat::RATE_10,
			Vat::RATE_18,
			Vat::RATE_110,
			Vat::RATE_118,
		);
	}

	public function printCheck($orderID) {
		if (intval($this->config->get('module_komtet_kassa_status')) === 0) {
			return;
		}

		$this->load->model('checkout/order');
		$order = $this->model_checkout_order->getOrder($orderID);

		if (!in_array($order['payment_code'], $this->config->get('module_komtet_kassa_payment_codes'))) {
			return;
		}

		$statusID = $order['order_status_id'];

		if (in_array($statusID, $this->config->get('module_komtet_kassa_statuses_sell'))) {
			$intent = Check::INTENT_SELL;
		} else if (in_array($statusID, $this->config->get('module_komtet_kassa_statuses_return'))) {
			$intent = Check::INTENT_SELL_RETURN;
		} else {
			return;
		}

		$totals = $this->getOrderTotals($orderID);
		$additionalPrice = ($totals['tax'] + $totals['coupon'] + $totals['voucher']) / $totals['sub_total'];

		$taxSystem = intval($this->config->get('module_komtet_kassa_tax_system'));
		$check = new Check($orderID, $order['email'], $intent, $taxSystem);
		$check->setShouldPrint(intval($this->config->get('module_komtet_kassa_should_print')) === 1);

		$total = 0;
		$stmt = sprintf("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = %d", $orderID);
		$productVatRate = $this->config->get('module_komtet_kassa_vat_rate_product');
		foreach ($this->db->query($stmt)->rows as $product) {
			$productPrice = $product['price'] * (1 + $additionalPrice);
			$productPriceTotal = $productPrice * $product['quantity'];
			$check->addPosition(new Position(
				$product['name'],
				$productPrice,
				floatval($product['quantity']),
				$productPriceTotal,
				abs(floatval($totals['coupon'])),
				new Vat($productVatRate)
			));
			$total += $productPriceTotal;
		}

		$shippingVatRate = $this->config->get('module_komtet_kassa_vat_rate_shipping');
		$check->addPosition(new Position(
			'Доставка',
			$totals['shipping'],
			1, // quantity
			$totals['shipping'],
			0, // discount
			new Vat($shippingVatRate)
		));

		$payment = Payment::createCard($total + $totals['shipping']);
		$check->addPayment($payment);

		$client = new Client(
			$this->config->get('module_komtet_kassa_shop_id'),
			$this->config->get('module_komtet_kassa_secret_key')
		);
		$client->setHost($this->config->get('module_komtet_kassa_server_url'));
		$qm = new QueueManager($client);
		$qm->registerQueue('default', $this->config->get('module_komtet_kassa_queue_id'));
		$qm->setDefaultQueue('default');

		try {
			$qm->putCheck($check);
		} catch (SdkException $e) {
			error_log(sprintf('Failed to print check: %s', $e->getMessage()));
		}
	}

	private function getOrderTotals($orderID) {
		$stmt = "SELECT code, value FROM " . DB_PREFIX . "order_total WHERE order_id = %d ORDER BY sort_order";
		$rows = $this->db->query(sprintf($stmt, $orderID))->rows;
		$result = array(
			'coupon' => 0,
			'shipping' => 0,
			'sub_total' => 0,
			'tax' => 0,
			'voucher' => 0
		);
		foreach ($rows as $row) {
			if (array_key_exists($row['code'], $result)) {
				$result[$row['code']] += $row['value'];
			}
		}

		return $result;
	}
}
