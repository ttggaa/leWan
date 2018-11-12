<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/10/27
 * Time: 16:02
 */

namespace app\api\controller;

use app\api\controller\ApiBaseController;
use app\api\model\BannerModel;
use Think\Exception;

class BannerController extends ApiBaseController{

    public function HomePageList(){
        try {
            $Condition = array();
            $ProCode   = intval($this->headerData["provincecode"]);//省code
            $CityCode  = intval($this->headerData["citycode"]);
            $Cate      = intval($this->post("cate", 0));

            parent::Tpl_Empty($ProCode,"请选择城市",2);
            parent::Tpl_Empty($CityCode,"请选择城市",2);

            $Condition["city"] = array("eq", $CityCode);

            if ($Cate) {
                $Condition["type"]   = array("eq", 2);
                $Condition["cat_id"] = array("eq", $Cate);
            }else{
                $Condition["type"] = array("eq", 1);
            }

            $List = BannerModel::BannerList($Condition);
            $this->returnApiData('获取成功', 200, $List);
        }catch (Exception $e) {
            Tpl_Abnormal($e->getMessage());  //数据库异常抛出
        }
    }


}