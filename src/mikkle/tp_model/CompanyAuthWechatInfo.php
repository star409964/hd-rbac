<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/13
 * Time: 10:48
 */

namespace mikkle\tp_model;

use mikkle\tp_model\ModelBase;

class CompanyAuthWechatInfo extends ModelBase
{
    protected $autoWriteTimestamp = 'timestamp';
    protected $type = [
        'func_info'    =>  'json',
        'miniprograminfo'     =>  'json',
        'business_info'     =>  'json',
    ];

    public function saveAuthorizationInfoData($data){
        $auth_info = $data['authorization_info'];
        $sData['authorization_appid'] = $auth_info['authorizer_appid'];
        $sData['authorizer_access_token'] = $auth_info['authorizer_access_token'];
        $sData['authorizer_refresh_token'] = $auth_info['authorizer_refresh_token'];
        $sData['expires_in'] = time()+$auth_info['expires_in']-1000;
        $F = $this->where('authorization_appid',$sData['authorization_appid'])->find();
        if($F){
           $result = $this->save($sData,['authorization_appid'=>$sData['authorization_appid']]);
        }else{
           $result = $this->save($sData);
        }
        return $result;
    }

    public function saveAuthorizationInfoOtherData($data){
        $authorization_info = $data['authorization_info'];
        $authorizer_info = $data['authorizer_info'];

        if(!empty($authorizer_info['MiniProgramInfo'])){
            $sData['miniprograminfo'] = $authorizer_info['MiniProgramInfo'];
        }

        $sData['func_info'] = $authorization_info['func_info'];
        $sData['nick_name'] = $authorizer_info['nick_name'];
        $sData['service_type_info'] = $authorizer_info['service_type_info']['id'];
        $sData['verify_type_info'] = $authorizer_info['verify_type_info']['id'];
        $sData['user_name'] = $authorizer_info['user_name'];
        $sData['alias'] = $authorizer_info['alias'];
        $sData['qrcode_url'] = $authorizer_info['qrcode_url'];
        $sData['business_info'] = $authorizer_info['business_info'];
        $sData['principal_name'] = $authorizer_info['principal_name'];

        $result = $this->save($sData,['authorization_appid'=>$authorization_info['authorizer_appid']]);

        return $result;

    }
    public function updateOneField($field,$value,$where=[]){
         $result = $this->save(array("$field"=>$value),$where);
         return $result;
    }

    /**
     * @name 根据微信的原始id获取到对应的数据库里面的微信数据
     * @param $user_name
     *
     * @return array|null|\PDOStatement|string|\think\Model
     */
    public function useUserNameGetInfo($user_name){
        $info = $this->where(['user_name'=>$user_name])->find();
        return $info;
    }

}