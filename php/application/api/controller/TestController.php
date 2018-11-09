<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/1
 * Time: 9:33
 */

namespace app\api\controller;


use app\api\model\OpenTmModel;
use app\common\model\AccountRecordModel;
use app\common\model\Currency;
use app\common\model\CurrencyAction;
use app\common\model\ProcedureModel;
use think\Db;

class TestController extends ApiBaseController
{

    public function index(){
        $otm = new OpenTmModel();
        $data['title'] = '您好，您的订单已支付成功';
        $data['keyword1'] = '您好，您的订单已支付成功';
        $data['keyword2'] = '您好，您的订单已支付成功';
        $data['keyword3'] = "1元\r\n客户姓名：test\r\n客户电话：123232323";
        $data['remark'] = '您好，您的订单已支付成功';
        $otm->sendTplmsg5('oRSVB5rM3f92zkp7O3f6R0Gp3Bhw', '15_SbSF7B5W81jpTobMYBuGUL-cTjwvi8QMksRYCnFLsrcMPuwiHJ4hUdc6_qHnvDk402Y4Ulw46mFnSRPbUN3SgdVWpaHILhQgckXR9u5RGT-26kwsyP-LhKXPMpFuGUbijsuXNyqbxA9q8Q9JXPUiAHAMEJ', $data);
    }

}