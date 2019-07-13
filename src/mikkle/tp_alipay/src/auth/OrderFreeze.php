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

class OrderFreeze extends AlipayClientBase
{
    protected  $method = "alipay.fund.auth.order.freeze";
    protected $isDebug =true;
    protected $paramList = ["app_id","notify_url"];

    protected $bizContentList =[

    ];





}