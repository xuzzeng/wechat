<?php

namespace Interfaces\Controller;

use Think\Controller;
use Think\Log;
use Interfaces\Common\WXBizMsgCrypt;
/**
 * 接口控制类
 */
class MyHospitalController extends Controller {
    
       public function index() {
       // 假设企业号在公众平台上设置的参数如下
        $encodingAesKey = "tuFUvyFGVL2no3y51QOQgdVqhvIRmDUUbF1lKemMFb0";
        $token = "lpsgBuOaGJ183";
        $corpId = "wx519046bea559a1f8";        
         //企业号的消息处理
//       if($_SERVER['REQUEST_METHOD']=='POST'){
//            Log::write("request method is :".$_SERVER['REQUEST_METHOD']);
////            Log::write("===============");
//            $signature = I('post.msg_signature');   
//            $timestamp= I('post.timestamp');
//            $nonce = I('post.nonce');
//            $sMsg = "";
//           //根据参数信息，初始化微信对应的消息加密解密类
//            $wxcpt = new WXBizMsgCrypt();
//            $wxcpt->set($token, $encodingAesKey, $corpId);        
//            $resultCode = $wxcpt->DecryptMsg($signature,$timestamp,$nonce,$_POST,$sMsg);
//            //返回成功
//            if($resultCode==0){
//                  Log::write($sMsg);
//                  Log::write("处理企业信息成功!");
//                  echo $resultCode;                 
//            }else{
//                Log::write("企业信息处理失败!");
//                print("Result: " . $resultCode . "\n\n");
//            }
//        } 
        
        // $sVerifyMsgSig = HttpUtils.ParseUrl("msg_signature");
        $sVerifyMsgSig = I('get.msg_signature');
        // $sVerifyTimeStamp = HttpUtils.ParseUrl("timestamp");
        $sVerifyTimeStamp = I('get.timestamp');
        // $sVerifyNonce = HttpUtils.ParseUrl("nonce");
        $sVerifyNonce = I('get.nonce');
        // $sVerifyEchoStr = HttpUtils.ParseUrl("echostr");
        $sVerifyEchoStr = I('get.echostr');
        Log::write("=====sVerifyEchoStr====".$sVerifyEchoStr);
        // 需要返回的明文
        $EchoStr = "";
        $wxcpt = new WXBizMsgCrypt();
      
        $wxcpt->set($token, $encodingAesKey, $corpId);       
        $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr); 
        Log::write("~~~~~~".$errCode."~~~~~~~~~~");        
        Log::write("get_msg_signature:".$sVerifyMsgSig);
        if ($errCode == 0) {
          Log::write("request is ok!");
          Log::write($sEchoStr);
          echo $sEchoStr;
          exit;
        } else {          
                Log::write("request is fail!");
                print("ERR: " . $errCode . "\n\n");
        }        

    }
    //生成我的医院应用的菜单
        public function createmenu() {        
         //参数一：北京企业号id,参数二：企业应用的secret
       $appid = C("wechat_xuzeng.CorpID");
        $accessToken = getAccessToken($appid,C("wechat_xuzeng.Secret"));
        $url = "https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=$accessToken&agentid=16";        
        $data ='{
               "button": [
                  {
                      "type":"view",
                      "name":"影像列表",
                      "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri=http://webtest.rimag.com.cn/interfaces/MyHospital/getDoctorPhone&response_type=code&scope=SCOPE&state=STATE#wechat_redirect"
                  }
               ]
           }';
        $obj2 =json_decode(publicCurl($data, $url, $second = 30,1,"json"));
        print_r($obj2);
        exit;
       return $obj2;
    }
    
    //获取医生电话号码
    public function getDoctorPhone() {        
         $code = I('get.code');
         $appid = C("wechat_xuzeng.CorpID");
         $accessToken = getAccessToken($appid, C("wechat_xuzeng.Secret"));
         $url="https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=$accessToken&code=$code";
         $result = json_decode(publicCurl(null, $url, $second=30,0),TRUE);
         $userPhone = $result['UserId'];
         $viewUser = new \Common\Logic\ViewUserLogic();
         $userInfo = $viewUser->findInfobyCondition(array('tel'=>$userPhone), 1, null, null, "hospitalid", "hospitalid");
         $requestUrl ="http://web.rimag.com.cn/interfaces/index/getHospitalImageList?hospid=".$userInfo[0]["hospitalid"];
         header("Location:$requestUrl");
         exit();     
    }
}
