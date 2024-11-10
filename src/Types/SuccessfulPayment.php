<?php

namespace Teg\Types;

class SuccessfulPayment implements \Teg\Types\Interface\InitObject
{
    private $currency;
    private $total_amount;
    private $invoice_payload;
    private $shipping_option_id;
    private $order_info;
    private $telegram_payment_charge_id;
    private $provider_payment_charge_id;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->currency = isset($request->currency) ? $request->currency : null;
        $this->total_amount = isset($request->total_amount) ? $request->total_amount : null;
        $this->invoice_payload = isset($request->invoice_payload) ? $request->invoice_payload : null;
        $this->shipping_option_id = isset($request->shipping_option_id) ? $request->shipping_option_id : null;
        $this->order_info = isset($request->order_info) ? new OrderInfo($request->order_info) : null;
        $this->telegram_payment_charge_id = isset($request->telegram_payment_charge_id) ? $request->telegram_payment_charge_id : null;
        $this->provider_payment_charge_id = isset($request->provider_payment_charge_id) ? $request->provider_payment_charge_id : null;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    public function getInvoicePayload()
    {
        return $this->invoice_payload;
    }

    public function getShippingOptionId()
    {
        return $this->shipping_option_id;
    }

    public function getOrderInfo()
    {
        return $this->order_info;
    }

    public function getTelegramPaymentChargeId()
    {
        return $this->telegram_payment_charge_id;
    }

    public function getProviderPaymentChargeId()
    {
        return $this->provider_payment_charge_id;
    }
}
