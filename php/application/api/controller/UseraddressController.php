<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/6
 * Time: 14:14
 * 收货地址接口
 * 肖亚子
 */
namespace app\api\controller;
use Think\Exception;
use app\api\model\UserModel;
use app\api\model\UserAddressModel;
use app\api\model\AreaModel;

class UseraddressController extends ApiBaseController{

    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 获取用户收货地址列表
     * 肖亚子
     */
    public function UserAddressList(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");
            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            $Uid = UserModel::UserFindUid($Token);

            $Condition["type"] = 1; //默认获取快递类收货地址
            $Condition["user_id"] = array("eq",$Uid);
            $Condition["status"] = array("eq",1);

            $List = UserAddressModel::UserAddressList($Condition);

            foreach ($List as $Key => $Val){
                $List[$Key]["mobile"] = Func_Phone_Replace($Val["mobile"]);
            }

            $this->returnApiData("获取成功", 200,$List);
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 用户(添加/修改)收货地址
     * 肖亚子
     */
    public function UserAppendAddress(){
        try{
            $Token     = input("post.token","","htmlspecialchars,strip_tags");
            $Addressid = intval(input("post.addressid"));
            $Contacts  = input("post.contacts","","htmlspecialchars,strip_tags");
            $Phone     = input("post.phone","","htmlspecialchars,strip_tags");
            $Province  = intval(input("post.province"));
            $City      = intval(input("post.city"));
            $Area      = intval(input("post.area"));
            $Address   = input("post.address","","htmlspecialchars,strip_tags");

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            parent::Tpl_Empty($Contacts,"请输入收货人",2);
            parent::Tpl_FullSpace($Contacts,"请输入收货人",2);
            parent::Tpl_StringLength($Contacts,"收货人最少1字最多12字",3,2,6,2);
            parent::Tpl_Empty($Phone,"请输入联系电话",2);
            parent::Tpl_Phone($Phone,"请输入正确的联系电话",2);
            parent::Tpl_Empty($Province,"请选择收货省份",2);
            parent::Tpl_Empty($City,"请选择收货城市",2);
            parent::Tpl_Empty($Area,"请选择收货地区",2);
            parent::Tpl_Empty($Address,"请输入收货详细地址",2);
            parent::Tpl_FullSpace($Address,"请输入收货详细地址",2);
            parent::Tpl_StringLength($Address,"收货详细地址最少8字最多30字",3,8,30,2);

            $ProvinceName = AreaModel::AreaName(array("id"=>array("eq",$Province)));
            $CityName     = AreaModel::AreaName(array("id"=>array("eq",$City),"parentid"=>array("eq",$Province)));
            $AreaName     = AreaModel::AreaName(array("id"=>array("eq",$Area),"parentid"=>array("eq",$City)));

            $Message = $Addressid?"收货地址编辑失败":"收货地址添加失败";
            $Success = $Addressid?"收货地址编辑成功":"收货地址添加成功";

            if (!$CityName){
                $this->returnApiData($Message , 400);
            }
            if (!$AreaName){
                $this->returnApiData($Message , 400);
            }

            $Uid = UserModel::UserFindUid($Token);

            if (!$Addressid){
                $Data["user_id"] = $Uid;
                $Data["flag"]    = 1;
                $Data["addtime"] = time();
            }

            $Data["contact"]       = $Contacts;
            $Data["mobile"]        = $Phone;
            $Data["province_code"] = $Province;
            $Data["city_code"]     = $City;
            $Data["area_code"]     = $Area;
            $Data["ssq"]           = $ProvinceName.$CityName.$AreaName;
            $Data["address"]       = $Address;

            if ($Addressid){
                $Condition["address_id"] = array("eq",$Addressid);
                $Condition["user_id"]    = array("eq",$Uid);
                $Data = UserAddressModel::UserAddressUpdate($Condition,$Data);
            }else{
                $Data = UserAddressModel::UserAddressAdd($Data);
            }

            if ($Data){
                $this->returnApiData($Success, 200);
            }else{
                $this->returnApiData($Message, 400);
            }
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }

    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 获取用户收货地址详情
     */
    public function UseramendAddress(){
        try{
            $Token     = input("post.token","","htmlspecialchars,strip_tags");
            $AddressId = intval(input("post.addressid"));

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            parent::Tpl_Empty($AddressId,"收货地址编辑失败",2);

            $Uid = UserModel::UserFindUid($Token);

            $Condition["address_id"] = $AddressId;
            $Condition["user_id"]    = $Uid;
            $Condition["status"]     = 1;

            $Data = UserAddressModel::UserAddressFind($Condition);

            if ($Data){
                $this->returnApiData("获取成功", 200,$Data);
            }else{
                $this->returnApiData("收货地址不存在", 400);
            }

        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }

    }

    /**
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 删除收货地址
     * 肖亚子
     */
    public function UserReviseAddress(){
        try{
            $Token     = input("post.token","","htmlspecialchars,strip_tags");
            $AddressId = intval(input("post.addressid"));

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            parent::Tpl_Empty($AddressId,"收货地址移除失败",2);

            $Uid = UserModel::UserFindUid($Token);

            $Condition["user_id"]    = $Uid;
            $Condition["address_id"] = $AddressId;

            $Data["status"] = 2;

            $Data = UserAddressModel::UserAddressUpdate($Condition,$Data);

            if ($Data){
                $this->returnApiData("收货地址移除成功", 200);
            }else{
                $this->returnApiData("收货地址移除失败", 400);
            }
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }

}