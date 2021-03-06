<?php
namespace app\system\model;

use think\Db;
use think\Config;
use think\Request;
use think\Session;
use think\Cache;

/**
 * 商家相关
 * Enter description here ...
 * @author Administrator
 *
 */
class MerchantModel extends BaseModel{

    /**
     * 查询列表
     * @param array $Condition
     * @param int $Page
     * @param int $PageSize
     */
    public function getList($Condition=array(), $Page=1, $PageSize=20){
        //查询总记录
        $Count = Db::name('merchant m')->where($Condition)->count();

        $PageCount = ceil($Count/$PageSize);

        $List = Db::name('merchant m')
                ->field('m.merchant_id, m.merchant_name, m.merchant_contact, m.loginname, m.merchant_ssq, m.merchant_address, m.merchant_status, m.merchant_remark, m.merchant_addtime, m.merchant_uptime, m.merchant_open')
                ->where($Condition)
                ->page($Page, $PageSize)->order('m.merchant_uptime desc')->select();

        $PaginaTion = parent::Paging($Count,$Page,$PageCount,$List);
        return $PaginaTion;
    }


    public static function getMerchantAccountList($condition=array(), $page=1, $pageSize=20){
        $Count = Db::name('merchant_account ma')
            ->join('merchant m','m.merchant_id=ma.merchant_id','left')
            ->where($condition)
            ->page($page, $pageSize)->order('m.merchant_uptime desc')->count();
        $PageCount = ceil($Count/$pageSize);

        $list = Db::name('merchant_account ma')
            ->field('m.merchant_id, m.merchant_name, m.merchant_contact, m.loginname, 
             m.merchant_ssq, m.merchant_address, m.merchant_status,
             m.merchant_addtime,  m.merchant_open, ma.account_cash_expenditure,
              ma.account_cash_income, ma.account_cash_balance')
            ->join('merchant m','m.merchant_id=ma.merchant_id','left')
            ->where($condition)
            ->page($page, $pageSize)->order('m.merchant_uptime desc')->select();
        $PaginaTion = parent::Paging($Count,$page,$PageCount,$list);
        return $PaginaTion;

    }


    
}
