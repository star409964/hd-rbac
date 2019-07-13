<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 8:36
 */

namespace mikkle\tp_wxpay\base;


use mikkle\tp_master\Config;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

class Tools
{

    static public function trimString($value)
    {
        $ret = null;
        if (null != $value)
        {
            $ret = $value;
            if (strlen($ret) == 0)
            {
                $ret = null;
            }
        }
        return $ret;
    }

    static public function encryptByRsa($input,$pubKey ) {

        openssl_public_encrypt($input,$output,$pubKey);
        return base64_encode($output);
    }

    /*
     * 生成签名
     */
    static public function getSignByKey($params,$key)
    {
        $String = self::formatBizQueryParaMap($params);
        return strtoupper(md5( "{$String}&key={$key}"));
    }

    /*
     * 检验签名
     */
    static public function checkSignByKey($data,$key)
    {
        if (!isset( $data['sign'])) return false ;
        $sign = $data['sign'];
        unset($data['sign']);
        return (Tools::getSignByKey($data,$key) == $sign) ? true : false;
    }

    /*
     * 	作用：格式化参数，签名过程需要使用
     */
    static public function formatBizQueryParaMap($paraMap, $urlencode=false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= "&{$k }={$v }";
        }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 1);
        }
        return $reqPar;
    }

    /*
 * 	作用：array转xml
 */
    static public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<{$key}>{$val}</{$key}>";
            }
            else
                $xml.="<{$key}><![CDATA[{$val}]]></{$key}>";
        }
        $xml.="</xml>";
        return $xml;
    }

    static public function arrayToXmlWithoutCDATA($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {

                $xml.="<{$key}>{$val}</{$key}>";
        }
        $xml.="</xml>";
        return $xml;
    }


    /*
     * 	作用：将xml转为array
     */
    static public function xmlToArray($xml)
    {
        $disableLibxmlEntityLoader =libxml_disable_entity_loader(true);
        //将XML转为array
        $re =  json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        libxml_disable_entity_loader($disableLibxmlEntityLoader);
        return $re;
    }

    static public function createNonceStr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }


    /*
     * 	作用：以post方式提交xml到对应的接口url
     */
    static public function postXmlCurl($xml,$url,$second=30,$referer="")
    {
        try{
            //初始化curl
            $ch = curl_init();
            //设置超时
            curl_setopt($ch, CURLOPT_TIMEOUT, $second);
            //这里设置代理，如果有的话
            //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
            //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
            //设置header
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            //要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //post提交方式
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            if ( $referer ){
                curl_setopt ($ch, CURLOPT_REFERER, $referer);
            }
            //运行curl
            $data = curl_exec($ch);
            curl_close($ch);
            //返回结果
            if($data)
            {
                // curl_close($ch);
                return $data;
            }
            else
            {
                $error = curl_errno($ch);
                //  echo "curl出错，错误码:$error"."<br>";
                //    echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
                curl_close($ch);
                return false;
            }
        }catch (Exception $e){
            Log::error($e->getMessage());
            return false;
        }

    }

    /*
     * 	作用：使用证书，以post方式提交xml到对应的接口url
     */
    static public function postXmlSSLCurl($xml,$url,$certPath,$keyPath,$caPath=null,$second=30,$referer="")
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 只信任CA颁布的证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//

        //设置header
        curl_setopt($ch,CURLOPT_HEADER,FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, $certPath );
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, $keyPath);
        if($caPath){
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt ( $ch, CURLOPT_CAINFO, $caPath );
        }
        if ( $referer ){
            curl_setopt ($ch, CURLOPT_REFERER, $referer);
        }

        //post提交方式
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        }
        else {

            $error = curl_errno($ch);
            if ( $error == 52) {
                Log::error("curl出错，:$certPath"."<br>" ) ;
                Log::error("curl出错，:$keyPath"."<br>" ) ;
            }
           // echo "curl出错，错误码:$error"."<br>";
          //  echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    static public function postCurl($url,$param,$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else{
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
//        dump(json_decode($sContent));
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }



    static  public function getRealIp(){
        $ip="null";
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim(current($arr));
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }



}