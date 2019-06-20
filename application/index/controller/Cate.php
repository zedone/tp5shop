<?php
namespace app\index\controller;
use catetree\Catetree;
class Cate extends Base
{
    public function index($id)
    {
    	$cate=db('cate');
    	//获取当前栏目及其子栏目id，返回数组
    	$cateTree=new Catetree();
    	$ids=$cateTree->childrenids($id,$cate);
    	$ids[]=$id;
    	$map['cate_id']=array('IN',$ids);
    	
        $cacheArtResName=$id.'_artRes';
        if(cache($cacheArtResName)){
            $artRes=cache($cacheArtResName);
        }else{
            $artRes=db('article')->where($map)->select();
            if($this->config['cache'] == '是'){
                cache($cacheArtResName,$artRes,$this->config['cache_time']);
            }
        }
    	// 当前栏目基本信息
    	$cates=$cate->find($id);
    	//普通左侧栏目分类
        if(cache('comCates')){
            $comCates=cache('comCates');
        }else{
            $comCates=model('cate')->getComCates();
            if($this->config['cache'] == '是'){
                cache('comCates',$comCates,$this->config['cache_time']);
            }
        }
        //帮助左侧栏目分类
        if(cache('helpCates')){
            $helpCates=cache('helpCates');
        }else{
            $helpCates=model('cate')->shopHelpCates();
            if($this->config['cache'] == '是'){
                cache('helpCates',$helpCates,$this->config['cache_time']);
            }
        }
    	$this->assign([
    		'show_right'=>1,//文章列表和商品列表头部偏移判断
    		'comCates'=>$comCates,
    		'helpCates'=>$helpCates,
    		'artRes'=>$artRes,//当前栏目及其子栏目里的文章
    		'cates'=>$cates,//当前栏目基本信息
    		]);
        return view('cate');
    }
}
