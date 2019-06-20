<?php
namespace app\index\controller;

class Category extends Base 
{
    public function index($id)
    {
    	$categoryInfo=db('category')->where('id',$id)->find($id);
        //计算筛选属性的逻辑
        if(!cache('attrRes_'.$id)){
            $sai=$categoryInfo['search_attr_ids'];
            $attrRes=db('attr')->field('id,attr_name')->where(['id'=>['in',$sai]])->select();
            //$attrValues=[];
            foreach ($attrRes as $key => $value) {
                $attrValues=db('goods_attr')->field('attr_id,attr_value,goods_id')->where('attr_id',$value['id'])->select();

                //dump($attrValues);die;
                //判断当前商品是否属于当前栏目
                foreach ($attrValues as $k1 => $v1) {
                    $categoryId=db('goods')->where('id',$v1['goods_id'])->value('category_id');
                    if($categoryId != $id){
                        unset($attrValues[$k1]);
                    }
                }
                if(!$attrValues){
                    unset($attrRes[$key]);
                }else{
                    $attrRes[$key]['attr_values'] = assoc_unique($attrValues,'attr_value');
                }
            }
            cache('attrRes_'.$id,$attrRes,3600);
        }else{
            $attrRes=cache('attrRes_'.$id);
        }
        //dump($attrRes);die;
        //缓存价格区间
        if(!cache('priceSection'.$id)){
            //计算区间价格
            $goodsPrice=db('goods')->field('MIN(shop_price) min_price,MAX(shop_price) max_price')->where('category_id',$id)->find();
            $sprice = ceil(($goodsPrice['max_price']-$goodsPrice['min_price'])/$categoryInfo['ps_num']);
            $priceSection=[];
            $firstPrice=(int)$goodsPrice['min_price'];
            for($i=0;$i<$categoryInfo['ps_num'];$i++){
                if($i==0){
                    $priceSection[]=$firstPrice.'-'.(ceil(($sprice+$firstPrice)/10)*10-1);
                }elseif($i ==$categoryInfo['ps_num']-1){
                    $priceSection[]=(ceil($firstPrice/10)*10).'-'.(int)$goodsPrice['max_price'];
                }else{
                    $startPrice=ceil($firstPrice/10)*10;
                    $endPrice=(ceil(($sprice+$firstPrice)/10)*10-1);
                    $goodsCount=db('goods')->where(
                        array(
                            'shop_price'=>array('between',array($startPrice,$endPrice)),
                            'category_id'=>array('eq',$id),
                            'on_sale'=>array('eq',1))
                        )->count();
                    if($goodsCount){
                        $priceSection[]= $startPrice.'-'.$endPrice;
                    }
                    
                }
                $firstPrice=$sprice+$firstPrice;
            }
            //缓存价格
            cache('priceSection'.$id,$priceSection,3600);
        }else{
                $priceSection=cache('priceSection'.$id);
            }
        $ob=input('ob')?input('ob'):'xl';
        $ow=input('ow')?input('ow'):'desc';
        $price=input('price');
        $goodsRes=model('goods')->search_goods($id);
        //dump($goodsRes);die;
    	// print_r($attrRes);
    	//dump($priceSection);die;
    	$this->assign('attrRes',$attrRes);
        $this->assign('priceSection',$priceSection);
        $this->assign('goodsRes',$goodsRes);
        $this->assign('ob',$ob);
        $this->assign('ow',$ow);
        $this->assign('price',$price);
        $this->assign('cateId',$id);
       // $this->assign('price',$price);
        return view('category');
    }

    public function getCateInfo($id){
    	$mCategory=model('Category');
    	//获取二级和三级子分类
    	$cateRes=$mCategory->getSonCates($id);
    	//获取关联词
    	$cwRes=$mCategory->getCategoryWords($id);
    	//获取关联品牌及推广信息
    	$brands=$mCategory->getCategoryBrands($id);
    	// dump($brands); die;
    	$data=array();
    	$cat=''; 
    	foreach ($cateRes as $k => $v) {
    		$cat.='<dl class="dl_fore1"><dt><a href="'.url('index/Category/index',['id'=>$v['id']]).'" target="_blank">'.$v['cate_name'].'</a></dt><dd>';
			    	foreach ($v['children'] as $k1 => $v1) {
			    		$cat.='<a href="'.url('index/Category/index',['id'=>$v1['id']]).'" target="_blank">'.$v1['cate_name'].'</a>';
			    	}
			$cat.='</dd></dl>
				<div class="item-brands"><ul></ul></div>
				<div class="item-promotions"></div>';
    	}
		
		$channels='';
		foreach ($cwRes as $k => $v) {
			$channels.='<a href="'.$v['link_url'].'" target="_blank">'.$v['word'].'</a>';
		}
		$bransAdContent='';
		$bransAdContent.='
		<div class="cate-brand">';
	            foreach ($brands['brands'] as $k => $v) {
	            	$bransAdContent.=
	            	'<div class="img">
	            		<a href="'.$v['brand_url'].'" target="_blank" title="'.$v['brand_name'].'"><img src="'.config('view_replace_str.__uploads__').'/'.$v['brand_img'].'"></a>
	            	</div>';
	            }
	    $bransAdContent.='</div>';
	    $bransAdContent.='
		<div class="cate-promotion">
	        <a href="'.$brands['promotion']['pro_url'].'" target="_blank"><img width="199" height="97" src="'.config('view_replace_str.__uploads__').'/'.$brands['promotion']['pro_img'].'"></a>
	    </div>';
    	$data['topic_content']=$channels;
    	$data['cat_content']=$cat;
    	$data['brands_ad_content']=$bransAdContent;
    	$data['cat_id']=$id;
    	return json($data);
    }


}
