<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/10/30
 * Time: 16:02
 * 商品分类控制器
 * 肖亚子
 */

namespace app\api\controller;
use app\api\model\ProductcategoryModel;


class ProductcategoryController extends ApiBaseController{

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取首页分类列表
     * 肖亚子
     */
    public function CategoryList(){
        $Condition = array();

        $Condition["category_status"] = array("eq",1);
        $Condition["category_del"]    = array("eq",0);

        $List =  ProductcategoryModel::CateList($Condition);

        foreach ($List as $Key => $Val){
            $List[$Key]["cate_icon"] = $Val["cate_icon"];
        }

        $this->returnApiData('获取成功',200,$List);
    }

}