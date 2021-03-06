<?php

namespace app\common\model;

use think\Db;


/**
 * 商户交易记录
 */
class MerchantAccountRecordModel {

    const ACTION_INCOME = 1; //收入
    const ACTION_EXPENDITURE = 2; //支出

    /**
     * 动态生成数据表
     */
    private static function getTableName($currency, $suffix = '') {
        $suffix = ($suffix != null && $suffix != '') ? $suffix : Tag::getTbSuffix();
        $_tableName = 'merchant_account' . $currency . $suffix;
        return $_tableName;
    }

    /**
     * 添加交易记录-同时更新账户余额
     * @param $merchantId
     * @param $recordAction 操作类型
     * @param $recordAmount 操作金额
     * @param $recordAttach json数据
     * @param $recordRemark 备注
     * @param $tag
     * @return bool
     */
    public static function add($merchantId,$recordCurrency,$recordAction, $recordAmount, $recordAttach,$recordRemark) {
        if($recordAmount == 0){
            return true;
        }
        //先锁表
        MerchantAccountModel::lockMerchantItem($merchantId);
        $fields = 'account_id,account_cash_balance';
        $account = MerchantAccountModel::getItemByMerchantId($merchantId,$fields);//获取商户余额数据
        if($recordAction == CurrencyAction::MCashDeducAdmin || $recordAction == CurrencyAction::MCashSettleAdmin ){
            $recordBalance = $account['account_cash_balance'] - $recordAmount;
            if($recordBalance<0){  //余额不足
                return false;
            }
            $acction = self::ACTION_EXPENDITURE;
        }else{ //电子码消单进账 | 收货订单完成进账 | 后台充值
            $acction = self::ACTION_INCOME;
            $recordBalance = $account['account_cash_balance'] + $recordAmount;
        }
        //实时统计用户帐户数据
        $status =  MerchantAccountModel::save($merchantId, $recordAmount, $recordBalance,$acction);
        if(!$status){
            return false;
        }
        $item = [
            'merchant_id' => $merchantId,
            'record_action' => $recordAction,
            'record_amount' => $recordAmount,
            'record_balance' => $recordBalance,
            'record_attach' => $recordAttach,
            'record_remark' => $recordRemark,
            'record_addtime' => time(),
        ];
        //增加日志明细
        Db::name(self::getTableName($recordCurrency))->insert($item);
        return true;
    }

    /**
     * 根据id获取记录
     * Enter description here ...
     * @param unknown_type $record_id
     * @param unknown_type $currency
     * @param unknown_type $suffix
     */
    public static function getById($record_id, $currency, $suffix) {
        return Db::name(self::getTableName($currency, $suffix))->where(['record_id'=>$record_id])->find();
    }


    /**
     * 获取商户钱包详情
     * @param array $condition
     * @param string $currency
     * @param string $suffix
     * @return bool|false|\PDOStatement|string|\think\Collection
     */
    public static function getMerchantAccountInfoList($condition=array(),$currency='', $suffix=''){
        $tabel = self::getTableName($currency,$suffix);
        if(Db::query("SHOW TABLES LIKE 'jay_{$tabel}'")){ //判表是否存在
            $list = Db::name($tabel.' ma')
                ->field('m.merchant_id, m.merchant_name,
                ma.record_action,ma.record_attach,ma.record_remark,ma.record_amount, ma.record_balance,ma.record_addtime')
                ->join('merchant m','m.merchant_id=ma.merchant_id','left')
                ->where($condition)
                ->order('ma.record_addtime desc')->select();
            return $list;
        }else{
            return [];
        }
    }
}
