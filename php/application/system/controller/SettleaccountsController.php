<?php
/**
 * Created by PhpStorm.
 * User: Admini
 * Date: 2018/11/10
 * Time: 17:05
 */

namespace app\system\controller;


use app\common\AdminBaseController;
use app\common\model\ProcedureModel;
use app\system\model\PaginationModel;
use think\Db;

/**
 * 次月结算 运营奖金+玩主奖金
 * Class FinanceController
 * @package app\system\controller
 */
class SettleaccountsController extends AdminBaseController
{

    public function index(){
        $page = $this->get('page', 1);
        $pagesize = 20;
        $where = [];
        //查询总记录
        $count = Db::table('view_forJiesuanCiYue a')->where($where)->count();

        $list = Db::table('view_forJiesuanCiYue a')
            ->field('a.*, u.nickname')
            ->where($where)
            ->join('jay_user u', 'a.user_id = u.user_id', 'left')
            ->page($page, $pagesize)
            ->order('a.user_id asc')
            ->select();

        $return['list'] = $list;
        $pagination = new PaginationModel();
        $return['page'] = $pagination->getPage($count, $page, $pagesize);
        $this->assign('data', $return);
        return $this->display('user', true);
    }


    /**
     * 立即结算
     */
    public function settle(){
        $user_id = $this->get('user_id', 0);
        //1.查询用户数据
        $data = Db::table('view_forJiesuanCiYue')->where(['user_id'=>$user_id])->find();
        if(!$data){
            $this->error('没有数据');
        }
        if($data['record_amount'] > 0){
            $config = Db::name('parameter')->column('value', 'key');
            $param = $data['user_id'];
            $param .= ','.$data['record_amount'];
            $param .= ",{\"userId\":7".$data['user_id'].",\"userName\":\"\",\"orderNo\":\"\",\"adminId\":\"".session('admin.id')."\"}";
            $param .= ','.$data['taxfee_fuwu'];
            $param .= ','.$data['taxfee_geren'];
            $param .= ','.$data['taxfee_pingtai'];
            $pm = new ProcedureModel();
            $res = $pm->execute('jiesuan_commissionToCashAll', $param, '@error');
            if($res){
                $this->success('执行成功');
            }else{
                $this->error('执行失败');
            }
        }


    }

}