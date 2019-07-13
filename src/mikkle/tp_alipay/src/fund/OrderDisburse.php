<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/1
 * Time: 11:58
 */

namespace mikkle\tp_alipay\src\fund;


use mikkle\tp_alipay\base\AlipayClientBase;
use mikkle\tp_alipay\base\Tools;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;
class OrderDisburse extends AlipayClientBase
{
    protected  $method = "alipay.fund.coupon.order.disburse";
    protected $isDebug =true;
    protected $paramList = ["app_id"];

    protected $bizContentList =[
        "order_title", //
        "out_order_no", //订单号
        "amount",
    ];


    public function setTransferBizContentParam($payee_account,$amount,$out_order_no,$deduct_auth_no){

        $this->setBizContentParam([
            "payee_logon_id"=>(string)$payee_account,
            "amount"=>(string)$amount,
            "out_order_no"=>(string)$out_order_no,
            "deduct_auth_no"=>(string)$deduct_auth_no,
            "out_request_no"=>(string)($out_order_no.rand(100000,999999)),
            "order_title"=>"红包".$out_order_no
        ]);
        return $this;
    }




}