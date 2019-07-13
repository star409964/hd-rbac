<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16
 * Time: 19:28
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

class RedPack extends WxpayClientBase
{
    protected $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";

    protected function checkParams()
    {
        if(!isset( $this->params["send_name"])  || empty(  $this->params["send_name"]  ) )
        {
            throw new Exception("红包接口中，缺少必填参数send_name！"."<br>");
        }
        if(!isset( $this->params["mch_billno"])  || empty(  $this->params["mch_billno"]  ) )
        {
            throw new Exception("红包接口中，缺少必填参数 mch_billno！"."<br>");
        }
        if(!isset( $this->params["re_openid"])  || empty(  $this->params["re_openid"]  ) )
        {
            throw new Exception("红包接口中，缺少必填参数 re_openid！"."<br>");
        }
        if(!isset( $this->params["total_amount"])  || empty(  $this->params["total_amount"]  ) )
        {
            throw new Exception("红包接口中，缺少必填参数 total_amount！"."<br>");
        }

        if (!isset( $this->params["scene_id"]  )){
            $this->params["scene_id"] = "PRODUCT_".array_rand([2=>2,4=>4,5=>5]);
        }
        if (!isset( $this->params["client_ip"]  )){
            $this->params["client_ip"]="192.168.1.1";
        }
        if (!isset( $this->params["remark"]  )){
            $this->params["remark"]="红包大派送,多到你想不到";
        }
        if (!isset( $this->params["act_name"]  )){
            $this->params["act_name"]="红包大派送";
        }
        if (!isset( $this->params["total_num"]  )){
            $this->params["total_num"]=1;
        }
    }


    public function setRedpackDate($mch_billno,$re_openid, $total_amount ,$send_name){
        $this->setParam([
            "mch_billno"=>$mch_billno,
            "re_openid"=>$re_openid,
            "total_amount"=>$total_amount,
            "send_name"=>$send_name,
        ]);
        return $this;
    }


    protected function createXml()
    {
        $this->checkParams();
        $this->params["wxappid"] = $this->options["appid"];                  //公众账号ID
        $this->params["mch_id"] =   $this->options["mch_id"];           //商户号
        $this->params["nonce_str"] = Tools::createNonceStr();//随机字符串
        if (isset($this->params["sign"] )){
            unset( $this->params["sign"]  );
        }
        $this->params["sign"] = Tools::getSignByKey($this->params,$this->options["key"]);//签名
        return  Tools::arrayToXml($this->params);
    }


    public function payToUser(){
        $this->getResultBySSLCurl();
        return $this->result;
    }

}