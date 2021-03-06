<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/23
 * Time: 13:46
 * 消费码预约控制器
 * 肖亚子
 */

namespace app\system\controller;
use think\Session;
use app\common\AdminBaseController;
use app\system\model\OrderreservationModel;
use app\system\model\OrderModel;

class OrderreservationController extends AdminBaseController{

    public function ReservationList(){

        $Condition = array();
        $Title     = $this->get('title', '');
        $Page      = $this->get('page', 1);//分页默认第一页
        $Status    = intval($this->get('status'));
        $Payment   = intval($this->get('payment'));
        $StartTime = strtotime($this->get("starttime"));
        $EndTime   = strtotime($this->get("endtime"));

        $Condition["r.reservation_status"] = array("gt",0);

        if ($Title){
            $Condition["cc.consume_code|o.order_no|o.order_fullname|o.order_mobile|p.product_name|u.nickname|u.mobile|m.merchant_name"] = array("like","%{$Title}%");
        }
        if ($Status){
            $Condition["r.reservation_status"] = array("eq",$Status);
        }
        if ($Payment){
            $Condition["r.reservation_payment"] = array("eq",$Payment);
        }

        $Condition    = $this->TimeContrast($StartTime,$EndTime,"r.reservation_addtime",$Condition);
        $Transference = self::Transference();
        $List         = OrderreservationModel::OrderReservationList($Condition,$Page,50);

        if ($List){
            foreach ($List["list"] as $Key => $Val){
                $List["list"][$Key]["paycss"]  = $Transference[0][$Val["reservation_payment"]]["css"];
                $List["list"][$Key]["payname"] = $Transference[0][$Val["reservation_payment"]]["name"];

                $List["list"][$Key]["statuscss"]  = $Transference[1][$Val["reservation_status"]]["css"];
                $List["list"][$Key]["statusname"] = $Transference[1][$Val["reservation_status"]]["name"];
            }
        }

        $Query = array("title"=>$Title,"status"=>$Status,"payment"=>$Payment);
        $Query = self::Time($StartTime,"starttime",$Query);
        $Query = self::Time($EndTime,"endtime",$Query);

        $this->assign("query",$Query);
        $this->assign('data', $List);
        return $this->display('list', true);
    }

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 用户电子码取消预约
     * 肖亚子
     */
    public function ReservationCancel(){
        if (request()->isGet()){
            $ID = $this->get('id', '');
            $this->assign("id",$ID);
            return $this->display('cancel', false);
        }else{
            $ID     = $this->post("id");
            $ReFind = OrderreservationModel::OrderReservationFind($ID,"consume_code_id,user_id");

            if (!$ReFind){
                $this->toError("预约信息不存在");
            }

            $Update = OrderreservationModel::OrderReservationUpdate($ID,array("reservation_status"=>4));

            if (!$Update){
                $this->toError("取消预约失败");
            }

            $CodeLog["user_id"]         = $ReFind["user_id"];
            $CodeLog["consume_code_id"] = $ReFind["consume_code_id"];
            $CodeLog["action"]          = "取消用户电子码预约";
            $CodeLog["admin_id"]        = Session::get('admin.id');
            $CodeLog["addtime"]         = time();
            OrderModel::OrderCodeLogAdd($CodeLog);

            $this->log("用户提现：[ID:".$ReFind["consume_code_id"]."电子码取消预约"."]");

            $this->toSuccess("更新成功",'', 2);
        }
    }
    /**
     * @return array
     * 预约状态转中文
     * 肖亚子
     */
    private function Transference(){

        $PayType = array("1" => array("css" => "layui-bg-green", "name" => "微信公众号支付"),
                        "2" => array("css" => "layui-bg-blue", "name" => "支付宝APP支付"),
                        "3" => array("css" => "layui-bg-orange", "name" => "银行卡支付"),
                        "4" => array("css" => "layui-bg-green", "name" => "微信APP支付"),);

        $Status  = array("1" => array("css" => "layui-bg-red","name" => "预约成功"),
                        "2" => array("css" => "layui-bg-green","name" => "预约完成"),
                        "3" => array("css" => "layui-bg-gray","name" => "预约过期"),
                        "4" => array("css" => "layui-bg-black","name" => "取消预约"));
        return array($PayType,$Status);
    }

    /**
     * @param $StartTime  开始时间
     * @param $EndTime    结束时间
     * @param $Key        字段
     * @param $Condition  返回组合
     * @return mixed
     */
    public  function TimeContrast($StartTime,$EndTime,$Key,$Condition){

        if (!empty($StartTime) && empty($EndTime)) {
            parent::Tpl_NotGtTime($StartTime,"开始时间不能大于当前时间"); //开始时间不为空和当前时间对比
            $Condition[$Key] = array(array('egt', $StartTime));
        } else if (empty($StartTime) && !empty($EndTime)) {
            parent::Tpl_NotGtTime($EndTime,"结束时间不能大于当前时间"); //结束时间不为空和当前时间对比
            $Condition[$Key] = array(array('lt', $EndTime));
        } else if (!empty($StartTime) && !empty($EndTime)) {
            parent::Tpl_TimeContrast1($StartTime,$EndTime); //开始和结束时间都不为空进行判断
            $Condition[$Key] = array(array('egt', $StartTime), array('elt', $EndTime));
        }

        return $Condition;
    }

    /**
     * @param $Time  转换时间
     * @param $Key   返回字段
     * @param $Query 组合数组
     * @return mixed
     */
    public function Time($Time,$Key,$Query){
        if(!empty($Time)){
            $Query[$Key] = date("Y-m-d H:i:s",$Time);
        }

        return $Query;
    }
}