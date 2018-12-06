<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/10/19
 * Time: 15:09
 * 用户银行卡控制器
 * 肖亚子
 */

namespace app\system\Controller;
use think\Request;
use app\common\AdminBaseController;
use app\system\model\UserbankModel;

class UserbankController extends AdminBaseController{

    /**
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取全部用户银行卡
     * 肖亚子
     */
    public function BankList(){
        $Condition = array();
        $Psize     = $this->get("page",1);//当前分页页数默认第一页
        $Title     = $this->get("title");
        $BankId    = $this->get("bank_id",0);

        if ($Title){
            $Condition["b.account_name|b.account_number|b.account_tel|u.nickname|u.mobile"] = array("like","%$Title%");
        }
        if ($BankId){
            $Condition["b.bank_id"] = array("eq",$BankId);
        }

        $DataList = UserbankModel::BankList($Condition,$Psize,50);
        $BankList = UserbankModel::PlatformBank();

        if ($DataList["list"]){
            foreach ($DataList["list"] as $Key => $Val){
                $Province = UserbankModel::UserBankArea($Val["province"]);
                $City     = UserbankModel::UserBankArea($Val["city"]);
                $Area     = UserbankModel::UserBankArea($Val["area"]);

                $DataList["list"][$Key]["region"] = $Province."/".$City."/".$Area;
            }
        }

        $this->assign("title",$Title);
        $this->assign("bankid",$BankId);
        $this->assign("banklist",$BankList);
        $this->assign("data",$DataList);
        return $this->display('list', true);
    }

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 获取银行卡(城市/修改)
     * 肖亚子
     */
    public function BankArea(){
        if(Request()->isGet()){
            $Id   = intval($this->get("id"));
            $Data = UserbankModel::UserBankFind(array("ub_id"=>array("eq",$Id)));

            $this->assign('data', $Data);
            $this->assign('province', $this->getProvenceList());
            $this->assign('city', $this->getCityList($Data['province']));
            $this->assign('area', $this->getAreaList($Data['city']));

            return $this->display('bankarea', false);
        }else{
            $Id = intval($this->get("id"));

            $Data['province'] = intval($this->post('provence_id' ));
            $Data['city']     = intval($this->post('city_id'));
            $Data['area']     = intval($this->post('area_id'));

            parent::Tpl_Empty($Data['province'],"请选择省份");
            parent::Tpl_Empty($Data['city'],"请选择城市");
            parent::Tpl_Empty($Data['area'],"请选择区/县");

            $Date = UserbankModel::UserBankUpdate($Id,$Data);

            if ($Date){
                $this->toSuccess('编辑成功', '', 2);
            }else{
                $this->toError('编辑失败');
            }
        }
    }
}