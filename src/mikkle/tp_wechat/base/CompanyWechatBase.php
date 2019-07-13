<?php
/**
 * Created by PhpStorm.
 * User: wangliang
 * Date: 2018/11/1
 * Time: 3:28 PM
 */

namespace mikkle\tp_wechat\base;

use mikkle\tp_model\CompanyAuthWechatInfo;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Request;
use think\facade\Log;
use mikkle\tp_master\Exception;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_wechat\support\StaticFunction;
use mikkle\tp_wechat\support\Prpcrypt;

class CompanyWechatBase
{
    protected $appId;
    protected $secret;
    protected $encodingAesKey;
    protected $token;
    protected $error = [];

    protected $prefix = 'mike_component_access_token.';
    protected $company_verify_ticket_prefix = 'company_verify_ticket_prefix.';
    protected $company_auth_code_prefix = 'company_auth_code_prefix.';
    protected $cacheKey;
    protected $cache;
    protected $access_token;
    protected $retry = false;
    public $errCode = 0;
    public $errMsg = "";

    const API_TOKEN_POST = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';//use

    const PRE_AUTH_CODE_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=';

    const API_QUERY_AUTH_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=';

    const API_AUTHORIZER_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=';
    //获取授权方的帐号基本信息
    const API_GET_AUTHORIZER_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=';
    //获取授权方的选项设置信息
    const API_GET_AUTHORIZER_OPTION_URL = 'https://api.weixin.qq.com/cgi-bin/component/ api_get_authorizer_option?component_access_token=';


    public function __construct($options = [])
    {
        if (empty($options) && !empty(Config::get("wechat.company_wechat.default_options_name"))) {
            $options = Config::get("wechat.company_wechat.default_options_name");
        }
        $this->appId = $options["appid"];
        $this->secret = $options["appsecret"];
        $this->encodingAesKey = $options["encodingAesKey"];
        $this->token = $options["token"];
        $this->cacheKey = $this->prefix . $options["appid"];

    }

    /**
     * 在第三方平台创建审核通过后，微信服务器会向其“授权事件接收URL”每隔10分钟定时推送component_verify_ticket。第三方平台方在收到ticket推送后也需进行解密（详细请见【消息加解密接入指引】），接收到后必须直接返回字符串success。
     */
    public function acceptComponentVerifyTicket()
    {
        try {
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            Cache::set('otherAcceptInfo',$postStr);
            $getData = Request::get();
            $toData = StaticFunction::xml2arr($postStr);
            $pr = new Prpcrypt($this->encodingAesKey, $this->token);
            $msg = [];
            $ret = $pr->decryptMsg($getData['msg_signature'], $getData['timestamp'], $getData['nonce'], $toData, $msg);
            if ($ret['code'] == 'T') {
                $decodeData = StaticFunction::xml2arr($msg[1]);
                //TODO 根据推送过来的不同值，进行相应的处理
                if ($decodeData['InfoType'] == 'component_verify_ticket') {
                    Cache::set($this->company_verify_ticket_prefix, $decodeData, 0);
                } else {
                    $this->acceptInfoType($decodeData);
                }
            }
            echo 'success';
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * 根据不同的InfoType 执行不同的操作
     * @param $data
     */
    protected function acceptInfoType($data)
    {
        //孩子需要继承这个函数-进行处理其他的微信推送消息
        if(method_exists($this,'handelAcceptInfoType')){
            $this->handelAcceptInfoType($data);
        }

    }


    /**
     * @return mixed 票据
     */
    public function getComponentVerifyTicket()
    {
        $componentVerifyData = Cache::get($this->company_verify_ticket_prefix);
        /*
        array(4) {
                  ["AppId"] => string(18) "wxa09e5355d8a4c635"
                  ["CreateTime"] => string(10) "1541139016"
                  ["InfoType"] => string(23) "component_verify_ticket"
                  ["ComponentVerifyTicket"] => string(95) "ticket@@@Rs7Q7ASGPuthvVUtE-yL7IACgMcgPHBVeCshvitXY4YQ8VWXnXEtiBg4TFUGLdQwyZHCvSv6BhdR-qbZiWlenw"
                }
        */
        return $componentVerifyData['ComponentVerifyTicket'];
    }


    /**
     * 获取微信调用自己得到的Token
     * Power: mike
     * Email：star521me
     *
     * @param bool|false $isRefresh
     *
     * @return bool|mixed
     */
    public function getToken($isRefresh = false)
    {
        $cache_key = $this->getCacheKey();
        $cache_token = Cache::get($cache_key);
        if (empty($cache_token) || $isRefresh) {
            $result = $this->getComponentAccessToken();
            $expire = isset($result['expires_in']) ? intval($result['expires_in']) - 1000 : 7000;
            $cache_token = $result["component_access_token"];
            Cache::set($cache_key, $cache_token, $expire);
        }
        if (!$cache_token) {
            return false;
        }
        $this->access_token = $cache_token;
        return $cache_token;
    }

    /*
     * {"component_access_token":"61W3mEpU66027wgNZ_MhGHNQDHnFATkDa9-2llqrMBjUwxRSNPbVsMmyD-yq8wZETSoE5NQgecigDrSHkPtIYA", "expires_in":7200}
     */
    protected function getComponentAccessToken()
    {
        if (empty($this->appId) || empty($this->secret)) {
            $this->error[] = "参数丢失";
            return false;
        }
        $url_getToken = self::API_TOKEN_POST;
        $data['component_appid'] = $this->appId;
        $data['component_appsecret'] = $this->secret;
        $data['component_verify_ticket'] = $this->getComponentVerifyTicket();
        $result = StaticFunction::parseJSON(Curl::curlPost($url_getToken, StaticFunction::jsonEncode($data)));
        if (!$result || isset($result['errcode'])) {
            Log::error("请求数据接口出错:code[{$result['errcode']}],{$result['errmsg']}");
            $this->error[] = $result['errmsg'];
            return false;
        }
        return $result;
    }

    /**
     * @param bool $isRefresh
     *
     * @return bool|mixed
     */
    public function getAuthCode($isRefresh = false)
    {
        $cache_key = $this->company_auth_code_prefix;
        $cache_token = Cache::get($cache_key);
        if (empty($cache_token) || $isRefresh) {
            $result = $this->getPreAuthCode();
            $expire = isset($result['expires_in']) ? intval($result['expires_in']) - 100 : 500;
            $cache_token = $result["pre_auth_code"];
            Cache::set($cache_key, $cache_token, $expire);
        }
        if (!$cache_token) {
            return false;
        }
        return $cache_token;
    }

    /**
     * 调用微信 获取预授权码pre_auth_code
     */
    protected function getPreAuthCode()
    {
        $url = self::PRE_AUTH_CODE_URL . $this->getToken();
        $data['component_appid'] = $this->appId;
        $result = StaticFunction::parseJSON(Curl::curlPost($url, StaticFunction::jsonEncode($data)));
        if (!$result || isset($result['errcode'])) {
            Log::error("请求数据接口出错:code[{$result['errcode']}],{$result['errmsg']}");
            $this->error[] = $result['errmsg'];
            return false;
        }
        return $result;
    }

    /**
     * 1.获取跳转到授权的url地址上
     *
     * @param $auth_type 1则商户点击链接后，手机端仅展示公众号、2表示仅展示小程序，3表示公众号和小程序都展示。如果为未指定，则默认小程序和公众号都展示
     * @param $redirect_uri 回调的url
     *
     * @return string
     */
    public function getAuthUrl($auth_type, $redirect_uri)
    {
        $appid = $this->appId;
        $pre_auth_code = $this->getAuthCode();
        $url = "https://mp.weixin.qq.com/safe/bindcomponent?action=bindcomponent&auth_type=3&no_scan=1&component_appid=$appid&pre_auth_code=$pre_auth_code&redirect_uri=https://txy5.sylyx.cn/wechat/index/resultAuth&auth_type=3#wechat_redirect";
        return $url;
    }

    /**
     * 2.回调的函数,上面的地址一定回调到这个函数上
     * @return bool|mixed 公众号的基础信息
     */
    public function ApiQueryAuth()
    {
        $getData = Request::param();
        $auth_code = $getData['auth_code'];
        $expires_in = $getData['expires_in'];
        $resutl = $this->getCompanyInfo($auth_code);
        return $resutl;
    }

    /**
     * 使用授权码换取公众号或小程序的接口调用凭据和授权信息
     *
     * @param $authorization_code 授权码
     *
     * @return bool|mixed
     */
    protected function getCompanyInfo($authorization_code)
    {
        $url = self::API_QUERY_AUTH_URL . $this->getToken();
        $data['component_appid'] = $this->appId;
        $data['authorization_code'] = $authorization_code;
        $result = StaticFunction::parseJSON(Curl::curlPost($url, StaticFunction::jsonEncode($data)));
        if (!$result || isset($result['errcode'])) {
            Log::error("请求数据接口出错:code[{$result['errcode']}],{$result['errmsg']}");
            $this->error[] = $result['errmsg'];
            return false;
        }
        return $result;
    }

    /**
     * 3.刷新用户的表单令牌
     *
     * @param $authorizer_appid 授权方appid
     * @param $authorizer_refresh_token 授权方的刷新令牌
     *
     * @return bool|mixed
     * {
     * "authorizer_access_token": "aaUl5s6kAByLwgV0BhXNuIFFUqfrR8vTATsoSHukcIGqJgrc4KmMJ-JlKoC_-NKCLBvuU1cWPv4vDcLN8Z0pn5I45mpATruU0b51hzeT1f8",
     * "expires_in": 7200,
     * "authorizer_refresh_token":
     * "BstnRqgTJBXb9N2aJq6L5hzfJwP406tpfahQeLNxX0w"
     * }
     */
    public function authorizerRefreshToken($authorizer_appid, $authorizer_refresh_token)
    {
        $url = self::API_AUTHORIZER_TOKEN_URL . $this->getToken();
        $data['component_appid'] = $this->appId;
        $data['authorizer_appid'] = $authorizer_appid;
        $data['authorizer_refresh_token'] = $authorizer_refresh_token;
        $result = StaticFunction::parseJSON(Curl::curlPost($url, StaticFunction::jsonEncode($data)));
        if (!$result || isset($result['errcode'])) {
            Log::error("请求数据接口出错:code[{$result['errcode']}],{$result['errmsg']}");
            $this->error[] = $result['errmsg'];
            return false;
        }
        return $result;

    }

    public function getAuthorizerInfo($authorization_appid)
    {
        $url = self::API_GET_AUTHORIZER_INFO_URL . $this->getToken();
        $data['component_appid'] = $this->appId;
        $data['authorizer_appid'] = $authorization_appid;
        $result = StaticFunction::parseJSON(Curl::curlPost($url, StaticFunction::jsonEncode($data)));
        if (!$result || isset($result['errcode'])) {
            Log::error("请求数据接口出错:code[{$result['errcode']}],{$result['errmsg']}");
            $this->error[] = $result['errmsg'];
            return false;
        }
        return $result;
    }

    /**
     * 获取授权方的选项设置信息
     * Power mike
     *
     * @param $authorization_appid 授权公众号或小程序的appid
     * @param $option_name 选项名称
     *
     * @return bool|mixed
     */
    public function getAuthorizerOption($authorization_appid, $option_name)
    {
        $url = self::API_GET_AUTHORIZER_OPTION_URL . $this->getToken();
        $data['component_appid'] = $this->appId;
        $data['authorizer_appid'] = $authorization_appid;
        $data['option_name'] = $option_name;
        $result = StaticFunction::parseJSON(Curl::curlPost($url, StaticFunction::jsonEncode($data)));
        if (!$result || isset($result['errcode'])) {
            Log::error("请求数据接口出错:code[{$result['errcode']}],{$result['errmsg']}");
            $this->error[] = $result['errmsg'];
            return false;
        }
        return $result;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    protected function getCacheKey()
    {
        if (is_null($this->cacheKey)) {
            return $this->prefix . $this->appId;
        }

        return $this->cacheKey;
    }

    /**
     * 第三方的调用凭证 token
     *
     * @param $authorization_appid 第三方平台的appid
     * @param $isRefresh
     *
     * @return bool|mixed
     */
    public function getComponetToken($component_appid, $isRefresh = false)
    {
        $cache_key = "wexhat_componet_token.$component_appid";
        $cache_token = Cache::get($cache_key);
        if (empty($cache_token) || $isRefresh) {
            $mo = new CompanyAuthWechatInfo();
            $result = $mo->where('authorization_appid', $component_appid)->field('authorization_appid,authorizer_access_token,expires_in,authorizer_refresh_token')->find();
            $newTokenInfo = $this->authorizerRefreshToken($result['authorization_appid'], $result['authorizer_refresh_token']);
            $expire = isset($newTokenInfo['expires_in']) ? intval($result['expires_in']) - 1000 : 7000;
            $cache_token = $newTokenInfo["authorizer_access_token"];
            $mo->save(['authorizer_access_token' => $newTokenInfo["authorizer_access_token"], 'expires_in' => time() + 6200], ['authorization_appid' => $component_appid]);
            Cache::set($cache_key, $cache_token, $expire);
        }
        if (!$cache_token) {
            return false;
        }
        return $cache_token;
    }

    protected function returnGetResult($url, $name, $componet_appid, $arguments)
    {
        $result = Curl::curlGet($url);
        return $this->resultJsonWithRetry($result, $name, $componet_appid, $arguments);
    }

    protected function returnPostResult($url, $data, $name, $componet_appid, $arguments)
    {
        $result = Curl::curlPost($url, $data);
        dump($result);
        return $this->resultJsonWithRetry($result, $name, $componet_appid, $arguments);
    }

    protected function resultJsonWithRetry($result, $name, $componet_appid, $arguments)
    {

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry($name, $componet_appid, $arguments);
            }
            return $json;
        }
        return false;

    }

    protected function resultBoolWithRetry($result, $name, $componet_appid, $arguments)
    {

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry($name, $componet_appid, $arguments);
            }
            return true;
        }
        return false;
    }

    protected function resultCommonRetry($state, $msg = '')
    {
        $code = $state == true ? 'T' : 'F';
        return ['code' => $code, 'msg' => $msg];
    }


    /**
     * 接口失败重试
     *
     * @param $method   SDK方法名称
     * @param array $arguments SDK方法参数
     *
     * @return bool|mixed
     */
    protected function checkRetry($method, $componet_appid, $arguments = array())
    {
        if (!$this->retry && in_array($this->errCode, ['40014', '40001', '41001', '42001'])) {
            Log::notice("Run {$method} Faild. {$this->errMsg}[{$this->errCode}]");
            ($this->retry = true) && $this->getComponetToken($componet_appid, true);
            $this->errCode = 40001;
            $this->errMsg = 'no access';
            Log::notice("Retry Run {$method} ...");
            return call_user_func_array(array($this, $method), $arguments);
        }
        return false;
    }

    /**
     * 数据加密传递给公众号
     * @param $data
     *
     * @return string
     */
    public function msgDecrypt($data){
        $pr = new Prpcrypt($this->encodingAesKey, $this->token);
        $encryptMsg = '';
        $ret = $pr->encryptMsg($data,$this->encodingAesKey,$this->appId,'',$pr->getRandomStr(),$encryptMsg);
        return $encryptMsg;
    }

    /**
     * 微信过来的数据进行解密
     * @return array|bool
     */
    public function msgEncrypt(){
        try {
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

            $getData = Request::get();

            $toData = StaticFunction::xml2arr($postStr);

            $toData['AppId'] = $this->getAppId();

            $pr = new Prpcrypt($this->encodingAesKey, $this->token);
            $msg = [];
            $ret = $pr->decryptMsg($getData['msg_signature'], $getData['timestamp'], $getData['nonce'], $toData, $msg);

            if ($ret['code'] == 'T') {
                $decodeData = StaticFunction::xml2arr($msg[1]);
                //TODO 根据推送过来的不同值，进行相应的处理
                return $decodeData;
            }else{
                return false;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }


    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if ($this->error) {
            Log::error($this->error);
        }
    }
}