<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/10
 * Time: 17:05
 */

namespace app\system\controller;


use app\common\AdminBaseController;
use app\common\model\AccountRecordModel;
use app\common\model\Tag;
use app\system\model\UserModel;
use Think\Db;

/**
 * 用户钱包
 * Class FinanceController
 * @package app\system\controller
 */
class UseraccountController extends AdminBaseController
{

    public function index(){
        $page     = $this->get('page', 1);
        $Title     = $this->get("title");//搜索栏数据
        $Level     = $this->get("level",0);//用户等级
        if ($Title){
            $Condition["u.mobile|u.nickname"] = array("like","%$Title%");
        }
        if ($Level){
            $Condition["u.level"] = array("eq",$Level);
        }
        $Condition["a.account_tag"] = array("eq",Tag::get());
        $um = new UserModel();
        $Data      = $um->getUserAccountList($Condition,$page,15);
        $Query     = array("title" => $Title,"level" => $Level);
        $this->assign("query",$Query);
        $this->assign("data",$Data);
        //统计
        $total = $um->getBalanceCount();
        $this->assign("total", $total);
        return $this->display('user:account', true);
    }



    public function month(){
        $userId = $this->get('id');
        $user = Db::name('user')->where(['user_id'=>$userId])->find();
        $this->assign('user', $user);
        //查询明细
        $where['user_id'] = $userId;
        $where['account_tag'] = ['between', '1,'. date('Ym')];
        $accountMonth =  Db::name('account')->where($where)->order('account_tag desc')->select();

        $this->assign('list', $accountMonth);
        return $this->display('user:account_month', true);
    }


    public function day(){
        $userid = $this->get('id');
        $tag = $this->get('tag');
        $user = Db::name('user')->where(['user_id'=>$userid])->find();
        $this->assign('user', $user);
        //查询明细
        $where['user_id'] = $userid;
        $where['account_tag'] = ['between', $tag.'00,'. $tag.'31'];
        $list =  Db::name('account')->where($where)->order('account_tag asc')->select();
        $this->assign('list', $list);
        return $this->display('user:account_day', true);
    }


    public function info(){
        $um = new UserModel();
        $monthlist = $um->getMonthList();
        $this->assign('monthlist', $monthlist);
        $page     = $this->get('page', 1);
        $userid = $this->get('user_id', 0);
        $starttime = $this->get('starttime', '');
        $endtime = $this->get('endtime', '');
        $curreny = $this->get('currency', 'cash');
        $month = $this->get('month', date('Ym'));
        $this->assign('month', $month);
        $month = str_replace('-','',$month);
        $startmonth = date('Yd',time());
        $endmonth = date('Yd',time());
        $where['user_id'] = $userid;
        if($starttime != ''){
            $this->assign('starttime', $starttime);
            $starttime = strtotime($starttime);
            $where['record_addtime'] = ['gt', $starttime];
        }
        if($endtime != ''){
            $this->assign('endtime', $endtime);
            $endtime = strtotime($endtime);
            $where['record_addtime'] = ['lt', ($endtime+86400)];
        }
        if($endtime != '' && $starttime != ''){
            $where['record_addtime'] = ['between', $starttime.','.($endtime+86400)];
        }
        $list = $um->getAccountRecordInfo($curreny, $month, $where, $page);//fuck($list);
        $this->assign('data', $list);
        return $this->display('user:account-info', true);
    }

    /**
     * 获取两个时间段中所有月份
     * @param $startTime 开始时间戳
     * @param $len
     * @return array
     */
    public function getMonth($startTime,$endtime){
        $monarr[] = date('Ym',$startTime); // 当前月
        while( ($startTime = strtotime('+1 month', $startTime)) <= $endtime){
            $monarr[]= date('Ym',$startTime); // 取得递增月;
        }
        return $monarr;
    }

}