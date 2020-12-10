<?php
/**
 * DTFinpay: Finpay payment receiver class
 * @since December 09th, 2020
 * @version 1.0
 * @link http://didats.net
 * @author Didats Triadi <didats@gmail.com>
 *
 */

class DTFinpayReceiver {
    public $requests;
    public $traxType, $merchantID, $invoiceNumber, $paymentCode, $resultCode, $resultText;
    public $logNumber, $paymentSource, $signature;

    private function issetVar(string $key): string {
        return (isset($this->requests[$key])) ? (string)$this->requests[$key] : "";
    }

    public function __construct(array $requests) {
        $this->requests = $requests;
        $keys = [
            'trax_type' => "traxType", 
            'merchant_id' => "merchantID", 
            'invoice' => "invoiceNumber", 
            'payment_code' => "paymentCode",
            'result_code' => "resultCode", 
            'result_desc' => "resultText", 
            'log_no' => "logNumber", 
            'payment_source' => "paymentSource", 
            'mer_signature' => "signature"
        ];

        foreach($keys as $key => $var) {
            $this->{"$var"} = $this->issetVar($key);
        }
    }
}
