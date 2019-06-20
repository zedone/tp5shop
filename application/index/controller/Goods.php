<?php
namespace app\index\controller;
class Goods extends Base
{
    public function index($id)
    {
    	$goodsInfo=db('goods')->find($id);
    	//商品主图信息数组
    	$goodsThumbArr=array();
    	if($goodsInfo['og_thumb']){
    		$goodsThumbArr['sm_photo']=$goodsInfo['sm_thumb'];
    		$goodsThumbArr['mid_photo']=$goodsInfo['mid_thumb'];
    		$goodsThumbArr['big_photo']=$goodsInfo['big_thumb'];
    		$goodsThumbArr['og_photo']=$goodsInfo['og_thumb'];
    	}
    	//获取当前商品相册信息
    	$goodsPhotoRes=db('goods_photo')->field('sm_photo,mid_photo,big_photo,og_photo')->where(array('goods_id'=>$id))->select();
    	//将商品主图放到相册最前面
    	array_unshift($goodsPhotoRes, $goodsThumbArr);
        //获取并处理商品属性信息
        $gaArr=db('goods_attr')->alias('ga')->field('ga.*,a.attr_name,a.attr_type')->join('attr a',"ga.attr_id = a.id")->where(array('ga.goods_id'=>$id))->select();
        $radioAttrArr=array();
        $uniAttrArr=array();
        foreach ($gaArr as $k => $v) {
            if($v['attr_type'] == 1){
                $radioAttrArr[$v['attr_id']][]=$v;
            }else{
                $uniAttrArr[]=$v;
            }
        }
    	$this->assign([
    		'show_right'=>1,//文章列表和商品列表头部偏移判断
    		'goodsInfo' =>$goodsInfo,
    		'goodsPhotoRes'=>$goodsPhotoRes,
            'uniAttrArr'=>$uniAttrArr,
            'radioAttrArr'=>$radioAttrArr,
    		]);
        return view('goods');
    }

    public function ajaxGetMemberPrice($goods_id){
        $price=model('goods')->getMemberPrice($goods_id);
        return json($price);
    }
}
