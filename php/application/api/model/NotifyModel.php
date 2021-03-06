<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/6
 * Time: 18:02
 */

namespace app\api\model;
use app\common\model\AccountRecordModel;
use app\common\model\Currency;
use app\common\model\CurrencyAction;
use app\common\model\PayMethod;
use think\Db;

/**
 * 支付通知业务处理
 * Class NotifyModel
 * @package app\api\model
 */
class NotifyModel
{

    public function notifyMall($order, $notifyparams, $paymethod, $cfg){
        //1.更新订单状态
        $uporder['order_transaction'] = $notifyparams['transaction_id'];
        $uporder['order_paytime'] = time();
        $uporder['order_uptime'] = time();
        $uporder['order_status'] = 2;
        $arm = new AccountRecordModel();
        //支付cash明细
        if($paymethod == PayMethod::WxJSApi || $paymethod == PayMethod::WxAppNative){//微信
            $res5 = $arm->add($order['user_id'], $order['order_id'], Currency::Cash, CurrencyAction::CashRechargeWechatResume, $order['order_payfee'], $arm->getRecordAttach($order['order_id'], $order['order_fullname'], $order['order_no']), '微信消费充值');
            $res6 = $arm->add($order['user_id'], $order['order_id'], Currency::Cash, CurrencyAction::CashWechatResume, -$order['order_payfee'], $arm->getRecordAttach($order['order_id'], $order['order_fullname'], $order['order_no']), '微信消费');
            $uporder['order_payment'] = 1;
            if($paymethod == PayMethod::WxAppNative){
                $uporder['order_payment'] = 4;
            }
        }elseif($paymethod == PayMethod::AlipayNative){//支付宝
            $res5 = $arm->add($order['user_id'], $order['order_id'], Currency::Cash, CurrencyAction::CashRechargeWechatResume, $order['order_payfee'], $arm->getRecordAttach($order['order_id'], $order['order_fullname'], $order['order_no']), '微信消费充值');
            $res6 = $arm->add($order['user_id'], $order['order_id'], Currency::Cash, CurrencyAction::CashWechatResume, -$order['order_payfee'], $arm->getRecordAttach($order['order_id'], $order['order_fullname'], $order['order_no']), '微信消费');
            $uporder['order_payment'] = 2;
        }
        $res1 = Db::name('order')->where(['order_id'=>$order['order_id']])->update($uporder);

        //2.新人免单全返到佣金
        $res2 = true;
        if($order['product_returnall'] == 1){
            $res2 = $arm->add($order['user_id'], $order['order_id'], Currency::Commission, CurrencyAction::CommissionReturnAll, $order['order_payfee'], $arm->getRecordAttach($order['order_id'], '平台', $order['order_no']), '新人免单全返', 1);
        }

        //3.生成电子码
        $consume_code='';
        if($order['order_isexpress'] == 1){
            $mom = new MallOrderModel();
            $consume_code = $mom->buildConsumeCode($order['order_id'], $order['num'], $order['user_id'], $order['op_id']);
        }
        //4.发放佣金+服务号通知
        $cm = new CommissionModel();
        $res3 = $cm->build($order['order_id'], $consume_code);

        //5.短信通知
        $this->sendOrderSms($order, $consume_code, $cfg);

        //6.会员升级
        $upm = new UserUpgradeModel();
        $upm->check($order['user_id'], 2);
        if($res1 !== false && $res2 && $res3 !== false && $res5 && $res6){
            return true;
        }else{
            return false;
        }
    }


    public function sendOrderSms($order, $consume_code, $cfg){
        if($order['order_reservation'] == 1 && $order['order_isexpress'] == 1){
            //到店预约类短信
            $content = config('cdxx_sms.content_ordersuccessyuyue');
            $content = str_replace('{name}', $order['order_fullname'], $content);
            $content = str_replace('{num}', $order['num'], $content);
            $content = str_replace('{code}', $consume_code, $content);
            $content = str_replace('{starttime}', date('m月d日',$order['product_startusetime']), $content);
            $content = str_replace('{yysj1}', $cfg['yy_start'], $content);
            $content = str_replace('{yysj2}', $cfg['yy_end'], $content);
            $codearray = explode(',', $consume_code);
            $yyurl='';
            foreach($codearray as $k=>$v){
                $nativeurl= 'http://'.$cfg['domain'].'/wechat_html/page/reservationCenter/reservationCenter.html?code='.$v;
                $yyurl .= createShortUrl($nativeurl).';';
            }
            $content = str_replace('{yyurl}', $yyurl, $content);
            $content = str_replace('{yxq1}', date('Y-m-d', $order['product_startusetime']), $content);
            $content = str_replace('{yxq2}', date('Y-m-d', $order['product_endusetime']), $content);
        }elseif($order['order_reservation'] == 2 && $order['order_isexpress'] == 1){
            //到店免预约类短信
            $content = config('cdxx_sms.content_ordersuccessmianyuyue');
            $content = str_replace('{name}', $order['order_fullname'], $content);
            $content = str_replace('{num}', $order['num'], $content);
            $content = str_replace('{code}', $consume_code, $content);
            $content = str_replace('{yxq1}', date('Y-m-d', $order['product_startusetime']), $content);
            $content = str_replace('{yxq2}', date('Y-m-d', $order['product_endusetime']), $content);
        }elseif($order['order_reservation'] == 2 && $order['order_isexpress'] == 2){
            //快递商品,免预约
            $content = config('cdxx_sms.content_ordersuccesskuaidi');
            $content = str_replace('{name}', $order['order_fullname'], $content);
            $content = str_replace('{product}', $order['product_name'], $content);
            $content = str_replace('{orderno}', $order['order_no'], $content);
        }elseif($order['order_reservation'] == 1 && $order['order_isexpress'] == 2){
            //快递商品,预约制
            $content = config('cdxx_sms.content_ordersuccesskuaidiyuyue');
            $content = str_replace('{name}', $order['order_fullname'], $content);
            $content = str_replace('{product}', $order['product_name'], $content);
            $content = str_replace('{orderno}', $order['order_no'], $content);
        }
        return sendSmsCdxx($order['order_mobile'], $content);
    }



}