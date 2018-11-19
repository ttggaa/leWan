<?php
namespace app\api\controller;

use app\api\model\NotifyModel;
use app\api\model\OpenTmModel;
use app\common\BaseController;
use app\common\model\PayMethod;
use think\Request;
use think\Db;

/**
 * 支付回调
 * Enter description here ...
 * @author Administrator
 *
 */
class NotifyController extends BaseController
{

    private $paymethod;
    private $WxPay;
    private $notifyparams;

    public function __construct()
    {
        parent::__construct();
        $payway = $this->get('payway','');
        if($payway == PayMethod::WxJSApi){
            $this->paymethod = PayMethod::WxJSApi;
            Vendor('WxPay.WxPay#Config');
            Vendor('WxPay.WxPay#Api');
            $this->WxPay = new \WxPayNotifyResults();
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            if(!$xml){
                $xml = file_get_contents('php://input');
            }
            file_put_contents('./log.txt', "\r\nxml:".$xml, FILE_APPEND);
            try {
                $this->notifyparams = \WxPayNotifyResults::Init(new \WxPayConfig(), $xml)->GetValues();
            }catch (\WxPayException $e){
                file_put_contents('./log.txt', "\r\nexception:".$e->getMessage(), FILE_APPEND);
                $this->output('FAIL', PayMethod::WxJSApi);
            }
            //$this->notifyparams = notifytest();
        }
    }

    private function output($status='FAIL'){
        if($this->paymethod == PayMethod::AlipayNative){
            //$this->alieturn(strtolower($status));
        }elseif($this->paymethod == PayMethod::WxJSApi){
            $this->wxreturn($status);
        }elseif($this->paymethod == PayMethod::WxAppNative){
            $this->wxreturn($status);
        }
    }

    /**
     * 微信支付回复通知
     * @param $status 状态
     */
    private function wxreturn($status='FAIL'){
        $this->WxPay->SetData('return_code', $status);
        echo $this->WxPay->ToXml();
        exit;
    }

    /*  ----------------------业务部分----------------------------  */
    /**
     * 商城购物通知
     */
    public function mall(){
        $order = Db::name('order o')
                ->field('o.*, p.product_returnall, p.op_id, p.commission, p.num, p.product_startusetime, p.product_endusetime')
                ->join('jay_order_product p', 'p.order_id = o.order_id', 'left')
                ->where(['order_no'=>$this->notifyparams['out_trade_no']])->find();
        if($order){
            if($order['order_status'] == 1){
                $nm = new NotifyModel();
                Db::startTrans();
                $res = $nm->notifyMall($order, $this->notifyparams, $this->paymethod, $this->CFG);
                if($res){
                    Db::commit();
                    $this->output('SUCCESS');
                }else{
                    Db::rollback();
                    $this->output();
                }
            }else{
                $this->output();
            }
        }else{
            $this->output();
        }
    }


    /**
     * 预约加价回调
     */
    public function yuyue(){
        $order = Db::name('order_user_reservation')->where(['reservation_no'=>$this->notifyparams['out_trade_no']])->find();
        if($order){
            if($order['reservation_status'] == 0){
                $data['reservation_transaction_id'] = $this->notifyparams['transaction_id'];
                $data['reservation_paytime'] = time();
                $data['reservation_status'] = 1;
                $res = Db::name('order_user_reservation')->where(['reservation_no'=>$this->notifyparams['out_trade_no']])->update($data);
                if($res !== false){
                    $this->output('SUCCESS');
                }else{
                    $this->output();
                }
            }else{
                $this->output();
            }
        }else{
            $this->output();
        }
    }



    /**
     * 推送新产品给用户
     * @return bool
     */
    public function sendMsgToWechat(){
        $product_id =  input('id', 0);
        $host =  $_SERVER['HTTP_HOST']?$_SERVER['HTTP_HOST']: $_SERVER['SERVER_NAME'];
        if($product_id){
            $productName = Db::name('product')->where(['product_id'=>$product_id])->value('product_name');
            if($productName){
                $data['title'] = '最新爆品推荐';
                $data['keyword1'] = '爆品推荐';
                $data['keyword2'] = $productName;
                $data['keyword3'] = date('Y-m-d H:i:s');
                $data['keyword4'] = '爆品推荐';
                $data['remark'] = '点击查看详情';
            }else{
                return false;
            }
            $url = $host. '/wechat_html/page/homePage/productDetails.html?productId='.$product_id;
            $accessToken = Db::name('access_token')->value('access_token');
            //获取所有微信用户openid
            $openidList = Db::name("user_connect")->where(array('platform'=>'wechat'))->field('openid')->select();
            foreach ($openidList as $val){
                if(isset($val['openid']) && $val['openid']){
                    //发送消息给每个微信用户
                    OpenTmModel::sendTplmsg6($val['openid'],$data,$accessToken,$url);
                }
            }
            return true;
        }
        return false;
    }

}
