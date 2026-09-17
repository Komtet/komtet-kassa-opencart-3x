<?php
require_once __DIR__.'/komtet-kassa-sdk/autoload.php';

use Komtet\KassaSdk\Exception\SdkException;
use Komtet\KassaSdk\v1\CalculationMethod;
use Komtet\KassaSdk\v1\CalculationSubject;
use Komtet\KassaSdk\v1\Check;
use Komtet\KassaSdk\v1\Client;
use Komtet\KassaSdk\v1\Payment;
use Komtet\KassaSdk\v1\Position;
use Komtet\KassaSdk\v1\QueueManager;
use Komtet\KassaSdk\v1\TaxSystem;
use Komtet\KassaSdk\v1\Vat;

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
			TaxSystem::UST,
			TaxSystem::PATENT
		);
	}

	public function getVatRates() {
		return array(
			Vat::RATE_NO,
			Vat::RATE_0,
			Vat::RATE_5,
			Vat::RATE_7,
			Vat::RATE_10,
			Vat::RATE_20,
			Vat::RATE_22
		);
	}

    protected static $prePaymentVatMap = [
        Vat::RATE_5  => Vat::RATE_105,
        Vat::RATE_7  => Vat::RATE_107,
        Vat::RATE_10 => Vat::RATE_110,
        Vat::RATE_20 => Vat::RATE_120,
        Vat::RATE_22 => Vat::RATE_122
    ];

    protected function getPaymentProps($orderStatus, $statusesHistory, $statusesPrepay, $statusesSell)
    {
        /**
         * Получение опций оплаты
         * @param string $orderStatus новый статус заказа
         * @param string $statusesHistory история всех статусов заказа
         * @param array $statusesPrepay статусы предоплаты из настроек
         * @param array $statusesSell статусы оплаты из настроек
         */

        // 1 check way
        if (empty($statusesPrepay) && in_array($orderStatus, $statusesSell)) {
            return array(
                'calculationMethod' => CalculationMethod::FULL_PAYMENT,
                'calculationSubject' => $this->calculationSubject,
                'isFullPayment' => false
            );
        }
        // 2 checks way
        else if ($statusesPrepay) {
            // prepayment
            if (in_array($orderStatus, $statusesPrepay)) {
                return array(
                    'calculationMethod' => CalculationMethod::PRE_PAYMENT_FULL,
                    'calculationSubject' => CalculationSubject::PAYMENT,
                    'isFullPayment' => false
                );
            }
            // full payment
            else if (
                in_array($orderStatus, $statusesSell) &&
                array_intersect($statusesHistory, $statusesPrepay)
            )
            {
                return array(
                    'calculationMethod' => CalculationMethod::FULL_PAYMENT,
                    'calculationSubject' => $this->calculationSubject,
                    'isFullPayment' => true
                );
            }
        }

        return array(
            'calculationMethod' => null,
            'calculationSubject' => null,
            'isFullPayment' => null
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

		$statusID = (int)$order['order_status_id'];

		$statusesPrepay = array_map('intval', (array)$this->config->get('module_komtet_kassa_statuses_prepay'));
		$statusesSell = array_map('intval', (array)$this->config->get('module_komtet_kassa_statuses_sell'));
		$statusesReturn = array_map('intval', (array)$this->config->get('module_komtet_kassa_statuses_return'));

		if (
			in_array($statusID, $statusesSell, true) || in_array($statusID, $statusesPrepay, true)
		) {
			$intent = Check::INTENT_SELL;
		} else if (in_array($statusID, $statusesReturn, true)) {
			$intent = Check::INTENT_SELL_RETURN;
		} else {
			return;
		}

		$statusesHistoryQuery = $this->db->query("
			SELECT `order_status_id`
			FROM `" . DB_PREFIX . "order_history`
			WHERE `order_id` = '" . (int)$orderID . "'
			ORDER BY `order_history_id` DESC
		");
		$statusesHistory = array_map(
			function ($row) { return (int)$row['order_status_id']; },
			$statusesHistoryQuery->rows
		);

		$paymentProps = $this->getPaymentProps(
			$statusID,
			$statusesHistory,
			$statusesPrepay,
			$statusesSell
		);

		$totals = $this->getOrderTotals($orderID);
		$discount = abs($totals['tax'] + $totals['coupon'] + $totals['voucher']);

		$taxSystem = intval($this->config->get('module_komtet_kassa_tax_system'));
		$check = new Check($orderID, $order['email'], $intent, $taxSystem);
		$check->setShouldPrint(intval($this->config->get('module_komtet_kassa_should_print')) === 1);
		$check->setInternet(intval($this->config->get('module_komtet_kassa_is_internet')) === 1);

		$total = 0;
		$stmt = sprintf("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = %d", $orderID);
		$productVatRate = $this->config->get('module_komtet_kassa_vat_rate_product');

		if ($paymentProps['calculationMethod'] == CalculationMethod::PRE_PAYMENT_FULL) {
			$productVatRate = self::$prePaymentVatMap[$productVatRate] ?? $productVatRate;
		}
		$productVatRate = new Vat($productVatRate);

		foreach ($this->db->query($stmt)->rows as $product) {
			$productTotal = $product['price'] * $product['quantity'];
			$position = new Position(
				html_entity_decode($product['name']),
				round($product['price'], 2),
				floatval($product['quantity']),
				round($productTotal, 2),
				$productVatRate
			);
			$position->setCalculationMethod($paymentProps['calculationMethod']);
			$position->setCalculationSubject($paymentProps['calculationSubject']);

			$check->addPosition($position);

			$total += $productTotal;
		}

		if ($discount > 0) {
			$check->applyDiscount($discount);
		}

		$shippingVatRate = $this->config->get('module_komtet_kassa_vat_rate_shipping');
		$shipping_position = new Position(
			'Доставка',
			$totals['shipping'],
			1, // quantity
			$totals['shipping'],
			new Vat($shippingVatRate)
		);

		// Доставка передаётся как услуга
		$shipping_position->setCalculationSubject(CalculationSubject::SERVICE);
		$check->addPosition($shipping_position);

		$paymentType = $paymentProps['isFullPayment'] ? Payment::TYPE_PREPAYMENT : Payment::TYPE_CARD;
		$payment = new Payment(
			$paymentType,
			round($total + $totals['shipping'] - $discount , 2)
		);
		$check->addPayment($payment);

		$client = new Client(
			$this->config->get('module_komtet_kassa_shop_id'),
			$this->config->get('module_komtet_kassa_secret_key')
		);
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
