<?php
namespace app\index\model;
use think\Model;
class Article extends Model
{
    public function getFooterArts()
    {
    	//获取帮助分类
        $helpCateRes=model('cate')->where(array('cate_type'=>3))->order('sort DESC')->select();
        foreach ($helpCateRes as $k => $v) {
        	$helpCateRes[$k]['arts']=$this->where(array('cate_id'=>$v['id']))->select();
        }
        return $helpCateRes;
    }

    //获取网店信息
    public function getShopInfo(){
    	$artArr=$this->where('cate_id','=',3)->field('id,title')->select();
    	return $artArr;
    }

    public function getArts($id,$limit){
        $arts=$this->where('cate_id','=',$id)->order('id DESC')->limit($limit)->select();
        return $arts;
    }

    //插入排序
    //第一次先取第二个数，然后和第一个数比较，第二个数小雨第一个 和第一个数互换位置
    public function insertSort($arr){
        $len=count($arr);
        for($i=1;$i<$len;$i++){
            $tmp=$arr[$i];
            for($j=$i-1;$j>=0;$j--){
                if($arr[$j]>$tmp){
                    $arr[$j+1]=$arr[$j];
                    $arr[$j]=$tmp;
                }else{
                    break;
                }
            }
        }
        return $arr;
    }

    public function shuzu(){
        $arr=array('a','b','c','d','e','f','g');//目标数组
        $i_arr=array('1','2');//要插入的数组
        $n=3;//插入的位置
        array_splice($arr,$n,0,$i_arr);
        print_r($arr);
    }

    //最大公约数 16 6
    public function gcd($a, $b){
        while ($b != 0) {
            $tmp = $a % $b;
            $a = $b;
            $b = $tmp;
       
        }
        return $a;
    }

    //快速排序
    public function quickSort($arr){
        $len=count($arr);
        if($len<=1){
            return $arr;
        }
        $left=[];
        $right=[];
        //默认第一个是中枢点
        for($i=1;$i<$len;$i++){
            
            if($arr[$i]<$arr[0]){
                $left[]=$arr[$i];
            }else{
                $right[]=$arr[$i];
            }
        }
        $right=self::quicksort($right);
        $left=self::quicksort($left);
        return array_merge($left,[$arr[0]],$right);
    }
    //冒泡排序
    public function battleSort($arr){
        $len=count($arr);
        $tmp=[];
        for($i=0;$i<$len;$i++){
            for($j=$i+1;$j<$len;$j++){
                if($arr[$i]>$arr[$j]){
                    $tmp=$arr[$i];
                    $arr[$i]=$arr[$j];
                    $arr[$j]=$tmp;
                }
            }
        }
        return $arr;
    }
    //选择排序 换位置
    public function selectSort($arr){
        $len=count($arr);

        for($i=0;$i<$len-1;$i++){
            //假设第0个位置是最小值
            $p=$i;
            for($j=$i+1;$j<$len;$j++){
                if($arr[$j]<$arr[$p]){
                    //确定最小值的位置
                    $p=$j;
                }
            }
            if($p!=$i){
                $tmp=$arr[$p];
                $arr[$p]=$arr[$i];
                $arr[$i]=$tmp;
            }
            
        }
        return $arr;
    }

    //斐波那契
    public function shulie(){
        //1,1,2,3,5,8
        $arr[1]=1;
        $arr[0]=1;
        for($i=2;$i<100;$i++){
            $arr[$i]=$arr[$i-1]+$arr[$i-2];
        }
        //echo join(",",$arr);
        return $arr;
    }

    //判断异位词
    public function yiweici(){

        $tmp[$s[$i]]++;
    }
  
}


