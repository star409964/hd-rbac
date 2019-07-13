<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/7/7
 * Time: 21:11
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\AlipayClientBase;
use mikkle\tp_alipay\base\Tools;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

class OrderSettle extends AlipayClientBase
{
    protected  $method = "alipay.trade.order.settle";
    protected $isDebug =true;
    protected $paramList = ["app_id",];
    protected $bizContentList =[
        "out_request_no", //
        "trade_no", //订单号
        "royalty_parameters",
    ];


    protected function buildPublicBizContentParam()
    {
        $publicParam = [
          //  "seller_id"=>"",
        ];
        $this->bizContent = array_merge($this->bizContent ,$publicParam ) ;
    }

    public function setSettleParams( $trans_out ,$trans_in , $trade_no ,$out_request_no = "" ,$amount = 0 ,$amount_percentage = 100 ){

        if (substr( $trans_out,0,4 )!=2088 || substr( $trans_in ,0,4 )!=2088 ){
            throw new Exception("请求的支付宝UserId不正确");
        }
        if (empty( $out_request_no )){
            $out_request_no = time()+rand(1000,9999);
        }
        $royalty_parameters=[
            "trans_out"=>$trans_out,
            "trans_in"=>$trans_in,
        ];
        if ( empty( $amount ) && empty( $amount_percentage )){
            throw new Exception("请求的分账参数不正确");
        }elseif( $amount ){
            $royalty_parameters["amount"] = $amount;
        }elseif( $amount_percentage ){
            $royalty_parameters["amount_percentage"]=$amount_percentage ;
        }
        $this->setBizContentParam([
            "out_request_no"=>$out_request_no,
            "trade_no"=>$trade_no,
        ]);
        $this->bizContent["royalty_parameters"] =[$royalty_parameters] ;
        return $this;
    }



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