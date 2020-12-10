<?php
/**
 * DTFinpay: Finpay payment customer class
 * @since December 09th, 2020
 * @version 1.0
 * @link http://didats.net
 * @author Didats Triadi <didats@gmail.com>
 *
 */
class DTFinpayCustomer {
    public $id, $email, $name, $phone;
    public function __construct(string $id, string $name, string $email, string $phone) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->phone = $phone;
    }
}