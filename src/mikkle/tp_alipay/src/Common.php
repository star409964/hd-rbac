<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\9 0009
 * Time: 17:05
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\AlipayClientBase;

/**
 * 预支付 生成支付二维码
 * Class Precreate
 * @package mikkle\tp_alipay\src
 */
class Common extends AlipayClientBase
{
    protected $method = "";
    protected $isDebug =true;
    protected $paramList = ["app_id","method"];

    protected $bizContentList =[



    ];




    protected function buildPublicBizContentParam()
    {
//        $publicParam = [
//            "product_code"=>"QUICK_WAP_WAY",
//            //  "seller_id"=>"",
//        ];
//        $this->bizContent = array_merge($this->bizContent ,$publicParam ) ;
    }

    public function setMethod($method){
        $this->method = $method;
        return $this ;
    }





}