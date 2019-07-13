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

class OrderAppPay extends AlipayClientBase
{
    protected  $method = "alipay.fund.coupon.order.app.pay";
    protected $isDebug =true;
    protected $paramList = ["app_id","notify_url"];

    protected $bizContentList =[
        "order_title", //
        "out_order_no", //订单号
        "amount",
    ];


    public function getQuickPayUrl()
    {
        try {
            $this->initParamsHandle();
            if (!$this->requestList){
                throw new Exception("请求的缺少丢失");
            }
            return $this->gatewayUrl.Tools::formatBizQueryParaMap($this->requestList,true,[]);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Log::error($e->getMessage());
            if ($this->isDebug){
                Log::notice($e->getMessage());
            }
            return false;
        }
    }


}