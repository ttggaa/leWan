<?php
namespace app\system\model;

use think\Db;
use think\Config;
use think\Request;
use think\Session;
use think\Cache;

/**
 * 会员相关
 * Enter description here ...
 * @author Administrator
 *
 */

class UserModel extends BaseModel{

    public static function TableName(){
        return Db::name("user");
    }

    /**
     * @param array $Condition     查询条件
     * @param int $Psize           当前分页数
     * @param int $PageSize        分页条数
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 肖亚子
     * 获取用户
     */
    public function UserList($Condition=array(), $Psize=1, $PageSize=50){
        $Field = "u.user_id,u.recode,u.mobile,u.nickname,u.avatar,u.childcount,u.level,u.auth,u.status,u.reg_time,r.nickname as rnickname,r.avatar as thumb";
        //查询总记录
        $Count     = self::TableName()
                        ->alias("u")
                        ->field($Field)
                        ->Join("user r","r.reid = u.user_id","left")
                        ->where($Condition)
                        ->group("u.user_id")
                        ->order('u.user_id desc')
                        ->count();

        $PageCount = ceil($Count/$PageSize);

        $List      = self::TableName()
                        ->alias("u")
                        ->field($Field)
                        ->Join("user r","r.reid = u.user_id","left")
                        ->where($Condition)
                        ->page($Psize, $PageSize)
                        ->group("u.user_id")
                        ->order("u.user_id desc")
                        ->select();

        $PaginaTion = parent::Paging($Count,$Psize,$PageCount,$List);

        return $PaginaTion;
    }

    /**
     * @param array $Condition    查询条件
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取用户详情
     * 肖亚子
     */
    public function UserFind($Condition = array()){
        $Data = self::TableName()->where($Condition)->find();

        return $Data;
    }
   
    
}
