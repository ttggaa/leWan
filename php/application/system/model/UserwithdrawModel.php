<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/10/20
 * Time: 9:33
 */
namespace app\system\model;
use think\Db;

class UserwithdrawModel extends BaseModel{

    public static function TableName(){
        return Db::name("user_withdraw");
    }

    /**
     * @param array $Condition     查询条件
     * @param int $Psize           分页数默认第一页
     * @param int $PageSize        分页条数默认50条
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取提现数据
     * 肖亚子
     */
    public function  UserCashList($Condition = array(), $Psize = 1, $PageSize = 50){
        $Field = "w.withdraw_id,w.withdraw_amount,w.withdraw_handfee,w.withdraw_realamount,w.withdraw_type,w.withdraw_status,w.withdraw_reason,w.withdraw_uptime,w.withdraw_addtime,u.avatar,u.mobile,u.nickname,ua.truename,ub.account_number,b.bank_name";

        //查询总记录
        $Count     = self::TableName()
                        ->alias("w")
                        ->field($Field)
                        ->Join("user u","u.user_id = w.user_id","left")
                        ->Join("user_auth ua","ua.user_id = w.user_id","left")
                        ->Join("user_bank ub","ub.ub_id = w.ub_id","left")
                        ->Join("bank b","b.bank_id = ub.bank_id","left")
                        ->where($Condition)
                        ->order('w.withdraw_addtime desc')
                        ->count();

        $PageCount = ceil($Count/$PageSize);

        $List      = self::TableName()
                        ->alias("w")
                        ->field($Field)
                        ->Join("user u","u.user_id = w.user_id","left")
                        ->Join("user_auth ua","ua.user_id = w.user_id","left")
                        ->Join("user_bank ub","ub.ub_id = w.ub_id","left")
                        ->Join("bank b","b.bank_id = ub.bank_id","left")
                        ->where($Condition)
                        ->page($Psize, $PageSize)
                        ->order('w.withdraw_addtime desc')
                        ->select();


        $PaginaTion = parent::Paging($Count,$Psize,$PageCount,$List);

        return $PaginaTion;
    }

    /**
     * @param array $Condition  查询条件
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取银行卡提现,加入提现队列银行提现信息
     * 肖亚子
     */
    public function UserBankListExcel($Condition = array()){
        $List = self::TableName()
                    ->alias("w")
                    ->field("b.bank_name,ub.province,ub.city,ub.account_number,ub.account_name,ub.account_tel,ub.bank_id,w.withdraw_realamount")
                    ->join("user_bank ub","ub.ub_id = w.ub_id","left")
                    ->join("bank b","b.bank_id = ub.bank_id")
                    ->where($Condition)
                    ->select();

        return $List;
    }

    /**
     * @param array $Cdondition 查询条件
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取银行卡提现,提现银行信息
     * 肖亚子
     */
    public function UserCashBank($Cdondition = array()){

        $Data = self::TableName()
                    ->alias("w")
                    ->field("ub.branch_name,ub.account_name,ub.account_number,ub.account_tel,b.bank_name")
                    ->join("user_bank ub","ub.user_id = w.user_id","left")
                    ->join("bank b","b.bank_id = ub.bank_id","left")
                    ->where($Cdondition)
                    ->find();

        return $Data;
    }
    /**
     * @param array $Condition  修改条件
     * @param $Data             修改内容
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 修改用户提现流程状态
     * 肖亚子
     */
    public  function UserCashUpdate($Condition = array(),$Data){
        $CashUp = self::TableName()->where($Condition)->update($Data);

        return $CashUp;
    }

    /**
     * @param array $Condition   查询条件
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取提现信息
     * 肖亚子
     */
    public  function UserCashFind($Condition = array()){
        $Data = self::TableName()->field("user_id,withdraw_type,withdraw_amount")->where($Condition)->find();

        return $Data;
    }

    /**
     * @param array $Condition  更新条件
     * @param $Data             更新内容
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 更新用户账户金额
     * 肖亚子
     */
    public function UserAcUpdate($Condition = array(),$Data){
        $AcUp = Db::name("account")->where($Condition)->update($Data);//->getLastSql();//->update($Data)

        return $AcUp;
    }

    /**
     * @param array $Condition  查询条件
     * @return int|string
     * 获取提现状态数量
     * 肖亚子
     */
    public function UserCashCount($Condition = array()){
        $Count = self::TableName()
                    ->where($Condition)
                    ->field("count(withdraw_id) as count,withdraw_status")
                    ->group("withdraw_status")
                    ->select();

        return $Count;
    }

}