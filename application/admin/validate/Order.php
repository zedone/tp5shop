<?php
namespace app\admin\validate;
use think\Validate;
class Order extends Validate
{
    protected $rule =   [
        'post_spent'  => 'require',
        'username'  => 'require',
        'distribution'  => 'require',
        'pay_status'  => 'require',
        'name'  => 'require',
        'phone'  => 'require',
        'address'  => 'require',
        'payment'  => 'require',
    ];
    
    protected $message =   [
        'post_spent.require'  => '数据不能为空',
        'username.require'  => '数据不能为空',
        'distribution.require'  => '数据不能为空',
        'pay_status.require'  => '数据不能为空',
        'name.require'  => '数据不能为空',
        'phone.require'  => '数据不能为空',
        'address.require'  => '数据不能为空',
        'payment.require'  => '数据不能为空',
    ];


}