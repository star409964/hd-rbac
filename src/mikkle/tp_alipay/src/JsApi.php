<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/17
 * Time: 16:59
 */

namespace mikkle\tp_alipay\src;


use mikkle\tp_alipay\base\AlipayClientBase;
use mikkle\tp_alipay\base\Tools;

class JsApi extends AlipayClientBase
{
    protected $method="alipay.system.oauth.token";
    protected $paramList = ["app_id"];
    protected $bizContentList =[


    ];

    public function createOauthUrlForAuthCode($redirectUrl,$appId="")
    {
        $urlObj["app_id"] = $appId?$appId : $this->options["app_id"];
        $urlObj["redirect_uri"] = $redirectUrl;
        $urlObj["scope"] = "auth_base";
        $urlObj["state"] = time();
        $bizString = Tools::formatBizQueryParaMap($urlObj, false);
        return "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?".$bizString;
    }

    public function createOauthUrlForUserInfo($redirectUrl,$appId="")
    {
        $urlObj["app_id"] = $appId?$appId : $this->options["app_id"];
        $urlObj["redirect_uri"] = $redirectUrl;
        $urlObj["scope"] = "auth_user";
        $urlObj["state"] = time();
        $bizString = Tools::formatBizQueryParaMap($urlObj, false);
        return "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?".$bizString;
    }



    /*
 * 	作用：通过curl向微信提交code，以获取openid
 */
    function getUserIdByAuthCode( $code )
    {
        $this->setParam([
            "grant_type"=>"authorization_code",
            "code"=>$code,
        ]);
        return $this->getResult();
    }


}