<?php
/**
 * DTFinpay: Finpay main payment class
 * @since December 09th, 2020
 * @version 1.0
 * @link http://didats.net
 * @author Didats Triadi <didats@gmail.com>
 *
 */

class DTFinpay {
    private $file = "./config.cfg";
    private $fileContent;
    private $paymentData = [];
    private $endpoint;
    private $build;

    public $done;
    
    public function __construct() {
        $handle = @fopen($this->file, "r");
        if ($handle) {
            $str = "";
            while (($buffer = fgets($handle, 4096)) !== false) {
                $str .= $buffer;
            }
            fclose($handle);

            $this->fileContent = $str;
        }
    }

    // configuration methods
    private function config(string $key): string {
        $pattern = "/([a-zA-Z0-9\_?]+\.?[a-zA-Z0-9\_?]+)[ ]?=[ ]?[\"|\']([^\"|\']+)/";
        preg_match_all($pattern, $this->fileContent, $matches);
        $data = array();
        if(count($matches) > 2) {
            $data = $this->map($matches);
        }
        
        return (isset($data[$key])) ? $data[$key] : "";
    }

    private function map(array $item): array {
        $data = array();
        if(!isset($item[1])) return $data;
        if(!isset($item[2])) return $data;

        foreach($item[1] as $index => $value) {
            $data[$value] = $item[2][$index];
        }
        return $data;
    }

    public function initiate(
        string $trackID,
        string $amount,
        string $paymentName,
        array $paymentData = [],
        DTFinpayCustomer $customer
    ): array {

        $this->endpoint = ($this->config("merchant.live") == "false") ? $this->config("endpoint.sandbox") : $this->config("endpoint.live");
        $this->paymentData = [
            "trans_date" => date('Ymdhis'),
            "invoice" => $trackID,
            "amount" => $amount,
            "timeout" => $this->config("merchant.timeout"),
            "cust_id" => $customer->id,
            "cust_name" => $customer->name,
            "cust_email" => $customer->email,
            "cust_msisdn" => $customer->phone,
            "items" => $paymentName,
            "return_url" => $this->config("merchant.returnURL"),
            "success_url" => $this->config("merchant.successURL"),
            "failed_url" => $this->config("merchant.successURL")
        ];

        $this->paymentData["add_info1"] = $customer->name;
        $this->paymentData["add_info5"] = $trackID;

        foreach($paymentData as $key => $value) {
            $i = $key + 1;
            ${"add_info$i"} = $value;

            $this->paymentData["add_info$i"] = $value;
        }

        $this->paymentData['merchant_id'] = $this->config("merchant.id");
        $this->paymentData['mer_signature'] = $this->signature();
        $this->build = json_encode($this->paymentData);

        $response = $this->curl();
        return json_decode($response, true);
    }

    public function receiver(): array {
        $finpayReceiver = new DTFinpayReceiver($_REQUEST);
        $result = (int)$finpayReceiver->resultCode;
        return ['status' => ($result == 0), 'data' => $finpayReceiver];
    }

    private function curl(): string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Developer: Rimbunesia',
        ));
        $server_output = curl_exec($ch);
        curl_close ($ch);

        return $server_output;
    }

    private function signature(): string {
        $data = $this->paymentData;
        $sign = $data['add_info1'] . "%" .
                $data['add_info2'] . "%" .
                $data['add_info3'] . "%" .
                $data['add_info4'] . "%" .
                $data['add_info5'] . "%" .
                $data['amount'] . "%" .
                $data['cust_email'] . "%" .
                $data['cust_id'] . "%" .
                $data['cust_msisdn'] . "%" .
                $data['cust_name'] . "%" .
                $data['return_url'] . "%" .
                $data['invoice'] . "%" .
                $data['items'] . "%" .
                $data['merchant_id'] . "%" .
                $data['success_url'] . "%" .
                $data['failed_url'] . "%" .
                $data['timeout'] . "%" .
                $data['trans_date'];
                
        $sign = strtoupper($sign) . "%" . $this->config("merchant.key");

        $sign = hash('sha256', $sign);

        return strtoupper($sign);
    }
}