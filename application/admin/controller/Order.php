<?php
namespace app\admin\controller;
use think\Controller;
class Order extends Controller
{
    //订单查询
    public function orderSelect(){
        return view();
    }
    public function lst(){
        //要导出订单的状态，默认为导出所有 
        $status='all'; 
        if(request()->isPost()){ 
            $data=input('post.');    
            $where=[];
            $selectValue=trim($data['select_value']);
            if($data['select_base']=='out_trade_no'){       
                $where['o.out_trade_no']=['=',$selectValue];
            }else{
                $userId=db('user')->where('username',$selectValue)->value('id');
                $where['o.user_id']=['=',$userId];
            }
        }
        $getData=input('get.');
        //dump($getData);die;
        //no_paied未支付
        if(isset($getData['status'])){
            $status = $getData['status'];
            $where=[];
            //$selectValue=trim($getData['status']);
            if($getData['status']=='paid'){       
                $where['o.pay_status']=['=',1];
            }elseif($getData['status']=='no_paid'){
                $where['o.pay_status']=['=',0];     
            }elseif($getData['status']=='no_post'){
                $where['o.post_status']=['=',0];       
            }elseif($getData['status']=='posted'){
                $where['o.post_status']=['=',1];         
            }
        }

        if(!isset($where)){
                $where=1;
            }
    	$OrderRes=db('order')->field('o.*,u.username')->alias('o')->join('user u',"u.id=o.user_id")->where($where)->order('id DESC')->paginate(10,false,['query'=>request()->param()]);
       
    	$this->assign([
    		'OrderRes'=>$OrderRes,
            'orderStatus'=>$status
    		]);
        return view('list');
    }
    //导出订单
    public function exportOrders(){
        $phpexcelSrc=APP_PATH.'../plus/PHPexcel/PHPexcel.php';
        include($phpexcelSrc);
        $phpexcel=new \PHPExcel();
        //定义第一张表
        $phpexcel->setActiveSheetIndex(0);
        $sheet=$phpexcel->getActiveSheet();
        $getData=input('get.');
        //dump($getData);die;
        //no_paied未支付
        if(isset($getData['status'])){
            $status = $getData['status'];
            $where=[];
            //$selectValue=trim($getData['status']);
            if($getData['status']=='paid'){       
                $where['o.pay_status']=['=',1];
            }elseif($getData['status']=='no_paid'){
                $where['o.pay_status']=['=',0];     
            }elseif($getData['status']=='no_post'){
                $where['o.post_status']=['=',0];       
            }elseif($getData['status']=='posted'){
                $where['o.post_status']=['=',1];         
            }else{
                $where['o.post_status']=['>',-1];    
            }
        }

        if(!isset($where)){
                $where=1;
            }
        $OrderRes=db('order')->field('o.*,u.username')->alias('o')->join('user u',"u.id=o.user_id")->where($where)->order('id DESC')->select();
        $arr=[
            'id'=>'订单id',
            'out_trade_no'=>'订单编号',
            'goods_total_price'=>'商品总额',
            'pay_status'=>'支付状态',
            'post_status'=>'配送状态',
            'order_status'=>'订单状态',
            'distribution'=>'配送方式',
            'payment'=>'支付方式',
            'name'=>'收货人',
            'phone'=>'手机号',
            'username'=>'用户名',
            'order_time'=>'下单时间',
        ];
        array_unshift($OrderRes,$arr);
        $row=0;
        foreach ($OrderRes as $k => $v) {
            $row+=1;
            //支付状态处理
            if($v['pay_status']==0){
                $v['pay_status']='未支付';
            }else{
                $v['pay_status']='已支付';
            }
            if($v['post_status']==0){
                $v['post_status']='未发货';
            }else{
                $v['post_status']='已发货';
            }
            if($v['payment']==1){
                $v['payment']='支付宝';
            }else{
                $v['payment']='微信';
            }
            if($v['order_status']==0){
                $v['order_status']='未确认';
            }elseif($v['order_status']==1){
                $v['order_status']='已确认';
            }elseif($v['order_status']==2){
                $v['order_status']='申请退款';
            }
            if($k){
                $v['order_time']=date('Y-m-d H:i',$v['order_time']);
            }
            
            //dump($OrderRes);die;
            $sheet->setCellValue('A'.$row,$v['id'])
                  ->setCellValue('B'.$row,$v['out_trade_no'])
                  ->setCellValue('C'.$row,$v['goods_total_price'])
                  ->setCellValue('D'.$row,$v['pay_status'])
                  ->setCellValue('E'.$row,$v['order_status'])
                  ->setCellValue('F'.$row,$v['post_status'])
                  ->setCellValue('G'.$row,$v['distribution'])
                  ->setCellValue('H'.$row,$v['payment'])
                  ->setCellValue('I'.$row,$v['name'])
                  ->setCellValue('I'.$row,$v['phone'])
                  ->setCellValue('I'.$row,$v['username'])
                  ->setCellValue('I'.$row,$v['order_time']);
        }
        // header('Content-Type:application/vnd.ms-excel');//设置下载前的头信息
        // header('Content-Disposition:attachment;filename="dingdan.xlsx"');
        // header('Cache-Control:max-age=0');
        // $phpwriter=new \PHPExcel_Writer_Excel2007($phpexcel);
        // $phpwriter->save('php://output');
       
        // $this->assign([
        //     'OrderRes'=>$OrderRes,
        //     'orderStatus'=>$status
        //     ]);
    }
    //商品详情
    public function detail($id){
        $orderInfo=db('Order')->field('o.*,u.username')->alias('o')->join('user u',"u.id=o.user_id")->find($id);
        $this->assign('orderInfo',$orderInfo);
        return view();
    }
    //订单商品展示
    public function orderGoods($id){
        $orderGoodsRes=db('order_goods')->where('order_id',$id)->select();
        $this->assign('orderGoodsRes',$orderGoodsRes);
        return $this->fetch();
    }
    public function orderGoodsDel($id){
        $del=db('order_goods')->delete($id);
        $this->success('删除订单商品成功');
    }
    public function orderGoodsEdit(){
        if(request()->isPost()){
            $data=input('post.');
            $save=db('order_goods')->update($data);
            if($save!==false){
                $this->success('修改订单商品成功');
            }else{
                $this->error('修改失败');
            }
        }
        $orderGoodsId=input('id');
        $orderGoodsInfo=db('order_goods')->find($orderGoodsId);
        $this->assign('orderGoodsInfo',$orderGoodsInfo);

        return $this->fetch();
    }

    //删除订单
    public function del($id){
        //删除订单商品表关联数据
        db('order_goods')->where('order_id',$id)->delete();
        $del=db('order')->delete($id);
        if($del){
            $this->success('删除订单成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function add()
    {
    	if(request()->isPost()){
    		$data=input('post.');
    		// $data['Order_url'];  http://
    		if($data['Order_url'] && stripos($data['Order_url'],'http://') === false){
    			$data['Order_url']='http://'.$data['Order_url'];
    		}
    		//处理图片上传
    		if($_FILES['Order_img']['tmp_name']){
    			$data['Order_img']=$this->upload();
    		}
    		//验证
    		$validate = validate('Order');
    		if(!$validate->check($data)){
			    $this->error($validate->getError());
			}
    		$add=db('Order')->insert($data);
    		if($add){
    			$this->success('添加品牌成功！','lst');
    		}else{
    			$this->error('添加品牌失败！');
    		}
    		return;
    	}
        return view();
    }

    public function edit($id)
    {
    	if(request()->isPost()){
    		$data=input('post.');
            $userId=db('user')->where('username',$data['username'])->find('id');
            if($userId){
                $data['user_id']=$userId;
            }
            $data['order_time']=strtotime($data['order_time']);
    		// $data['Order_url'];  http://
    		// if($data['Order_url'] && stripos($data['Order_url'],'http://') === false){
    		// 	$data['Order_url']='http://'.$data['Order_url'];
    		// }
            //dump($data);die; 
    	
    		//验证
   //  		$validate = validate('Order');
   //  		if(!$validate->check($data)){
			//     $this->error($validate->getError());
			// }
    		$save=db('order')->strict(false)->update($data);
    		if($save !== false){
    			$this->success('修改订单成功！','lst');
    		}else{
    			$this->error('修改订单失败！');
    		}
    		return;
    	}
    	//$id=input('id');
    	$orderInfo=db('Order')->field('o.*,u.username')->alias('o')->join('user u',"u.id=o.user_id")->find($id);
    	$this->assign([
    		'orderInfo'=>$orderInfo,
    		]);
        return view();
    }


    //上传图片
    public function upload(){
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('Order_img');
    
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