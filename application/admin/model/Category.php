<?php
namespace app\admin\model;
use think\Model;
class Category extends Model
{
    protected $field=true;
    protected static function init()
    {

        category::beforeUpdate(function ($category) {
            // 商品id
            $categoryId=$category->id;
            // 新增商品属性
            $categoryData=input('post.');
            //修改筛选属性
            $attrIds=$categoryData['attr_id'];
            foreach ($attrIds as $key => $value) {
                if(empty($value)){
                    unset($attrIds[$key]);
                }
            }
            if($attrIds){
                $searchAttrIds=implode(',', $attrIds);
                $category->search_attr_ids=$searchAttrIds;
            }else{
                $category->search_attr_ids='';
            }
            //处理商品推荐位
            db('rec_item')->where(array('value_type'=>2,'value_id'=>$categoryId))->delete();
            if(isset($categoryData['recpos'])){
                foreach ($categoryData['recpos'] as $k => $v) {
                    db('rec_item')->insert(['recpos_id'=>$v,'value_id'=>$categoryId,'value_type'=>2]);
                }
            } 
        });

        category::afterInsert(function($category){
            //接受表单数据
            $categoryData=input('post.');
            $categoryId=$category->id;
            //处理商品推荐位
            if(isset($categoryData['recpos'])){
                foreach ($categoryData['recpos'] as $k => $v) {
                    db('rec_item')->insert(['recpos_id'=>$v,'value_id'=>$categoryId,'value_type'=>2]);
                }
            }
        });

        //处理筛选属性
        category::beforeInsert(function ($category) {
            //dump(input('post.'));die;
            //商品id
            $data=input('post.');
            $attrIds=$data['attr_id'];
            foreach ($attrIds as $key => $value) {
                if(empty($value)){
                    unset($attrIds[$key]);
                }
            }
            if($attrIds){
                $searchAttrIds=implode(',', $attrIds);
                $category->search_attr_ids=$searchAttrIds;
            }     
        });
    }
        

}