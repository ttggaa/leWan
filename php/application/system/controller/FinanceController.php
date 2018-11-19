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
use app\system\model\ExcelModel;
use app\system\model\FinanceModel;
use Think\Db;

/**
 * 平台的财务统计
 * Class FinanceController
 * @package app\system\controller
 */
class FinanceController extends AdminBaseController
{

    public function index(){

    }


    /**
     * 平台收支统计
     */
    public function table(){
        $page = $this->get('page', 1);
        $starttime = $this->get('starttime', '');
        $endtime = $this->get('endtime', '');
        $where['total_tag'] = ['gt', 20181001];
        if($starttime != ''){
            $where['total_tag'] = array('between', str_replace('-','',$starttime).',20301201');
            $this->assign('starttime', $starttime);
        }
        if($endtime != ''){
            $where['total_tag'] = array('between', '20181001,'.str_replace('-','',$endtime));
            $this->assign('endtime', $endtime);
        }
        if($starttime != '' && $endtime != ''){
            $where['total_tag'] = array('between', str_replace('-','',$starttime).','.str_replace('-','',$endtime));
        }
        $fm = new FinanceModel();
        $data = $fm->getList($where, $page);
        $this->assign('data', $data);
        return $this->display('table', true);
    }

    /**
     * 按订单
     */
    public function order(){
        $page = $this->get('page', 1);
        $starttime = $this->get('starttime', '');
        $endtime = $this->get('endtime', '');
        $where['o.order_status'] = ['gt', 1]; //统计已付款
        if($starttime != ''){
            $where['total_tag'] = array('between', str_replace('-','',$starttime).',20301201');
            $this->assign('starttime', $starttime);
        }
        if($endtime != ''){
            $where['total_tag'] = array('between', '20181001,'.str_replace('-','',$endtime));
            $this->assign('endtime', $endtime);
        }
        if($starttime != '' && $endtime != ''){
            $where['total_tag'] = array('between', str_replace('-','',$starttime).','.str_replace('-','',$endtime));
        }
        $fm = new FinanceModel();
        $data = $fm->getOrderList($where, $page);
        $this->assign('data', $data);

        $cates = Db::name('product_categoryfinance')->select();
        $this->assign('cates', $cates);
        return $this->display('order', true);
    }


    /**
     * 导出数据
     */
    public function exportOrder(){
        $em = new ExcelModel();
        $em->export(array('姓名','年龄'), array(array('a',21),array('b',23)), '档案', true);
    }



    /**
     * 统计商家消单
     */
    public function merchant(){

    }

    public function exportMerchant(){

    }
}