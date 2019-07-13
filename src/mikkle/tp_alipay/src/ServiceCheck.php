<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/17
 * Time: 19:56
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\Tools;
use mikkle\tp_master\Exception;

class ServiceCheck
{
    protected $options=[];
    public function __construct($options)
    {
        $this->setOptions($options);
        $this->_initialize();
    }
    protected function _initialize()
    {

        ;

    }

    protected function setOptions($options=[]){
        if ( !empty( $options)&& is_array( $options ) ){
            $this->options=  array_merge($this->options,$options);

        }else{
            throw  new  Exception("缺失重要的参数对象");
        }
        if (empty($this->options)){
            throw  new  Exception("参数缺失");
        }
    }


    public function getCheckXml($pub_key,$pri_key){
        $sign = $this->getRsaSign($pub_key,$pri_key);
        return "<?xml version=\"1.0\" encoding=\"GBK\"?>
<alipay>
    <response>
        <biz_content>$pub_key</biz_content>
        <success>true</success>
    </response>
    <sign>$sign</sign>
    <sign_type>RSA2</sign_type>
</alipay>";
    }



    public function getRsaSign($public_key,$pri_key){
        return Tools::getRsaSign("<biz_content>$public_key</biz_content><success>true</success>",$pri_key);
    }

}