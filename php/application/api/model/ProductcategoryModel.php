<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/10/30
 * Time: 16:05
 */

namespace  app\api\model;
use think\Db;
use think\Request;

class ProductcategoryModel{

    public static function TableName(){
        return Db::name("product_category");
    }

    /**
     * @param array $COndition   查询条件
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 获取商品分类
     * 肖亚子
     */
    public function CateList($COndition = array()){
        $Data = self::TableName()->where($COndition)->select();

        return $Data;
    }
}