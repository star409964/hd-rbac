<?php


namespace mikkle\tp_aliyun;

use GuzzleHttp\Client;
use think\Exception;

class IdcardOcr
{
    static protected $host = "https://yixi.market.alicloudapi.com";
    static protected $path = "/ocr/idcard";
    static protected $method = "POST";
    static protected $appcode = "5241d9efed744c7f9ac35f375f6f79d4";

    public function ocrIdcard1()
    {
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $this::$appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type" . ":" . "application/x-www-form-urlencoded; charset=UTF-8");
        $querys = "";
        $image = base64_encode(file_get_contents('https://shangke2.oss-cn-qingdao.aliyuncs.com/newnsfz/445381199510064031.jpg'));
        $bodys = "image=" . $image . "&side=front";
        $url = $this::$host . $this::$path;


        $config = [
            'base_uri' => $this::$host,

        ];

        try {

            $client = new Client($config);

            $data = [
                'headers'=> [
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Authorization' => "APPCODE " . $this::$appcode
                ],
                'multipart'=>[
                    [
                        'name'=>'image',
                        'contents' => $image . "&side=front",
                    ]
                ]
            ];

//            $response = $client->post($this::$path, $data);
//            $response_code = $response->getStatusCode();
//
//            echo $response_code;
//            $ret = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);




//            dump($ret);
        } catch (Exception $e) {
            dump($e);
        }


        //        $curl = curl_init();
        //        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this::$method);
        //        curl_setopt($curl, CURLOPT_URL, $url);
        //        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        //        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //        curl_setopt($curl, CURLOPT_HEADER, true);
        //        if (1 == strpos("$" . $this::$host, "https://")) {
        //            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //        }
        //        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        //        var_dump(curl_exec($curl));
    }

    public function ocrIdcard(){
        $baseUrl = 'http://120.27.63.37:38880/PIM_IDCard/download';

        $client = new \GuzzleHttp\Client();

        $respont = $client->request('POST',$baseUrl,[
            'multipart'=>[
                [
                    'name'=>'file',
                    'contents'=>fopen('https://shangke2.oss-cn-qingdao.aliyuncs.com/newnsfz/110101200810080529.jpg','r')
                ]
            ]
        ]);


        $ret = json_decode((string)$respont->getBody(),JSON_UNESCAPED_UNICODE);
        return $ret;


    }


}