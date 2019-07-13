<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 13:06
 */

namespace mikkle\tp_alipay\src\security;


use mikkle\tp_alipay\base\AlipayClientBase;

class CustomerriskQuery extends AlipayClientBase
{
    protected $method = "alipay.security.risk.customerrisk.query";
    protected $isDebug =true;
    protected $paramList = ["app_id","method"];

    protected $bizContentList =[
        "risk_type", //
    ];




    protected function buildPublicBizContentParam()
    {
//        $publicParam = [
//            "product_code"=>"QUICK_WAP_WAY",
//            //  "seller_id"=>"",
//        ];
//        $this->bizContent = array_merge($this->bizContent ,$publicParam ) ;
    }

    /**
     * @title 通过身份证查询
     * @param $cert_no //身份证
     * @return bool|mixed
     */
    public function queryByCertNo($cert_no){
        $this->setBizContentParam([
            "risk_type"=> "riskinfo_cert_no" ,
            "cert_no"=>$cert_no
        ]);
        return $this->getResult();
    }

    public function queryByMobileNo($mobile_no){
        $this->setBizContentParam([
            "risk_type"=> "riskinfo_mobile_no" ,
            "mobile_no"=>$mobile_no
        ]);
        return $this->getResult();
    }

    public function queryByBankCardNo($bank_card_no){
        $this->setBizContentParam([
            "risk_type"=> "riskinfo_bank_card_no" ,
            "bank_card_no"=>$bank_card_no
        ]);
        return $this->getResult();
    }


    public function queryByBusinessLicenseNo($business_license_no){
        $this->setBizContentParam([
            "risk_type"=> "riskinfo_business_license_no" ,
            "business_license_no"=>$business_license_no
        ]);
        return $this->getResult();
    }

    public function queryByCompanyName($company_name){
        $this->setBizContentParam([
            "risk_type"=> "riskinfo_company_name" ,
            "company_name"=>$company_name
        ]);
        return $this->getResult();
    }


}