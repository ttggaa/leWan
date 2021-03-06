<?php

namespace app\common\model;

use think\Db;


/**
 * 用户交易记录
 */
class AccountRecordModel {

    /**
     * 动态生成数据表
     */
    private function getTableName($currency, $suffix = '') {
        $suffix = ($suffix != null && $suffix != '') ? $suffix : Tag::getTbSuffix();
        $_tableName = 'account_' . $currency . $suffix;
        return $_tableName;
    }

    /**
     * 添加交易记录-同时更新账户余额
     * @param $user_id
     * @param $record_currency
     * @param $record_action
     * @param $record_amount
     * @param $record_attach
     * @param $record_remark
     * @return bool
     */
    public function add($user_id, $order_id=0, $record_currency, $record_action, $record_amount, $record_attach, $record_remark, $record_status=2) {
        if($record_amount == 0){
            return true;
        }
        $AccountM = new AccountModel();
        //先锁表
        $AccountM->lockUserItem($user_id);
        $allbalence = $AccountM->getAllBalance($user_id, 0, false);
        $balence_field = $AccountM->getBalanceField($record_currency);
        $item = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'record_currency' => $record_currency,
            'record_action' => $record_action,
            'record_amount' => $record_amount,
            'record_balance' => $allbalence[$balence_field] + $record_amount,
            'record_attach' => $record_attach,
            'record_remark' => $record_remark,
            'record_status' => $record_status,
            'record_addtime' => time(),
        ];
        //余额不足
        if($allbalence[$balence_field] + $record_amount < 0){
            return false;
        }
        //1.增加日志明细
        $res = Db::name($this->getTableName($record_currency))->insert($item);
        if ($res) {
            //2.实时统计用户帐户数据
            unset($allbalence[$balence_field]);
            $status = $AccountM->save($user_id, $item['record_currency'], $record_amount, $item['record_balance'], $allbalence);
            if($status){
            	return $res;
            }else{
            	return false;
            }
        }
        return false;
    }

    /**
     * 根据id获取记录
     * Enter description here ...
     * @param unknown_type $record_id
     * @param unknown_type $currency
     * @param unknown_type $suffix
     */
    public function getById($record_id, $currency, $suffix) {
        return Db::name($this->getTableName($currency, $suffix))->where(['record_id'=>$record_id])->find();
    }


    /**
     * 生成附件信息
     */
    public function getRecordAttach($user_id, $username='平台', $orderNo) {
        $data['userId'] = $user_id;
        $data['userName'] = $username;
        $data['orderNo'] = $orderNo;
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取商户钱包详情
     * @param array $condition
     * @param string $currency
     * @param string $suffix
     * @return bool|false|\PDOStatement|string|\think\Collection
     */
    public static function getAccountInfoList($condition=array(),$currency='', $suffix=''){
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
