<?php
require_once("DTFinpayCustomer.php");
require_once("DTFinpayReceiver.php");
require_once("DTFinpay.php");

$finpay = new DTFinpay();
$trackID = date("ymdHi").str_pad(mt_rand(0,1000), 4, "0", STR_PAD_LEFT);
$customer = new DTFinpayCustomer("cust-123", "Didats Triadi", "didats@gmail.com", "0818036581328");
$status = $finpay->initiate($trackID, 
                    "195400", 
                    "Belanja barang test", 
                    ["Test 1", "Test 2", "Test 3", "Test 4", "Test 5"], 
                    $customer);
print_r($status);
exit;
if(isset($status['status_code'])) {
    if((int)$status['status_code'] == 0) {
        $url = $status['landing_page'];
        echo $url;
    } else {
        echo "Failed payment";
    }
}