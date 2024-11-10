<?php

namespace Teg\Types;

class RefundedPayment implements \Teg\Types\Interface\InitObject
{
    private $currency;
    private $total_amount;
    private $invoice_payload;
    private $telegram_payment_charge_id;
    private $provider_payment_charge_id;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->currency = isset($request->currency) ? $request->currency : 'XTR';
        $this->total_amount = isset($request->total_amount) ? $request->total_amount : 0;
        $this->invoice_payload = isset($request->invoice_payload) ? $request->invoice_payload : '';
        $this->telegram_payment_charge_id = isset($request->telegram_payment_charge_id) ? $request->telegram_payment_charge_id : '';
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

    public function getTelegramPaymentChargeId()
    {
        return $this->telegram_payment_charge_id;
    }

    public function getProviderPaymentChargeId()
    {
        return $this->provider_payment_charge_id;
    }
}
