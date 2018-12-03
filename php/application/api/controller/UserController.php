<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/3
 * Time: 13:58
 * 用户接口
 * 肖亚子
 */

namespace app\api\controller;
use app\api\model\OrderModel;
use app\api\model\UserModel;
use app\common\model\ProcedureModel;
use app\common\model\Tag;
use Think\Db;
use Think\Exception;
use app\api\model\HelpModel;
use app\api\model\UserUpgradeModel;
use app\api\model\UserauthModel;
use app\common\model\Paymodel;

class UserController extends ApiBaseController{
    /**
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 用户注册
     * 肖亚子
     */
    public function UserRegister(){
        try{
            $Token       = input("post.token","","htmlspecialchars,strip_tags");
            $Recode      = input("post.recode","","htmlspecialchars,strip_tags");//推荐码
            $Mobile      = input("post.mobile","","htmlspecialchars,strip_tags");//注册电话
            $ProvingCode = input("post.provingcode","","htmlspecialchars,strip_tags");//验证码

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Punfu->PhoneVerification($Mobile,$ProvingCode);

            parent::Tpl_Empty($Recode,"请输入推荐码",2);
            parent::Tpl_FullSpace($Recode,"请输入推荐码",2);
            parent::Tpl_NoSpaces($Recode,"请输入正确的推荐码",2);
            parent::Tpl_Alphanumeric($Recode,"请输入正确的推荐码",2);
            parent::Tpl_Lengths($Recode,"推荐码正确长度为6-16",6,16,2);
            parent::Tpl_Empty($Mobile,"请输入手机号",2);
            parent::Tpl_FullSpace($Mobile,"请输入正确的手机号",2);
            parent::Tpl_Phone($Mobile,"请输入正确的手机号码",2);
            parent::Tpl_Empty($ProvingCode,"请输入验证码",2);
            parent::Tpl_FullSpace($ProvingCode,"请输入正确的验证码",2);
            parent::Tpl_Lengths($ProvingCode,"验证码为6位",6,6,2);

            $Uid = UserModel::UserFindUid($Token);

            $Condition["u.user_id"]  = array("eq",$Uid);
            $Condition["u.reg_type"] = array("eq",1);

            $User = UserModel::UserDataFind($Condition,"u.mobile,u.level");

            if ($User){
                if ($User["level"] > 1){
                    $this->returnApiData("您已经注册,请勿重复", 400);
                }
                if ($User["mobile"] == $Mobile){
                    $this->returnApiData("该手机号已被注册,请重新输入", 400);
                }
            }else{
                $this->returnApiData("请登录", 400);
            }

            $Referee = UserModel::UserDataFind(array("u.recode"=>array("eq",$Recode)),"u.user_id,u.path,u.floor");

            if ($Referee){
                $Data["reid"]  = $Referee["user_id"];
                $Data["path"]  = $Referee["path"].$Referee["user_id"].",";
                $Data["floor"] = $Referee["floor"] + 1;
            }else{
                $this->returnApiData("推荐人不存在", 400);
            }

            $Locus  = self::UserMobileArea($Mobile);
            $Random = Func_Random(6);

            $Data["recode"]     = HelpModel::makeUserCode(6);
            $Data["mobile"]     = $Mobile;
            $Data["password"]   = func_user_hash(substr($Mobile,-8),$Random);
            $Data["dllkey"]     = $Random;
            $Data["level"]      = 2;
            $Data["upgrade_time"]      = time();
            $Data["mobileaddr"] = $Locus["prov"]."/".$Locus["city"]."/".$Locus["type"];

            $ConditionUp["user_id"] = array("eq",$Uid);

            $UserData = UserModel::UserUpdate($ConditionUp,$Data);

            if ($UserData){
                UserUpgradeModel::check($Uid,1);//用户升级检测
                $uplog['user_id'] = $Uid;
                $uplog['old_level'] = 1;
                $uplog['new_level'] = 2;
                $uplog['remark'] = '注册超级会员';
                $uplog['addtime'] = time();
                Db::name('user_upgrade')->insert($uplog);
                $this->returnApiData("注册成功", 200);
            }else{
                $this->returnApiData("注册失败", 400);
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
     * 获取用户个人信息
     * 肖亚子
     */
    public function UserPersonal(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            $Uid       = UserModel::UserFindUid($Token);
            $UserData  = UserModel::UserDataFind(array("u.token"=>array("eq",$Token)),"u.token,u.recode,u.nickname,u.avatar,u.level,u.auth,u.freesheet");

            if ($this->headerData["product"] == "wechat"){
                $Condition["user_id"]  = $Uid;
                $Condition["platform"] = "wechat";

                $Subscribe = UserModel::UserConnectFind($Condition);
                $UserData["subscribe"] = $Subscribe["subscribe"];
            }

            switch ($UserData["level"]){
                case 1:$UserData["username"] = "普通用户"; break;
                case 2:$UserData["username"] = "超级会员"; break;
                case 3:$UserData["username"] = "分享达人"; break;
                case 4:$UserData["username"] = "运营达人"; break;
                case 5:$UserData["username"] = "玩主"; break;
            }

            $this->returnApiData("获取成功", 200,$UserData);
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
     * 获取注册用户我的好友
     * 肖亚子
     */
    public function UserFriends(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Punfu->UserLevelPower($Token);//用户权限验证

            //获取我的好友
            $Time   = strtotime(date("Y-m-d",time()));
            $Fecode = UserModel::UserDataFind(array("u.token"=>array("eq",$Token)),"u.user_id,u.recode,u.floor,u.level");
            $Uid    = $Fecode["user_id"];
            $Floor  = $Fecode["floor"] + 2;

            $C_Condition[] = array("exp","sid = {$Uid} and reid = 0 and reg_time > {$Time}");

            $Condition["reid"] = array("eq",$Uid);
            $NCondition[]      = array("exp"," reid = {$Uid} and reg_time > {$Time}");

            if (in_array($Fecode["level"],array(2,3))){//超级达人/营销达人 全部好友 查推荐的下两级用户,独立出去的用户团队不查
                $UnderCondition[] = array("exp"," level <= 3 and floor <= {$Floor}");
                $NewCondition[]   = array("exp"," level <= 3 and floor <= {$Floor} and reg_time > {$Time}");
            }elseif($Fecode["level"] == 4){//运营达人看自己的下级,独立出去的用户团队不查
                $UnderCondition["level"] = array("elt",3);
                $NewCondition[]          = array("exp"," level <= 3 and reg_time > {$Time}");
            }else{//玩主查看全部的推荐用户,玩主团队不查
                $UnderCondition["level"] = array("elt",4);
                $NewCondition[]          = array("exp"," level <= 4 and reg_time > {$Time}");
            }

            $Customer    = UserModel::UserCount(array("sid"=>array("eq",$Uid),"reid"=>array("eq",0)));//获取客户人数
            $NewCustomer = UserModel::UserCount($C_Condition);//获取最新客户人数
            $Directly    = UserModel::UserCount($Condition);//获取直属好友人数并且没独立出去
            $NewDirectly = UserModel::UserCount($NCondition);//获取最新直属好友人数
            $Whole       = UserModel::UserOperateCount($UnderCondition,$Uid);//获取全部好友人数
            $NewWhole    = UserModel::UserOperateCount($NewCondition,$Uid);//获取最新全部好友人数

            $UseFriendsr["customer"]    = $Customer;
            $UseFriendsr["newcustomer"] = $NewCustomer;
            $UseFriendsr["directly"]    = $Directly;
            $UseFriendsr["newdirectly"] = $NewDirectly;
            $UseFriendsr["whole"]       = $Whole;
            $UseFriendsr["newwhole"]    = $NewWhole;

            $this->returnApiData("获取成功", 200,$UseFriendsr);
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
     * 获取注册用户钱包
     * 肖亚子
     */
    public function UserWallet(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Punfu->UserLevelPower($Token);//用户权限验证

            $Uid = UserModel::UserFindUid($Token);

            $UserData = array();

            //获取我的钱包
            $AccCondition["user_id"]     = array("eq",$Uid);
            $AccCondition["account_tag"] = array("eq",0);
            $AcfCondition["user_id"]     = array("eq",$Uid);
            $AcfCondition["finance_tag"] = array("eq",0);

            $Acc = UserModel::UserAccount($AccCondition,"account_cash_balance,account_commission_balance");
            $Acf = UserModel::UserAccountFinance($AcfCondition,"finance_withdraw,finance_first,finance_second,finance_operations,finance_operationchilds,finance_playerhost");

            //累计提现
            $ufinance = Db::name('account_finance')->where(['user_id'=>$Uid, 'finance_tag'=>0])->find();
            $jrtime = strtotime(date('Y-m-d'));
            $txamount = Db::name('account_cash'.Tag::getMonth())->where(['user_id'=>$Uid, 'record_addtime'=>['between',$jrtime.','.($jrtime+86400)], 'record_action'=>['in', '852,850,851']])->sum('record_amount');
            //累计结算
            $jsamount = Db::name('account_cash'.Tag::getMonth())->where(['user_id'=>$Uid, 'record_addtime'=>['between',$jrtime.','.($jrtime+86400)], 'record_action'=>['in', '802,803']])->sum('record_amount');

            $UserReward["putforward"] = $Acc?$Acc["account_cash_balance"]:0;
            $UserReward["pending"]    = $Acc?$Acc["account_commission_balance"]:0;
            $UserReward["grandtotal"] = $ufinance['finance_settle'] + abs($jsamount);
            $UserReward["sumup"]      = $ufinance['finance_withdraw'] + abs($txamount);

            $UserData["reward"] = $UserReward;

            //获取收入指南
            $TodayTime     = strtotime(date("Ymd",time()));
            $YesterdayTime = strtotime(date("Ymd",strtotime("-1 day",time())));

            $TodayCondition["user_id"]         = array("eq",$Uid);
            $TodayCondition["account_tag"]     = array("eq",date("Ymd",time()));
            $YesterdayCondition["user_id"]     = array("eq",$Uid);
            $YesterdayCondition[]              = array("exp","record_action=802 or record_action=803 and  record_addtime > {$YesterdayTime} and record_addtime < {$TodayTime}");
            $ThismonthCondition["user_id"]     = array("eq",$Uid);
            $ThismonthCondition["account_tag"] = array("eq",date("Ym",time()));
            $LastmonthCondition["user_id"]     = array("eq",$Uid);
            $LastmonthCondition["finance_tag"] = array("eq",date("Ym",strtotime('-1 month')));

            // 今日、
            $toay = Db::name('account_commission'.Tag::getMonth())->where(['user_id'=>$Uid, 'record_addtime'=>['between',$jrtime.','.($jrtime+86400)], 'record_action'=>['in', '601,602,603,604,606,607']])->sum('record_amount');
            $Income["today"] = sprintf('%.2f', $toay);
            //昨日、
            $Yesterday = Db::name('account_finance')->where(['user_id'=>$Uid, 'finance_tag'=>Tag::getYesterday()])->field('finance_first+finance_second+finance_operations+finance_operationchilds+finance_playerhost+finance_xrmd as amount')->find();
            $Income["yesterday"] = sprintf('%.2f', $Yesterday['amount']);
            //当月
            $thismonth = Db::name('account_finance')->where(['user_id'=>$Uid, 'finance_tag'=>Tag::getMonth()])->field('finance_first+finance_second+finance_operations+finance_operationchilds+finance_playerhost+finance_xrmd as amount')->find();
            $Income["thismonth"] = sprintf('%.2f', $thismonth['amount']+$toay*1);
            //上月
            $lastmonth = Db::name('account_finance')->where(['user_id'=>$Uid, 'finance_tag'=>Tag::getLastMonth()])->field('finance_first+finance_second+finance_operations+finance_operationchilds+finance_playerhost+finance_xrmd as amount')->find();
            $Income["lastmonth"] = sprintf('%.2f', $lastmonth['amount']);

            $UserData["income"] = $Income;

            $this->returnApiData("获取成功", 200,$UserData);
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
     * 获取我的推荐人信息
     * 肖亚子
     */
    public function UserRefereeData(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Punfu->UserLevelPower($Token);//用户权限验证

            $Uid  = UserModel::UserFindUid($Token);
            $Reid = UserModel::UserDataFind(array("u.user_id"=>array("eq",$Uid)),"u.reid");

            if (!$Reid["reid"]){
                $this->returnApiData("没有推荐人", 400);
            }

            $Referee = UserModel::UserDataFind(array("u.user_id"=>array("eq",$Reid["reid"])),"u.nickname,u.avatar");

            $this->returnApiData("获取成功", 200,$Referee);
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }

    }
    /**
     * 获取推荐码用户详细
     * 肖亚子
     */
    public function UserReferee(){
        try{
            $Recode = input("post.recode","","htmlspecialchars,strip_tags");//用户推荐码

            parent::Tpl_Empty($Recode,"请输入推荐码",2);
            parent::Tpl_FullSpace($Recode,"请输入推荐码",2);
            parent::Tpl_NoSpaces($Recode,"请输入正确的推荐码",2);
            parent::Tpl_Alphanumeric($Recode,"请输入正确的推荐码",2);
            parent::Tpl_Lengths($Recode,"推荐码正确长度为6-16位",6,16,2);

            $Condition["u.recode"] = array("eq",$Recode);

            $Referee = UserModel::UserDataFind($Condition,"u.nickname,u.avatar");

            if (!$Referee){
                $this->returnApiData("推荐人不存在", 400);
            }

            $this->returnApiData("获取成功", 200,$Referee);
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
     * 用户第一次分享二维码生成
     * 肖亚子
     */
    public function UserQrCode(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");
            $Url   = input("post.url","","htmlspecialchars,strip_tags");

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Punfu->UserLevelPower($Token);//用户权限验证

            parent::Tpl_Empty($Url,"请求错误",2);
            parent::Tpl_FullSpace($Url,"请求错误",2);

            $User = UserModel::UserFinds($Token);

            $Data["url"]         = $Url."?recode={$User['recode']}";
            $Data["Picturename"] = $User["recode"];

            $Invitation = QrCode($Data,1);//生成邀请海报

            $this->returnApiData("获取成功", 200,array("url"=>$Invitation));

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
     * 获取用户邀请注册二维码
     * 肖亚子
     */
    public function UserInviteQRCode(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");
            $Type  = intval(input("post.type"));
            $Url   = input("post.url","","htmlspecialchars,strip_tags");

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Punfu->UserLevelPower($Token);//用户权限验证
            // $Punfu->UserRealName($Token);//验证用户是否实名认证

            if (!in_array($Type,array(1,2,3,4))){
                $this->returnApiData("请选择邀请函背景", 400);
            }

            parent::Tpl_Empty($Url,"请求错误",2);
            parent::Tpl_FullSpace($Url,"请求错误",2);

            $Uid      = UserModel::UserFindUid($Token);
            $User     = UserModel::UserFinds($Token);
            $TrueName = UserauthModel::UserAuthFind($Uid);

            $Data["url"]         = $Url."?recode={$User['recode']}";
            $Data["truename"]    = $TrueName;
            $Data["Picturename"] = $User["recode"];
            $Data["type"]        = $Type;

            $Invitation = QrCode($Data,2);//生成邀请海报

            $this->returnApiData("获取成功", 200,array("url"=>$Invitation));
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }

    /**
     * @param $Mobile   手机号
     * @return mixed
     * 获取用户手机号归属
     * 肖亚子
     */
    private function UserMobileArea($Mobile){
        $MobileUrl = "https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query={$Mobile}&resource_id=6004&ie=utf8&oe=utf8&format=json";
        $MobileUrl = json_decode(curlGet($MobileUrl),true);

        if (!$MobileUrl){
            $this->returnApiData("请输入正确的手机号", 400);
        }else{
            $Locus = $MobileUrl["data"][0];
            return $Locus;
            // $Data["mobileaddr"] = $Locus["prov"]."/".$Locus["city"]."/".$Locus["type"];
        }
    }

    /**
     *  获取注册用户我的好友列表
     * author@yihong
     */
    public function UserFriendsList(){

        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");
            $Status = intval(input("post.type",1));//状态 1全部好友，2 我的客户 ，3直属好友
            $Page   = intval(input("post.page",1));//分页默认第一页
            $Psize  = intval(input("post.psize",10));//分页条数默认10条
            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Punfu->UserLevelPower($Token);//用户权限验证

            //获取我的好友
            $Time   = strtotime(date("Y-m-d",time()));
            $Fecode = UserModel::UserDataFind(array("u.token"=>array("eq",$Token)),"u.user_id,u.recode,u.floor,u.level");
            $Uid    = $Fecode["user_id"];
            $Floor  = $Fecode["floor"] + 2;

            $C_Condition[] = array("exp","sid = {$Uid} and reid = 0 and reg_time > {$Time}");
            $Condition["reid"] = array("eq",$Uid);
            $NCondition[]      = array("exp"," reid = {$Uid} and reg_time > {$Time}");

            if (in_array($Fecode["level"],array(2,3))){//超级达人/营销达人 全部好友 查推荐的下两级用户,独立出去的用户团队不查
                $UnderCondition[] = array("exp"," level <= 3 and floor <= {$Floor}");
                $NewCondition[]   = array("exp"," level <= 3 and floor <= {$Floor} and reg_time > {$Time}");
            }elseif($Fecode["level"] == 4){//运营达人看自己的下级,独立出去的用户团队不查
                $UnderCondition["level"] = array("elt",3);
                $NewCondition[]          = array("exp"," level <= 3 and reg_time > {$Time}");
            }else{//玩主查看全部的推荐用户,玩主团队不查
                $UnderCondition["level"] = array("elt",4);
                $NewCondition[]          = array("exp"," level <= 4 and reg_time > {$Time}");
            }

            $filed = "user_id,nickname,avatar,level";
            if($Status == UserModel::ALL_USER_FRIEND){ //全部好友
                if (in_array($Fecode["level"],array(2,3))){//超级达人/营销达人 全部好友 查推荐的下两级用户,独立出去的用户团队不查
                    $Where[] = array("exp"," level <= 3 and floor <= {$Floor}");
                }elseif($Fecode["level"] == 4){//运营达人看自己的下级,独立出去的用户团队不查
                    $Where["level"] = array("elt",3);
                }else{//玩主查看全部的推荐用户,玩主团队不查
                    $Where["level"] = array("elt",4);
                }
                $count  = UserModel::UserOperateCount($Where,$Uid);//获取全部好友人数
                $reids  = [];
                $List = UserModel::UserOperateList($Where,$Uid,$filed,$reids,$Page,$Psize);//获取全部好友
            }elseif ($Status == UserModel::USER_CUSTOMER){ //我的客户
                $Where = array("sid"=>array("eq",$Uid),"reid"=>array("eq",0));
                $count    = UserModel::UserCount($Where);//获取客户人数
                $List    = UserModel::getUserFriendList($Where,$filed, $Page,$Psize);//获取我的好友
            }else{ //我的直属好友
                $Where["reid"] = array("eq",$Uid);
                $count    = UserModel::UserCount($Where);//获取直属好友人数并且没独立出去
                $List    = UserModel::getUserFriendList($Where,$filed, $Page,$Psize);//获取我的好友
            }

            $UseFriendsr['count'] = $count;
            $UseFriendsr['list'] = $List;
            $this->returnApiData("获取成功", 200,$UseFriendsr);
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
     * 获取我的订单列表
     * 肖亚子
     */
    public function UserOrderList(){
        try{
            $Token  = input("post.token","","htmlspecialchars,strip_tags");
            $Page   = intval(input("post.page",1));//分页默认第一页
            $Psize  = intval(input("post.psize",10));//分页条数默认10条
            $Type   = intval(input("post.type",'1'));//订单状态默认1 1全部 2待付款 3已付款 4已完成 5退款

            $Punfu  = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Userid = UserModel::UserFindUid($Token);

            $Condition["o.user_id"]      = array("eq",$Userid);
            $Condition["o.order_status"] = array("neq",0);

            if ($Type == 2){
                $Condition["o.order_status"] = array("eq",1);
            }elseif ($Type == 3){
                $Condition[] = array("exp","o.order_status >= 2 and o.order_status < 4");
            }elseif ($Type == 4){
                $Condition["o.order_status"] = array("eq",4);
            }elseif ($Type == 5){
                $Condition["o.order_status"] = array("eq",6);
            }

            $OrderList = OrderModel::OrderList($Condition,$Page,$Psize);
           // $this->returnApiData("获取成功", 200,$OrderList);
            if ($OrderList){
                $OrderList = self::OrderStatus($OrderList);
            }
            $this->returnApiData("获取成功", 200,$OrderList);
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
     * 用户订单详情
     * 肖亚子
     */
    public function UserOrderInfo(){
        try{
            $Token   = input("post.token","","htmlspecialchars,strip_tags");
            $Orderid = intval(input("post.order_id",0));//订单ID

            $Punfu   = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            parent::Tpl_Empty($Orderid,"请求错误",2);

            $Uid = UserModel::UserFindUid($Token);

            $Condition["o.order_id"] = array("eq",$Orderid);
            $Condition["o.user_id"] = array("eq",$Uid);

            $OrderInfo = OrderModel::getOrderInfoByOrderId($Condition);

            if($OrderInfo){
                $OrderData = self::OrderFindStatus($OrderInfo,$Uid);
                $this->returnApiData("获取成功", 200,$OrderData);
            }else{
                $this->returnApiData("订单不存在", 400);
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
     * 获取达人后台订单数据
     * 肖亚子
     */
    public function OrderDaren(){
        try{
            $Token = input("post.token","","htmlspecialchars,strip_tags");
            $Page  = intval(input("post.page",1));//分页默认第一页

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            $Level = UserModel::UserDataFind(array("u.token"=> array("eq",$Token)),"u.level");

            if (!in_array($Level["level"],array(4,5))){
                $this->returnApiData("权限不足", 400);
            }

            $Uid = UserModel::UserFindUid($Token);

            $Condition[] = array("exp","(o.order_status >= 2 or o.order_status <= 4) and (p.userid_first = {$Uid} or p.userid_second = {$Uid} or p.userid_operations = {$Uid} or p.userid_operations_child = {$Uid} or p.userid_playerhost_child = {$Uid} )");

            $List = OrderModel::OrderDarenList($Condition,$Page,10);

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
     * 用户确认收货
     * 肖亚子
     */
    public function OrderConfirm(){
        try{
            $Token   = input("post.token","","htmlspecialchars,strip_tags");
            $OrderId = intval(input("post.order_id",1));//订单id

            $Punfu = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            $Uid = UserModel::UserFindUid($Token);

            $Condition["order_id"]        = array("eq",$OrderId);
            $Condition["user_id"]         = array("eq",$Uid);
            $Condition["order_isexpress"] = array("eq",2);
            $Condition["order_status"]    = array("eq",3);

            //查询订单是不是待收货
            $OrderFind = OrderModel::UserOrderFind($Condition);
            if (!$OrderFind){
                $this->returnApiData("确认收货失败", 400);
            }

            $DataCondition["order_id"] = array("eq",$OrderId);
            $DataCondition["user_id"]  = array("eq",$Uid);

            //订单改为收货完成
            $OrderUp = OrderModel::OrderUpdate($DataCondition,array("order_status"=>4));
            $pm = new  ProcedureModel();

            $res6 = $pm->execute('merchant_kuaidi_income', $OrderId, '@error');
            if(!$OrderUp || !$res6){
                $this->returnApiData("确认收货失败", 400);
            }

            $this->returnApiData("收货成功", 200);
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }
    /**
     * @param $List 订单数据
     * 0订单过期 1待付款 2待发货 3待预约发货 4待收货 5待使用 6预约订单已使用 7已完成  8取消订单 9申请退款 10 申请换货
     * 订单列表状态转换
     * 肖亚子
     */
    private function OrderStatus($List){
        $Time = strtotime("-30 minutes", time());

        $OrderList = array();
        foreach ($List as $Key => $Val){

            if ($Val["order_status"] == 1 &&  $Val["order_addtime"] < $Time){
                continue;
            }

            if ($Val["order_isexpress"] == 1){

                if ($Val["order_reservation"] == 1){//到店预约商品
                    $Condition["cc.order_id"] = array("eq",$Val["order_id"]);
                    $Condition["cc.status"]   = array("elt",2);
                    $Condition["re.reservation_status"] = array("gt",0);
                    $Condition["re.reservation_status"] = array("lt",4);
                    $CodeCondition["cc.order_id"] = array("eq",$Val["order_id"]);
                    $CodeCondition["cc.status"]   = array("elt",2);

                    $ReservationList = OrderModel::OrderConsumeCodeList($Condition);
                    $CodeList        = OrderModel::OrderConsumeCodeNoList($CodeCondition);

                    if ($ReservationList){
                        $ReCount   = count($ReservationList);
                        $CodeCount = count($CodeList);

                        if ($ReCount != $CodeCount){
                            if ($Val["order_status"] == 2){
                                $List[$Key]["order_status"] = 6;
                            }
                        }else{
                            foreach ($CodeList as $K => $V){
                                if ($V["status"] == 1){
                                    if ($Val["order_status"] == 2){
                                        $List[$Key]["order_status"] = 5;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($Val["order_status"] == 4){
                            $List[$Key]["order_status"] = 7;
                        }
                    }else{
                        if ($Val["order_status"] == 2){
                            $List[$Key]["order_status"] = 5;
                        }elseif ($Val["order_status"] == 4){
                            $List[$Key]["order_status"] = 7;
                        }
                    }

                }else{//到店免预约商品
                    if ($Val["order_status"] == 2){
                        $List[$Key]["order_status"] = 5;
                    }elseif ($Val["order_status"] == 4){
                        $List[$Key]["order_status"] = 7;
                    }
                }

            }elseif ($Val["order_isexpress"] == 2 && $Val["order_reservation"] == 1){
                $OrderFahuo = OrderModel::OrderReservationFahuoFind(array("order_id"=>array("eq",$Val["order_id"])));

                if ($OrderFahuo){
                    if ($Val["order_status"] == 3){
                        $List[$Key]["order_status"] = 4;
                    }elseif ($Val["order_status"] == 4){
                        $List[$Key]["order_status"] = 7;
                    }
                }else{
                    if ($Val["order_status"] == 2){
                        $List[$Key]["order_status"] = 3;
                    }
                }
            }elseif ($Val["order_isexpress"] == 2 && $Val["order_reservation"] == 2){
                if ($Val["order_status"] == 3){
                    $List[$Key]["order_status"] = 4;
                }elseif ($Val["order_status"] == 4){
                    $List[$Key]["order_status"] = 7;
                }
            }
            unset($List[$Key]["order_isexpress"]);
            unset($List[$Key]["order_reservation"]);
            unset($List[$Key]["order_addtime"]);

            $OrderList[] = $List[$Key];
        }

        return $OrderList;
    }

    /**
     * @param $Data 转换数据
     * 订单详情转换状态
     * 肖亚子
     */
    private function OrderFindStatus($Data,$Uid){
        $OrderCode = array();
        $Count     = 0;

        if ($Data["endusetime"] < time()){
            $EndTime = 2;//订单商品结束时间过期等于2
        }else{
            $EndTime = 1;//订单商品结束时间未过期等于1
        }

        if ($Data["isexpress"] == 1 && $Data["reservation"] == 1){

            $Condition["cc.order_id"]           = array("eq",$Data["order_id"]);
            $Condition["cc.status"]             = array("elt",2);
            $Condition["re.reservation_status"] = array("gt",0);
            $Condition["re.reservation_status"] = array("lt",4);
            $CodeCondition["cc.order_id"]       = array("eq",$Data["order_id"]);
            $CodeCondition["cc.status"]         = array("elt",2);

            $ReservationList = OrderModel::OrderConsumeCodeList($Condition);
            $CodeList        = OrderModel::OrderConsumeCodeNoList($CodeCondition);

            if ($ReservationList){
                $ReCount   = count($ReservationList);
                $CodeCount = count($CodeList);

                if ($ReCount != $CodeCount){
                    if ($Data["status"] == 2){
                        $Data["status"] = 6;
                    }
                }else{
                    foreach ($CodeList as $Key => $Val){
                        if ($Val["status"] == 1){
                            if ($Data["status"] == 2){
                                $Data["status"] = 5;
                                break;
                            }
                        }
                    }
                }

                if ($Data["status"] == 4){
                    $Data["status"] = 7;
                }
            }else{
                if ($Data["status"] == 2){
                    $Data["status"] = 5;
                }elseif ($Data["status"] == 4){
                    $Data["status"] = 7;
                }
            }

            $CodeCondition["cc.order_id"] = array("eq",$Data["order_id"]);
            $CodeCondition["cc.user_id"]  = array("eq",$Uid);

            $CodeList  = OrderModel::OrderConsumeCodeList($CodeCondition);

            if ($CodeList){
                foreach ($CodeList as $K => $V){
                    if (!$V["reservation_id"] || ($V["reservation_no"] && $V["reservation_status"] == 0)){//未预约
                        $Codes["consume_code"] = $V["consume_code"];
                        $Codes["consume_status"]       = $V["status"];
                        $Codes["consume_type"]         = 1;
                        $OrderCode[]           = $Codes;
                    }elseif ($V["reservation_id"] || $V["reservation_no"] && $V["reservation_status"] == 1){//已预约待使用
                        $Codes["consume_code"] = $V["consume_code"];
                        $Codes["consume_status"]       = $V["status"];
                        $Codes["consume_type"]         = 2;
                        $OrderCode[]           = $Codes;
                    }
                }
            }

            $Count = count($OrderCode);

        }elseif ($Data["isexpress"] == 1 && $Data["reservation"] == 2){
            if ($Data["status"] == 2){
                $Data["status"] = 5;
            }elseif ($Data["status"] == 4){
                $Data["status"] = 6;
            }

            $CodeCondition["cc.order_id"] = array("eq",$Data["order_id"]);
            $CodeCondition["cc.user_id"]  = array("eq",$Uid);
            $CodeList  = OrderModel::OrderConsumeCodeNoList($CodeCondition);

            if ($CodeList){
                foreach ($CodeList as $K => $V){
                    $Codes["consume_code"] = $V["consume_code"];
                    $Codes["consume_status"] = $V["status"];
                    $Codes["consume_type"]   = 2;
                    $OrderCode[]             = $Codes;
                }
            }
        } elseif ($Data["isexpress"] == 2 && $Data["reservation"] == 1){
            $OrderFahuo = OrderModel::OrderReservationFahuoFind(array("order_id"=>array("eq",$Data["order_id"])));
            if ($OrderFahuo){
                if ($Data["status"] == 3){
                    $Data["status"] = 4;
                }elseif ($Data["status"] == 4){
                    $Data["status"] = 7;
                }
            }else{
                if ($Data["status"] == 2){
                    $Data["status"] = 3;
                }
            }
        }elseif ($Data["isexpress"] == 2 && $Data["reservation"] == 2){
            if ($Data["status"] == 3){
                $Data["status"] = 4;
            }elseif ($Data["status"] == 4){
                $Data["status"] = 7;
            }
        }

        $Data["endtime"]    = $EndTime;
        $Data["code_count"] = $Count;
        $Data["code"]       = $OrderCode;
        $Data['payendtime'] = $Data['addtime']+1800;

        return $Data;
    }

    /**
     * 获取消息
     */
    public function getSysMsg(){

        try{
            $Token   = input("post.token","","htmlspecialchars,strip_tags");
            $Punfu   = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
          //  $Uid = UserModel::UserFindUid($Token);
            //'0所有人，1安卓，2iOS，3微信 ,4APP',
            $touser = $this->post('touser','web');
            if($touser == "android"){
                $condition['a.msg_user']   = array('in','0,1,4');
            }else if($touser == "ios"){
                $condition['a.msg_user']   = array('in','0,2,4');
            }else{
                $condition['a.msg_user']   = array('in','0,3');
            }
            $page = $this->post('page',1);
            $pagesize = 10;
            $condition['a.msg_status'] = 2;//已推送的消息
            $count =Db::name('msg a')->join('jay_msg_read r' ,'r.msg_id=a.msg_id','left')->where($condition)->count();
            $list = Db::name('msg a')
                ->field('a.msg_id,a.msg_type,a.msg_title,a.msg_content,a.send_time,r.user_id ')
                ->join('jay_msg_read r' ,'r.msg_id=a.msg_id','left')
                ->where($condition)
                ->page($page, $pagesize)
                ->order(' msg_id desc')
                ->select();
            foreach ($list as &$val){
                if($val){
                    $val['isread'] = 0;
                     //30天以前未读的消息全部默认已读
                    if($val['send_time'] < strtotime('-30 day'))
                        $val['isread'] = 1;
                    if($val['user_id']) //近30天内的消息有阅读情况
                        $val['isread'] = 1;
                    unset($val['user_id']);
                }
            }
            $data['count'] = $count;
            $data['list'] = $list;
            $this->returnApiData('获取成功', 200, $data);
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }
    /**
     * 标记已读消息
     */
    public function readMsg(){
        try{
            $Token   = input("post.token","","htmlspecialchars,strip_tags");
            $Punfu   = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Uid = UserModel::UserFindUid($Token);
            $msgid = $this->post('msg_id');
            $umcount = Db::name('msg_read')->where(array('msg_id'=>$msgid,'user_id'=>$Uid))->count();
            if(!$umcount){ //已读
                $data['msg_id'] = $this->post('msg_id');
                $data['user_id'] = $Uid;
                $data['read_time'] = time();
                Db::name('msg_read')->insert($data);
            }
            $this->returnApiData('获取成功', 200);
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }


    /**
     * 获取消息详情
     */
    public function getSysMsgInfo(){

        try{
            $Token   = input("post.token","","htmlspecialchars,strip_tags");
            $Punfu   = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            //  $Uid = UserModel::UserFindUid($Token);
            //'0所有人，1安卓，2iOS，3微信 ,4APP',
            $msgid = $this->post('m_id');
            if(!$msgid){
                $this->returnApiData('消息ID不能为空', 400);
            }
            $condition['msg_status'] = 2;//已推送的消息
            $condition['msg_id'] = $msgid;
            $info = Db::name('msg')
                ->field('msg_id,msg_type,msg_title,msg_content,send_time')
                ->where($condition)
                ->find();
            $this->returnApiData('获取成功', 200, $info);
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }
    }
    /**
     * 用户取消预约
     */
    public function cancelUserReservation(){
        try{
            $Token   = input("post.token","","htmlspecialchars,strip_tags");
            $Punfu   = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断
            $Uid = UserModel::UserFindUid($Token);
            $resId   = input("post.res_id");
            $where['user_id'] = $Uid;
            $where['reservation_id'] = $resId;
            $where['reservation_status'] = 1; //已预约成功
            $field = 'r.reservation_id,pr.calendar,r.reservation_no,r.reservation_transaction_id as transaction_id
            ,r.reservation_payment,r.reservation_addprice,r.consume_code_id,r.reservation_addprice as totalfee';
            $quxiaoyuyue_day = Db::name('parameter')->where(array('key'=>'quxiaoyuyue_day'))->value('value');
            if($quxiaoyuyue_day){
                $rs = Db::name('order_user_reservation r')
                    ->field($field)
                    ->join('product_reservationday pr','pr.reservationday_id=r.reservationday_id')
                    ->where($where)->find();
                //判断是否满足预约提前取消条件 公式 预约时间>= 当前时间+提前取消时间 ”列：2018-07-05> 2018-07-(01+2) “
                if($rs && $rs['calendar'] >=  strtotime("+{$quxiaoyuyue_day} day")){
                    Db::startTrans();
                    $data['reservation_status'] = 4;
                    $res = Db::name('order_user_reservation')->where(array('reservation_id'=>$resId))->update($data);
                    if($res){
                        Db::commit();
                        Db::name('order_consume_code')->where(array('consume_code_id'=>$rs['consume_code_id']))->update(array('status'=>1));
                        if($rs['reservation_no']){ //有预约加价退款
                            $pm = new Paymodel();
                            $pm->wxRefund($rs);
                        }
                    }else{
                        Db::rollback();
                        $this->returnApiData('取消预约失败', 400);
                    }
                }else{
                    $this->returnApiData('没有查到预约信息', 400);
                }
            }
            $this->returnApiData('取消预约成功', 200);
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
     * 用户进行锁粉
     * 肖亚子
     */
    public function UserLockPowder(){
        try{
            $Token  = input("post.token","","htmlspecialchars,strip_tags");
            $Recode = input("post.recode","","htmlspecialchars,strip_tags");

            $Punfu  = new PubfunController();
            $Punfu->UserLoginStatus($Token,$this->headerData);//用户判断

            parent::Tpl_Empty($Recode,"锁粉失败",2);
            parent::Tpl_FullSpace($Recode,"锁粉失败",2);
            parent::Tpl_StringLength($Recode,"锁粉失败",3,6,20,2);

            $Uid  = UserModel::UserFindUid($Token);
            $User = UserModel::UserDataFind(array("u.user_id" => array("eq",$Uid)),"u.sid");

            if ($User["sid"]){
                $this->returnApiData('已被锁粉', 400);
            }

            $Condition["u.recode"]  = array("eq",$Recode);
            $Condition["u.user_id"] = array("neq",$Uid);

            $ReUser = UserModel::UserDataFind($Condition,"u.user_id,u.level");

            if ($ReUser && $ReUser["level"] > 1){
                $Data["sid"] = $ReUser["user_id"];
            }else{
                $this->returnApiData('锁粉失败', 400);
            }

            $UserRid = UserModel::UserUpdate(array("user_id"=>array("eq",$Uid)),$Data);

            if (!$UserRid){
                $this->returnApiData('锁粉失败', 400);
            }

            $this->returnApiData('锁粉成功', 200);
        }catch (Exception $e){
            parent::Tpl_Abnormal($e->getMessage());
        }

    }

}