<?php

/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/10/22
 * Time: 10:26
 * 肖亚子
 * 订单控制器
 */

namespace app\system\controller;

use app\common\AdminBaseController;
use app\system\model\ProductModel;
use think\Config;
use Think\Log;
use think\Request;
use think\Db;
use think\Session;
use app\common\RegExpression;
use app\common\Md5Help;
use app\common\SysHelp;
use app\system\model\PaginationModel;
use app\system\model\AdminModel;
use app\system\model\ContentModel;
use app\system\model\OrderModel;
use Think\Upload;

class OrderController extends AdminBaseController {

    /**
     * @return string
     * 获取订单列表数据
     * 肖亚子
     */
    public function OrderList() {
        //获取参数
        $Condition = array();
        $Page      = $this->get('page', 1);//分页默认第一页
        $Status    = $this->get('status', 0);
        $Title     = $this->get('title', '');
        $StartTime = strtotime($this->get("starttime"));
        $EndTime   = strtotime($this->get("endtime"));
        $Isexpress = $this->get('isexpress', 0);
        $Payment   = $this->get('payment', 0);

        $Condition = $this->TimeContrast($StartTime,$EndTime,"o.order_addtime",$Condition);
        if ($Title){
            $Condition["o.order_no|u.nickname|u.mobile|m.merchant_name|o.order_fullname|o.order_mobile"] = array("like","%$Title%");
        }
        if ($Isexpress){
            $Condition["o.order_isexpress"] = $Isexpress;
        }
        if ($Payment){
            $Condition["o.order_payment"] = $Payment;
        }
        $this->assign('count', OrderModel::OrderCount($Condition));

        if ($Status){
            if ($Status==8){
                $Condition["o.order_status"] = 0;
            }else{
                $Condition["o.order_status"] = $Status;
            }
        }

        $OrderList = OrderModel::OrderList($Condition,$Page,50);
        $List      = $OrderList[0];
        $Payfee    = $OrderList[1];

        $Transference = self::Transference();

        foreach($List["list"] as $Key=>$Val){
            $List["list"][$Key]["typecss"]    = $Transference[0][$Val["order_isexpress"]]["css"];
            $List["list"][$Key]["typename"]   = $Transference[0][$Val["order_isexpress"]]["name"];
            $List["list"][$Key]["recss"]      = $Transference[1][$Val["order_reservation"]]["css"];
            $List["list"][$Key]["rename"]     = $Transference[1][$Val["order_reservation"]]["name"];
            $List["list"][$Key]["paycss"]     = $Transference[2][$Val["order_payment"]]["css"];
            $List["list"][$Key]["payname"]    = $Transference[2][$Val["order_payment"]]["name"];

            if ($Val["order_isexpress"] == 1){
                if ($Val["order_status"] == 2){
                    $List["list"][$Key]["statuscss"]  = $Transference[4][0]["css"];
                    $List["list"][$Key]["statusname"] = $Transference[4][0]["name"];
                }else{
                    $List["list"][$Key]["statuscss"]  = $Transference[3][$Val["order_status"]]["css"];
                    $List["list"][$Key]["statusname"] = $Transference[3][$Val["order_status"]]["name"];
                }
            }elseif ($Val["order_isexpress"] == 2 && $Val["order_reservation"] == 1){

                if ($Val["order_status"] == 0){
                    $List["list"][$Key]["statuscss"]  = $Transference[3][$Val["order_status"]]["css"];
                    $List["list"][$Key]["statusname"] = $Transference[3][$Val["order_status"]]["name"];
                }else{
                    if (!$Val["address_id"]){
                        $List["list"][$Key]["statuscss"]  = $Transference[4][1]["css"];
                        $List["list"][$Key]["statusname"] = $Transference[4][1]["name"];
                    }else{
                        $List["list"][$Key]["statuscss"]  = $Transference[3][$Val["order_status"]]["css"];
                        $List["list"][$Key]["statusname"] = $Transference[3][$Val["order_status"]]["name"];
                    }
                }
            }else{
                $List["list"][$Key]["statuscss"]  = $Transference[3][$Val["order_status"]]["css"];
                $List["list"][$Key]["statusname"] = $Transference[3][$Val["order_status"]]["name"];
            }

//            $List["list"][$Key]["statuscss"]  = $Transference[3][$Val["order_status"]]["css"];
//            $List["list"][$Key]["statusname"] = $Transference[3][$Val["order_status"]]["name"];
        }

        $Query = array("title" => $Title,"isexpress" => $Isexpress,"payment"=>$Payment);
        $Query = self::Time($StartTime,"starttime",$Query);
        $Query = self::Time($EndTime,"endtime",$Query);

        $this->assign("query",$Query);
        $this->assign('status', $Status);
        $this->assign('payfee', $Payfee);
        $this->assign('data', $List);
       // $this->assign('query_str', http_build_query($Query));
        return $this->display('index', true);
    }

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 获取订单详情信息
     * 肖亚子
     */
    public function OrderData(){
        $Condition = array();

        if (Request()->isGet()){
            //获取订单相关信息
            $OrderId = $this->get("order_id");
            $Status  = $this->get("status");

            $DataFind    = OrderModel::OrderFind($OrderId);
            $Data        = $DataFind[0];//订单信息
            $Goods       = $DataFind[1];//订单商品信息
            $Calendar    = $DataFind[2];//到店免预约日历数据
            $Reservation = $DataFind[3];//到店预约制商品或免预约商品
            $Delivery    = $DataFind[4];//快递预约制商品,预约发货信息
            $OrderCode   = $DataFind[5];//电子码
            $OrderMarkup = $DataFind[6];//电子码预约加价信息

            $Transference = self::Transference();

            $Data["typecss"]    = $Transference[0][$Data["order_isexpress"]]["css"];
            $Data["typename"]   = $Transference[0][$Data["order_isexpress"]]["name"];
            $Data["recss"]      = $Transference[1][$Data["order_reservation"]]["css"];
            $Data["rename"]     = $Transference[1][$Data["order_reservation"]]["name"];
            $Data["paycss"]     = $Transference[2][$Data["order_payment"]]["css"];
            $Data["payname"]    = $Transference[2][$Data["order_payment"]]["name"];

            if ($Data["order_isexpress"] == 1){
                if ($Data["order_status"] == 2){
                    $Data["statuscss"]  = $Transference[4][0]["css"];
                    $Data["statusname"] = $Transference[4][0]["name"];
                }else{
                    $Data["statuscss"]  = $Transference[3][$Data["order_status"]]["css"];
                    $Data["statusname"] = $Transference[3][$Data["order_status"]]["name"];
                }
            }elseif ($Data["order_isexpress"] == 2 && $Data["order_reservation"] == 1){
                if (!$Delivery){
                    $Data["statuscss"]  = $Transference[4][1]["css"];
                    $Data["statusname"] = $Transference[4][1]["name"];
                }else{
                    $Data["statuscss"]  = $Transference[3][$Data["order_status"]]["css"];
                    $Data["statusname"] = $Transference[3][$Data["order_status"]]["name"];
                }
            }else{
                $Data["statuscss"]  = $Transference[3][$Data["order_status"]]["css"];
                $Data["statusname"] = $Transference[3][$Data["order_status"]]["name"];
            }

            if($Goods["pricecalendar"]){
                $Goods["pricecalendar"] = json_decode($Goods["pricecalendar"],true);
            }

            if($OrderMarkup){
                foreach ($OrderMarkup as $Key=>$Val){
                    $OrderMarkup[$Key]["paycss"]  = $Transference[2][$Val["reservation_payment"]]["css"];
                    $OrderMarkup[$Key]["payname"] = $Transference[2][$Val["reservation_payment"]]["name"];
                }
            }

            $this->assign("status",$Status);
            $this->assign("data",$Data);
            $this->assign("goods",$Goods);
            $this->assign("calendar",$Calendar);
            $this->assign("reservation",$Reservation);
            $this->assign("ordercode",$OrderCode);
            $this->assign("ordermarkup",$OrderMarkup);
            $this->assign("delivery",$Delivery);

            return $this->display("view",true);
        }else{
            //修改订单消费码状态
            $OrderId = $this->post("order_id");
            $UserId  = $this->post("user_id");
            $Statuss = $this->post("statuss");
            $Status  = $this->post("status/a");

            foreach ($Status as $Key=>$Val){
                $Condition["order_id"]        = $OrderId;
                $Condition["user_id"]         = $UserId;
                $Condition["consume_code_id"] = $Key;

                $Data["status"] = $Val;
                $Data["uptime"] = time();

                $CodeUp = OrderModel::OrderConsumeCodeUp($Condition,$Data);

                if ($CodeUp){
                    $Action = "管理员：".Session::get('admin.nickname')."修改订单消费码状态为：";
                    $TypeName = $Val == 1?"恢复":"冻结";
                    $CodeLog["user_id"]         = $UserId;
                    $CodeLog["consume_code_id"] = $Key;
                    $CodeLog["action"]          = $Action . $TypeName;
                    $CodeLog["admin_id"]        = Session::get('admin.id');
                    $CodeLog["addtime"]         = time();
                    OrderModel::OrderCodeLogAdd($CodeLog);
                }
            }

            $this->log("修改订单消费码状态：[订单ID:".$OrderId."ID/状态值".json_encode($Status)."]");

            $this->toSuccess("更新成功", url("Order/OrderData",array("order_id"=>$OrderId,"status"=>$Statuss)), 1);
        }

    }

    /**
     * @return array
     * 订单状态转中文
     * 肖亚子
     */
    private function Transference(){
        $GoodsType   = array("1" => array("css" => "layui-bg-red", "name" => "到店商品"),
                            "2" => array("css" => "layui-bg-green", "name" => "快递商品"),);
        $Reservation = array("0" => array("css" => "layui-bg-gray", "name" => "免预约"),
                             "1" => array("css" => "layui-bg-blue", "name" => "预约制"),
                             "2" => array("css" => "layui-bg-gray", "name" => "免预约"),);
        $PayType     = array("1" => array("css" => "layui-bg-green", "name" => "微信公众号支付"),
                            "2" => array("css" => "layui-bg-blue", "name" => "支付宝APP支付"),
                            "3" => array("css" => "layui-bg-orange", "name" => "银行卡支付"),
                            "4" => array("css" => "layui-bg-green", "name" => "微信APP支付"),
                            "5" => array("css" => "layui-bg-green", "name" => "后台下单核算"),
                          );
        $OrderStatus = array("1" => array("css" => "layui-bg-gray","name" => "待付款"),
                            "2" => array("css" => "layui-bg-black","name" => "待发货"),
                            "3" => array("css" => "layui-bg-cyan","name" => "待收货"),
                            "4" => array("css" => "layui-bg-green","name" => "已完成"),
                            "5" => array("css" => "layui-bg-blue","name" => "取消订单"),
                            "6" => array("css" => "layui-bg-red","name" => "申请退款"),
                            "7" => array("css" => "layui-bg-orange","name" => "申请换货"),
                            "0" => array("css" => "layui-bg-black","name" => "订单过期"));
        $OrderBespoke = array("0"=>array("css" => "layui-bg-black","name" => "待使用"),
                              "1"=>array("css" => "layui-bg-gray","name" => "待预约发货")
                            );

        return array($GoodsType,$Reservation,$PayType,$OrderStatus,$OrderBespoke);
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

    /**
     * 修改
     * Enter description here ...
     */
//    public function view() {
//        if (Request::instance()->isGet()) {
//            $item = Db::name('order o')
//                ->field('o.*, r.username, r.mobile, r.remark, m.title, r.num, r.price, u.nickname')
//                ->where('o.id', Request::instance()->param('id', 0))
//                ->join('order_room r', 'r.order_id = o.id', 'left')
//                ->join('room m', 'm.id = r.room_id', 'left')
//                ->join('member u', 'o.user_id = u.id', 'left')
//                ->order('o.id desc')
//                ->find();
//
//            $service = Db::name('order_service')->where(['order_id'=>$item['id']])->select();
//            $item['services'] = $service;
//
//            $this->assign('obj', $item);
//            return $this->display('view', true);
//        }
//    }

    /**
     * 删除账号
     * Enter description here ...
     */
//    public function delete() {
//        $id = Request::instance()->param('id', 0);
//        $idstr = Request::instance()->post('idstr', '');
//        if ($id > 0) {
//            $obj = Db::name('order')->where('id', $id)->find();
//            $this->log('删除订单：' . $obj['id']);
//            $res = Db::name('order')->where('id='.$id)->update(['del'=>1]);
//        } else {
//            //批量删除
//            $idarray = explode(',', $idstr);
//            foreach ($idarray as $k => $v) {
//                if (!(empty($v))) {
//                    $obj = Db::name('order')->where('id', $v)->find();
//                    $this->log('删除订单：' . $obj['id']);
//                    $res = Db::name('order')->where('id='.$v)->update(['del'=>1]);
//                }
//            }
//        }
//        $this->toSuccess('删除成功');
//    }


//    public function ruzhu() {
//        $id = Request::instance()->param('id', 0);
//        if ($id > 0) {
//            $obj = Db::name('order')->where('id', $id)->find();
//            $this->log('客人入住：' . $obj['id']);
//            $res = Db::name('order')->where('id='.$id)->update(['status'=>2]);
//        }
//        $this->toSuccess('入住成功');
//    }


    public function createOrder(){
        $product_id = input('id');
        $price = input('price');
        $buynum = input('buynum');
        $price_id = DB::name('product_price')->where(array('product_id'=>$product_id))->value('price_id');
        $product = $this->verfiyProduct($product_id, $price_id,$buynum);
        if($product){
            $data['buynum'] = $buynum;
            $data['concat'] = '后台下单';
            $data['product_id'] = $product_id;
            $data['price_id'] = $price_id;
            $data['price'] = $price;
            $data['remark'] = $this->post('remark', '通过后台管理员下单');
            $data['user_id'] = 15439;//下单用户
            $data['time'] = time();
            $data['key'] = getSelfSignStr($data);
            $host =  $_SERVER['HTTP_HOST']?$_SERVER['HTTP_HOST']: $_SERVER['SERVER_NAME'];
            $url =  $host.'/api/Order/createOrder';
            curlPost($url,$data); //curl创建订单
            $this->success('创建成功,',url('system/order/orderlist'));
        }else{
            $this->error('没有找到产品信息');
        }
    }


    public function createOrderByExcel(){
        if (!empty ($_FILES ['excel'] ['name'])) {
            $tmp_file = $_FILES ['excel'] ['tmp_name'];
            $file_types = explode(".", $_FILES ['excel'] ['name']);
            $file_type = $file_types [count($file_types) - 1];
            /*判别是不是.xls文件，判别是不是excel文件*/
            if (strtolower($file_type) != "xlsx") {
                $this->error('不支持的Excel文件，请重新上传');
            }
            vendor('phpexcel.PHPExcel');
            vendor('phpexcel.PHPExcel.IOFactory');

            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $obj_PHPExcel = $objReader->load($tmp_file, $encode = 'utf-8');//加载文件内容,编码utf-8
            $excel_array = $obj_PHPExcel->getsheet(0)->toArray();//转换为数组格式
            array_shift($excel_array);//删除第一个数组(标题);
            $datas = [];
            foreach ($excel_array as $k => $v) {
                $datas[$k]['product_id'] = $v[0];
                $datas[$k]['mobile'] = $v[1];
                $datas[$k]['buynum'] = $v[2];
                $datas[$k]['price'] = $v[3];
            }

            $data['concat'] = '用户';
            $data['remark'] = $this->post('remark', '通过后台管理员下单');
            $data['user_id'] =  15439;//下单用户
            $data['time'] = time();
            $data['key'] = getSelfSignStr($data);
            $data['list'] = json_encode($datas);
            $host =  $_SERVER['HTTP_HOST']?$_SERVER['HTTP_HOST']: $_SERVER['SERVER_NAME'];
            $url =  $host.'/api/Order/createBatchOrder';
            curlPost($url,$data); //curl创建订单
            $this->success('创建成功,',url('system/order/orderlist'));

        }
    }

    private function verfiyProduct($product_id, $price_id, $buynum){
        $product = Db::name('product p')
            ->field('c.*, p.product_name, p.product_status, p.product_del, p.product_reviewstatus,
            p.price_type, p.product_returnall, p.product_reservation, p.product_isexpress, 
            p.product_timelimit, p.product_numlimit, p.product_numlimit_num, p.product_starttime, p.product_endtime, 
            p.product_startusetime, p.product_endusetime, p.merchant_id, p.sold_out')
            ->join('product_price c', 'c.product_id = p.product_id', 'left')
            ->join('merchant m', 'm.merchant_id = p.merchant_id', 'left')
            ->where(['p.product_id'=>$product_id, 'c.price_id'=>$price_id, 'm.merchant_status'=>2, 'c.price_status'=>1])
            ->find();
        if(!$product){
            $this->error('商品不存在', 400);
        }
        if($product['sold_out'] == 1){
            $this->error('商品已售罄', 400);
        }
        if($product['product_isexpress'] == 2 && $product['product_reservation'] == 2){
            $this->error('快递类产品不支持后台下单');
        }
        if($product['product_del'] == 1 || $product['product_status'] == 0){
               $this->error('商品售罄已下架', 400);
        }
        if($product['product_reviewstatus'] != 2){
               $this->error('商品未审核通过', 400);
        }
        if($product['product_buynum'] >= $product['product_totalnum']){
               $this->error('商品已售罄', 400);
        }
        if($product['product_buynum']+$buynum > $product['product_totalnum']){
               $this->error('商品库存不足', 400);
        }
        if($product['price_type'] == 2){
               $this->error('商品价格类型异常', 400);
        }
        $product['product_sku'] = $product['product_totalnum']-$product['product_buynum']; //剩余库存
        return $product;
    }


}
