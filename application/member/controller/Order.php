<?php
namespace app\member\controller;
use app\index\controller\Base;
class Order extends Base
{  
    public function orderList(){
        $uid=session('uid');
        $orderStatus=input('order_status');
        //组合条件
        $map=[];
        if(!$orderStatus){
            $orderStatus=1;
        }

        if($orderStatus==1){
            $map['id']=['>',1];
        }elseif($orderStatus==2){
            $map['order_status']=0;
        }elseif($orderStatus==3){
            $map['pay_status']=0;
        }else{
            $map['post_status']=1;
        }

        $orderRes=db('order')->field('id,out_trade_no,user_id,order_total_price,order_status,pay_status,post_status,order_time,name')->where('user_id',$uid)->where('del_status',0)->where($map)->select();
        foreach ($orderRes as $k => $v) {
            $goodsRes=db('order_goods')->alias('og')->field('g.mid_thumb,g.goods_name,og.*')->join('goods g','g.id=og.goods_id')->where('order_id',$v['id'])->select();
            $orderRes[$k]['goods']=$goodsRes;
            //dump($v);
        }
        //全部订单数量
        $totalCount=db('order')->where('del_status',0)->count();
        //待确认的订单数量
        $notDoneCount=db('order')->where(['del_status'=>0,'order_status'=>0])->count();
        //待付款
        $notPayCount=db('order')->where(['del_status'=>0,'pay_status'=>0])->count();
        //待收货
        $notGetCount=db('order')->where(['del_status'=>0,'post_status'=>1])->count();
        //$notGetCount=db('order')->where(['del_status'=>0,'order_status'=>0])->count();
        //dump($orderRes);die;
        $this->assign([
            'orderRes'=>$orderRes,
            'show_right'=>1,
            'totalCount'=>$totalCount,
            'notDoneCount'=>$notDoneCount,
            'notPayCount'=>$notPayCount,
            'notGetCount'=>$notGetCount,
            'orderStatus'=>$orderStatus
            ]);
        return view();
    }

    public function orderDetail(){
        $oid=input('id');
        $orderLists=db('order')->where('id',$oid)->find();
        $orderGoods=db('order_goods')->alias('og')->field('og.*,g.sm_thumb')->join('goods g','g.id=og.goods_id')->where('order_id',$orderLists['id'])->select();
        //dump($orderGoods);die;
        $this->assign('orderLists',$orderLists);
        $this->assign('orderGoods',$orderGoods);
        return view();
    }

    public function delOrder(){
        $orderId=input('id');
        $save=db('order')->update(['id'=>$orderId,'del_status'=>1]);
        if($save){
            $this->success('删除订单成功','member/Order/orderList');
        }else{
            $this->error('删除订单失败');
        }
    }
}
