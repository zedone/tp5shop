<?php
namespace app\admin\controller;
use think\Controller;
class User extends Controller
{
    public function lst()
    {
        $userRes=db('user')->order('id DESC')->paginate(2);
        $this->assign([
            'userRes'=>$userRes,
            ]);
        return view('list');
    }

    public function add()
    {
        if(request()->isPost()){
            $data=input('post.');
            
            $validate = validate('user');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
            $add=db('user')->insert($data);
            if($add){
                $this->success('添加品牌成功！','lst');
            }else{
                $this->error('添加品牌失败！');
            }
            return;
        }
        return view();
    }

    public function edit()
    {
        if(request()->isPost()){
            $data=input('post.');
            // $data['user_url'];  http://
            if($data['user_url'] && stripos($data['user_url'],'http://') === false){
                $data['user_url']='http://'.$data['user_url'];
            }
            //处理图片上传
            if($_FILES['user_img']['tmp_name']){
                $oldusers=db('user')->field('user_img')->find($data['id']);
                $olduserImg=IMG_UPLOADS.$oldusers['user_img'];
                if(file_exists($olduserImg)){
                    @unlink($olduserImg);
                }
                $data['user_img']=$this->upload();
            }
            //验证
            $validate = validate('user');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
            $save=db('user')->update($data);
            if($save !== false){
                $this->success('修改品牌成功！','lst');
            }else{
                $this->error('修改品牌失败！');
            }
            return;
        }
        $id=input('id');
        $users=db('user')->find($id);
        $this->assign([
            'users'=>$users,
            ]);
        return view();
    }

    public function del($id)
    {
        $user=db('user');
        $users=$user->field('user_img')->find($id);
        $userImg=IMG_UPLOADS.$users['user_img'];
        if(file_exists($userImg)){
            @unlink($userImg);
        }
        $del=$user->delete($id);
        if($del){
            $this->success('删除品牌成功！','lst');
        }else{
            $this->error('删除品牌失败！');
        }
    }

    //上传图片
    public function upload(){
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('user_img');
    
    // 移动到框架应用根目录/public/uploads/ 目录下
    if($file){
        $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS .'uploads');
        if($info){
            return $info->getSaveName();
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
            die;
        }
    }
}


}