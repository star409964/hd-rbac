<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/1
 * Time: 11:58
 */

namespace mikkle\tp_alipay\src\auth;


use mikkle\tp_alipay\base\AlipayClientBase;
use mikkle\tp_alipay\base\Tools;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

class OrderPay extends AlipayClientBase
{
    protected  $method = "alipay.trade.pay";
    protected $isDebug =true;
    protected $paramList = ["app_id","notify_url"];

    protected $bizContentList =[
        "out_trade_no",
        "buyer_id",
        "seller_id",
        "total_amount",
        "auth_no"
    ];

    protected function buildPublicBizContentParam()
    {
        $publicParam = [
            "product_code"=>"PRE_AUTH",
            //  "seller_id"=>"",
        ];
        $this->bizContent = array_merge($this->bizContent ,$publicParam ) ;
    }






}