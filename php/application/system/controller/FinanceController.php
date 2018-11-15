<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/10
 * Time: 17:05
 */

namespace app\system\controller;


use app\common\AdminBaseController;
use app\common\model\ManageFinanceModel;
use app\common\model\Tag;
use Think\Db;

/**
 * 平台的财务统计
 * Class FinanceController
 * @package app\system\controller
 */
class FinanceController extends AdminBaseController
{

    public function index(){
        $tag  = $this->get('tag','day' );
        if($tag =='day'){
            $day = Tag::getDay();
            $dayArr = array();
            $timeArr = array();
            $orderTit = '最近7天';
            for($i=6;$i>=0;$i--){
                $str = strtotime($day);
                $curDay = date('Ym',$str-($i*86400));
                $count = ManageFinanceModel::getManageFinanceCount(array('total_tag'=>$curDay));
                if($count) { //判断是否有当日数据
                    $dayArr[] = date('Ymd',$str-($i*86400)); //获取获取几天日期
                    $timeArr[] = date('m月d日',$str-($i*86400)); //获取获取几天日期
                }
            }
            $pieChartTag  = $day;
            $dstr = join(",", $dayArr);
            $condition =  "FIND_IN_SET(total_tag, '".$dstr."')";
        }else{
            $month = Tag::getMonth();
            $monthArr = array();
            $timeArr = array();
            for($i=6;$i>=0;$i--){
                $str = strtotime($month);
                $curMonth = date('Ym',$str-($i*86400*30));
                $count = ManageFinanceModel::getManageFinanceCount(array('total_tag'=>$curMonth));
                if($count){ //判断是否有当月数据
                    $monthArr[] = $curMonth; //获取获取几个月
                    $timeArr[] = date('Y年m月',$str-($i*86400*30)); //获取获取几个月
                }
            }
            $pieChartTag  = $month;
            $orderTit = '最近7个月';
            $mstr = join(",", $monthArr);
            $condition =  "FIND_IN_SET(total_tag, '".$mstr."')";
        }
        $this->assign('timeArr', json_encode($timeArr));
        $total  = ManageFinanceModel::getManageFinanceByTag(Tag::get())  ;//总数据
        $data = ManageFinanceModel::getManageFinanceByTag($pieChartTag)  ;//饼状图
        $list = ManageFinanceModel::getManageFinanceList($condition)  ;//统计曲线图
        $this->assign('orderTit', $orderTit);
        $this->assign('data', $data);
        $this->assign('total', $total);
        $this->assign('list', json_encode($list));
        return $this->display('index', true);
    }
}