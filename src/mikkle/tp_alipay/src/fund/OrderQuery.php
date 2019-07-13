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

class OrderQuery extends AlipayClientBase
{
    protected  $method = "alipay.fund.coupon.operation.query";
    protected $isDebug =true;
    protected $paramList = ["app_id"];

    protected $bizContentList =[

        "out_order_no", //订单号
    ];



    public function setQueryOutTradeNo($out_order_no){
        $this->setBizContentParam("out_order_no",$out_order_no);
        return $this ;
    }

}