<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/12
 * Time: 13:53
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\CompanyWechatBase;
use mikkle\tp_wechat\support\Curl;

class CompanyDevice extends CompanyWechatBase
{

    const SHAKEAROUND_DEVICE_APPLYID = '/shakearound/device/applyid?'; //申请设备ID
    const SHAKEAROUND_DEVICE_APPLYSTATUS = '/shakearound/device/applystatus?'; //查询设备ID申请审核状态
    const SHAKEAROUND_DEVICE_UPDATE = '/shakearound/device/update?'; //编辑设备信息
    const SHAKEAROUND_DEVICE_SEARCH = '/shakearound/device/search?'; //查询设备列表
    const SHAKEAROUND_DEVICE_BINDLOCATION = '/shakearound/device/bindlocation?'; //配置设备与门店ID的关系
    const SHAKEAROUND_DEVICE_BINDPAGE = '/shakearound/device/bindpage?'; //配置设备与页面的绑定关系
    const SHAKEAROUND_MATERIAL_ADD = '/shakearound/material/add?'; //上传摇一摇图片素材
    const SHAKEAROUND_PAGE_ADD = '/shakearound/page/add?'; //增加页面
    const SHAKEAROUND_PAGE_UPDATE = '/shakearound/page/update?'; //编辑页面
    const SHAKEAROUND_PAGE_SEARCH = '/shakearound/page/search?'; //查询页面列表
    const SHAKEAROUND_PAGE_DELETE = '/shakearound/page/delete?'; //删除页面
    const SHAKEAROUND_USER_GETSHAKEINFO = '/shakearound/user/getshakeinfo?'; //获取摇周边的设备及用户信息
    const SHAKEAROUND_STATISTICS_DEVICE = '/shakearound/statistics/device?'; //以设备为维度的数据统计接口
    const SHAKEAROUND_STATISTICS_PAGE = '/shakearound/statistics/page?'; //以页面为维度的数据统计接口



    public function  __construct(array $option)
    {
        parent::__construct($option);
        $this->getToken();
    }


    /**
     * 申请设备ID
     * @param array $data
     * @return bool|array
     */
    public function applyShakeAroundDevice($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_APPLYID . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 查询设备ID申请审核状态
     * @param int $apply_id
     * @return bool|array
     */
    public function applyStatusShakeAroundDevice($apply_id)
    {
        if (!$this->access_token  || empty($apply_id)) {
            return false;
        }
        $data = ["apply_id" => $apply_id];

         $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_APPLYSTATUS . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 编辑设备信息
     * @param array $data
     * @return bool
     */
    public function updateShakeAroundDevice($data)
    {
        if (!$this->access_token  || empty($data)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_UPDATE ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;
        return $this->resultBoolWithRetry($result,__FUNCTION__, func_get_args());
    }


    /**
     * 查询设备列表
     * @param $data
     * @return bool|array
     */
    public function searchShakeAroundDevice($data)
    {
        if (!$this->access_token  || empty($data)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_SEARCH . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 配置设备与门店的关联关系
     * @param string $device_id 设备编号，若填了UUID、major、minor，则可不填设备编号，若二者都填，则以设备编号为优先
     * @param int $poi_id 待关联的门店ID
     * @param string $uuid UUID、major、minor，三个信息需填写完整，若填了设备编号，则可不填此信息
     * @param int $major
     * @param int $minor
     * @return bool|array
     */
    public function bindLocationShakeAroundDevice($device_id, $poi_id, $uuid = '', $major = 0, $minor = 0)
    {
        if (!$this->access_token  || empty($device_id)|| empty($poi_id)) {
            return false;
        }
        if (!$device_id) {
            if (!$uuid || !$major || !$minor) {
                return false;
            }
            $device_identifier = ['uuid' => $uuid, 'major' => $major, 'minor' => $minor];
        } else {
            $device_identifier = ['device_id' => $device_id];
        }
        $data = ['device_identifier' => $device_identifier, 'poi_id' => $poi_id];


        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_BINDLOCATION . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 配置设备与其他公众账号门店的关联关系
     * @param type $device_identifier 设备信息
     * @param type $poi_id 待关联的门店ID
     * @param type $poi_appid 目标微信appid
     * @return boolean
     */
    public function bindLocationOtherShakeAroundDevice($device_identifier, $poi_id, $poi_appid)
    {
        if (!$this->access_token  || empty($device_identifier)  || empty($poi_id)  || empty($poi_appid)) {
            return false;
        }
        $data = ['device_identifier' => $device_identifier, 'poi_id' => $poi_id, "type" => 2, "poi_appid" => $poi_appid];


        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_BINDLOCATION . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 配置设备与页面的关联关系
     * @param string $device_id 设备编号，若填了UUID、major、minor，则可不填设备编号，若二者都填，则以设备编号为优先
     * @param array $page_ids 待关联的页面列表
     * @param int $bind 关联操作标志位， 0 为解除关联关系，1 为建立关联关系
     * @param int $append 新增操作标志位， 0 为覆盖，1 为新增
     * @param string $uuid UUID、major、minor，三个信息需填写完整，若填了设备编号，则可不填此信息
     * @param int $major
     * @param int $minor
     * @return bool|array
     */
    public function bindPageShakeAroundDevice($device_id, $page_ids = array(), $bind = 1, $append = 1, $uuid = '', $major = 0, $minor = 0)
    {
        if (!$this->access_token || empty($device_id)) {
            return false;
        }
        if (!$device_id) {
            if (!$uuid || !$major || !$minor) {
                return false;
            }
            $device_identifier = ['uuid' => $uuid, 'major' => $major, 'minor' => $minor];
        } else {
            $device_identifier = ['device_id' => $device_id];
        }
        $data = ['device_identifier' => $device_identifier, 'page_ids' => $page_ids, 'bind' => $bind, 'append' => $append];


        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_BINDPAGE . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }


    /**
     * 上传在摇一摇页面展示的图片素材
     * @param array $file_url
     * @return bool|array
     */
    public function uploadShakeAroundMedia($file_url)
    {
        if (!$this->access_token  || empty($file_url)) {
            return false;
        }

        $data['media'] = Curl::getCurlFileMedia($file_url);
        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_MATERIAL_ADD . "access_token={$this->access_token}";
        $result = Curl::CurlFile($curl_url, $data);
        return $this->resultJsonWithRetry($result,__FUNCTION__, func_get_args());

    }


    /**
     * 增加摇一摇出来的页面信息
     * @param string $title 在摇一摇页面展示的主标题，不超过6 个字
     * @param string $description 在摇一摇页面展示的副标题，不超过7 个字
     * @param string $icon_url 在摇一摇页面展示的图片， 格式限定为：jpg,jpeg,png,gif; 建议120*120 ， 限制不超过200*200
     * @param string $page_url 跳转链接
     * @param string $comment 页面的备注信息，不超过15 个字,可不填
     * @return bool|array
     */
    public function addShakeAroundPage($title, $description, $icon_url, $page_url, $comment = '')
    {
        if (!$this->access_token  || empty($title) || empty($description) || empty($icon_url) || empty($page_url)) {
            return false;
        }
        $data = ["title" => $title, "description" => $description, "icon_url" => $icon_url, "page_url" => $page_url, "comment" => $comment];


        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_ADD . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }


    /**
     * 编辑摇一摇出来的页面信息
     * @param int $page_id
     * @param string $title 在摇一摇页面展示的主标题，不超过6 个字
     * @param string $description 在摇一摇页面展示的副标题，不超过7 个字
     * @param string $icon_url 在摇一摇页面展示的图片， 格式限定为：jpg,jpeg,png,gif; 建议120*120 ， 限制不超过200*200
     * @param string $page_url 跳转链接
     * @param string $comment 页面的备注信息，不超过15 个字,可不填
     * @return bool|array
     */
    public function updateShakeAroundPage($page_id, $title, $description, $icon_url, $page_url, $comment = '')
    {
        if (!$this->access_token  || empty($page_id) || empty($title) || empty($description) || empty($icon_url) || empty($page_url)) {
            return false;
        }
        $data = ["page_id" => $page_id, "title" => $title, "description" => $description, "icon_url" => $icon_url, "page_url" => $page_url, "comment" => $comment];

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_UPDATE . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }


    /**
     * 查询已有的页面
     * @param array $page_ids
     * @param int $begin
     * @param int $count
     * @return bool|mixed
     */
    public function searchShakeAroundPage($page_ids = array(), $begin = 0, $count = 1)
    {
        if (!$this->access_token ) {
            return false;
        }
        if (!empty($page_ids)) {
            $data = ['page_ids' => $page_ids];
        } else {
            $data = ['begin' => $begin, 'count' => $count];
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_SEARCH . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }


    /**
     * 删除已有的页面
     * @param array $page_ids
     * @return bool|array
     */
    public function deleteShakeAroundPage($page_ids = array())
    {
        if (!$this->access_token ) {
            return false;
        }
        $data = ['page_ids' => $page_ids];

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_DELETE . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }


    /**
     * 获取设备信息
     * @param string $ticket 摇周边业务的ticket(可在摇到的URL中得到，ticket生效时间为30 分钟)
     * @return bool|array
     */
    public function getShakeInfoShakeAroundUser($ticket)
    {
        if (!$this->access_token  || empty($ticket)) {
            return false;
        }
        $data = ['ticket' => $ticket];

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_USER_GETSHAKEINFO . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }


    /**
     * 以设备为维度的数据统计接口
     * @param int $device_id 设备编号，若填了UUID、major、minor，即可不填设备编号，二者选其一
     * @param int $begin_date 起始日期时间戳，最长时间跨度为30 天
     * @param int $end_date 结束日期时间戳，最长时间跨度为30 天
     * @param string $uuid UUID、major、minor，三个信息需填写完成，若填了设备编辑，即可不填此信息，二者选其一
     * @param int $major
     * @param int $minor
     * @return bool|array
     */
    public function deviceShakeAroundStatistics($device_id, $begin_date, $end_date, $uuid = '', $major = 0, $minor = 0)
    {
        if (!$this->access_token  || empty($device_id) || empty($begin_date) || empty($end_date)) {
            return false;
        }
        if (!$device_id) {
            if (!$uuid || !$major || !$minor) {
                return false;
            }
            $device_identifier = ['uuid' => $uuid, 'major' => $major, 'minor' => $minor];
        } else {
            $device_identifier = ['device_id' => $device_id];
        }
        $data = ['device_identifier' => $device_identifier, 'begin_date' => $begin_date, 'end_date' => $end_date];

        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_STATISTICS_DEVICE . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }


    /**
     * 以页面为维度的数据统计接口
     * @param int $page_id 指定页面的ID
     * @param int $begin_date 起始日期时间戳，最长时间跨度为30 天
     * @param int $end_date 结束日期时间戳，最长时间跨度为30 天
     * @return bool|array
     */
    public function pageShakeAroundStatistics($page_id, $begin_date, $end_date)
    {
        if (!$this->access_token  || empty($page_id) || empty($begin_date) || empty($end_date)) {
            return false;
        }
        $data = ['page_id' => $page_id, 'begin_date' => $begin_date, 'end_date' => $end_date];


        $curl_url = self::API_BASE_URL_PREFIX . self::SHAKEAROUND_STATISTICS_DEVICE . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }




}