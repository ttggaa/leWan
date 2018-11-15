<?php
namespace app\system\controller;

use app\common\AdminBaseController;
use think\Request;
use think\Db;
use think\Log;
use think\Cache;
use think\Config;
use think\Session;
use app\common\RegExpression;
use app\common\Md5Help;
use app\common\SysHelp;
use app\system\model\NodesModel;

/**
 * 专门处理ajax请求
 * Enter description here ...
 * @author Administrator
 *
 */
class AjaxController extends AdminBaseController
{
    
    /**
     * 单图片上传
     * Enter description here ...
     */
    public function uploadSingleImage(){
        // 获取表单上传文件, 参数name对应
        $file = request()->file('img');
        if(empty($file)){
            return $this->ajaxReturn('请选择文件');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $savepath = 'uploads';
        $info = $file->move(ROOT_PATH .'' . DS . $savepath);
        if($info){
            $filename = $info->getSaveName();
            //拼接返回图片地址
            $data['url']      = Config("picture").$filename;
            $data['filename'] = $filename;
            //记录数据
            $this->addUploadRecord($filename);
            return $this->ajaxReturn('success', 1, $data);
        }else{
            // 上传失败获取错误信息
            return $this->ajaxReturn($file->getError(), 0);
        }
    }
    
    
    /**
     * 64位图片上传地址
     * Enter description here ...
     */
    public function upload64Image(){
        $base64_img = Request::instance()->post('img');
        $folder = date('Ymd');
        $up_dir = ROOT_PATH .'' . DS .'uploads'.DS. $folder.DS;//存放在当前目录的upload文件夹下
        if(!file_exists($up_dir)){
            mkdir($up_dir,0777);
        }
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file_name = date('YmdHis_').'.'.$type;
                $new_file = $up_dir.$new_file_name;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                    
                    //拼接返回图片地址
                    $data['url'] = 'http://'.DOMAIN.'/uploads/'.$folder.'/'.$new_file_name;
                    $data['filename'] = $folder.DS.$new_file_name;
            
                    return $this->ajaxReturn('success', 1, $data);
                }else{
                    return $this->ajaxReturn('图片上传失败');
                }
            }else{
                return $this->ajaxReturn('图片上传类型错误');
            }
        
        }else{
            return $this->ajaxReturn('文件错误');
        }
    }
    
    /**
     * 添加上传文件记录
     * 记录到表中，等保存实体的时候，删除对应的记录，方便以后根据这个记录表删除无效的图片
     * @param $filename
     */
    private function addUploadRecord($filename){
        $data['fileurl'] = $filename;
        $data['addtime'] = time();
        Db::name('sys_temp_uploadimages')->insert($data);
    }
    
    
    /**
     * 删除图片
     * Enter description here ...
     */
    public function deleteimg(){
        $filename = Request::instance()->post('filename');
        //文件地址
        $file = ROOT_PATH .'public' . DS . 'uploads' . DS . $filename;
        if(file_exists($file)){
            @unlink($file);
        }
        Db::name('sys_temp_uploadimages')->where('fileurl', $filename)->delete();
        return $this->ajaxReturn('success', 1);
    }
    
    
    /**
     * 点击顶部导航，获取二级菜单
     */
    public function clickGroup(){
    	$group_id = $this->post('group_id', 0);
    	$nm = new NodesModel();
    	$group_menus = $nm->secondMenu($group_id);
    	$menu = $group_menus[0];
    	//记录grouop_id
    	Session::set('admin.groupid', $menu['group_id']);
    	return $this->ajaxReturn('ok', 1, $menu['url']);
    }
    
    
    public function togglemenu(){
    	$status = $this->post('status', 0);
    	Session::set('admin.hidemenu', $status);
    	return $this->ajaxReturn('ok', 1);
    }
    
    
    /**
     * 加载城市
     */
    public function loadcity(){
    	$pcode = $this->post('pcode', '0');
        $status = $this->post('status', '0');
        if($status > 0){
            $map['status'] = $status;
        }
        $map['parentid'] = $pcode;
    	$list = Db::name('region')->field('`name` as city, id as `code`')->where($map)->order('id asc')->select();
    	return $this->ajaxReturn('ok', 1, $list);
    }
    
    public function loadarea(){
    	$ccode  = $this->post('ccode', '0');
        $status = $this->post('status', '0');
        if($status > 0){
            $map['status'] = $status;
        }
        $map['parentid'] = $ccode;
        $list = Db::name('region')->field('`name` as area, id as `code`')->where($map)->order('id asc')->select();
        return $this->ajaxReturn('ok', 1, $list);
    }


    public function loadMerchant(){
        $key = $this->post('key', '');
        if($key != ''){
            $where['merchant_contact|merchant_contactmobile|merchant_name'] = ['like', '%'.$key.'%'];
            $list = Db::name('merchant')->field('merchant_id, merchant_name, loginname, merchant_contact')->where($where)->select();
            return $this->ajaxReturn('ok', 1, $list);
        }
        return $this->ajaxReturn('ok', 1, []);
    }
    public function loadUser(){
        $key = $this->post('key', '');
        if($key != ''){
            $where['u.mobile|u.nickname|a.truename'] = ['like', '%'.$key.'%'];
            $list = Db::name('user u')->field('u.user_id, u.mobile, a.truename, u.nickname')
                    ->join('jay_user_auth a', 'a.user_id = u.user_id', 'left')
                    ->where($where)->select();
            return $this->ajaxReturn('ok', 1, $list);
        }
        return $this->ajaxReturn('ok', 1, []);
    }
    
}
