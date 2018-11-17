<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/13
 * Time: 15:54
 * 开放银行接口
 * 肖亚子
 */

namespace app\system\controller;
use app\common\AdminBaseController;
use app\system\model\BankModel;
use think\Config;
use think\Url;

class BankController extends AdminBaseController{

    /**
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取开放银行列表
     * 肖亚子
     */
    public function BankList(){
        $List = BankModel::BankAll();

        if ($List){
            foreach ($List as $Key => $Val){
                $List[$Key]["bank_icon"] = Config("picture").$Val["bank_icon"];
                $List[$Key]["bank_back"] = Config("picture").$Val["bank_back"];
            }
        }
        $this->assign("data", $List);

        return $this->display('list', true);
    }

    public function BankDet(){
        if (Request()->isGet()){
            $Id = $this->get("id");

            $Condition["bank_id"] = array("eq",$Id);

            $BankData =  BankModel::BankDataFind($Condition);

            $this->assign("data",$BankData);
            return $this->display('det', true);
        }else{
            $Id        = $this->post("id");
            $Bank_icon = $this->post("bank_icon");
            $Bank_back = $this->post("bank_back");

            $Condition["bank_id"] = array("eq",$Id);
            $Data["bank_icon"]    = $Bank_icon;
            $Data["bank_back"]    = $Bank_back;

            $Data = BankModel::BankUpdate($Condition,$Data);

            if ($Data){
                $this->log('修改开放银行：ID'.$Id);
                $this->toSuccess('编辑成功', url("Bank/BankList"), 1);
            }else{
                $this->toError('编辑失败');
            }
        }
    }
}