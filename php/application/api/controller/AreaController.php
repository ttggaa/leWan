<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/10/30
 * Time: 9:22
 * 城市控制器
 * 肖亚子
 */
namespace app\api\controller;
use app\api\model\AreaModel;

class AreaController extends ApiBaseController{

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取平台开通城市数据
     * 肖亚子
     */
    public function AreaOpenUp(){
        $Condition = array();
        $Condition["status"] = array("eq",1);
        $Condition["leveltype"] = array(array("eq",1),array("eq",2),"or");

        $List = AreaModel::AreaList($Condition);
        $List = self::AreaWordbook($List);

        $this->returnApiData('获取成功',200,$List);
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取全部城市数据
     * 肖亚子
     */
    public function CityDictionary(){
        $Condition = array();
        $List      = AreaModel::AreaList($Condition);
        $List      = self::AreaWordbook($List);

        $this->returnApiData('获取成功',200,$List);
    }

    /**
     * @param $List  城市数据
     * @return array
     * 城市数据进行转换组合
     * 肖亚子
     */
    private function AreaWordbook($List){
        $Province = array();
        $City     = array();
        $Area     = array();
        $AreaList = array();

        foreach ($List as $Key => $Val) {
            if($Val["leveltype"] == 1){
                $Province[] = $Val;
            }elseif ($Val["leveltype"] == 2){
                $City[] =  $Val;
            }elseif ($Val["leveltype"] == 3){
                $Area[] = $Val;
            }
        }

        foreach ($Province as $Key => $Val){
            $AreaList[$Key]["provincecode"] = $Val["areacode"];
            $AreaList[$Key]["provincename"] = $Val["name"];
            $AreaList[$Key]["provinceletter"] = strtoupper(substr( $Val["quanpin"], 0, 1 ));
            foreach ($City as $K => $V){
                if($Val["areacode"] == $V["parentid"]){
                    $AreaList[$Key]["city"][$K] = array("citycode"=>$V["areacode"],"cityname"=>$V["name"],"cityletter"=>strtoupper(substr( $V["quanpin"], 0, 1 )));

                    if ($Area){
                        foreach ($Area as $A => $S){
                            if($V["areacode"] == $S["parentid"]){
                                $AreaList[$Key]['city'][$K]["area"][]= array("areacode"=>$S["areacode"],"areaname"=>$S["name"],"arealetter"=>strtoupper(substr( $S["quanpin"], 0, 1 )));
                            }
                        }
                    }
                }
            }
        }

        return $AreaList;
    }
}