<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 Tangchao All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaoying <360963804@qq.com>
// +----------------------------------------------------------------------
namespace plugins\eacpay\controller;

use cmf\controller\PluginBaseController;
use plugins\eacpay\lib\Eacpay;
use plugins\eacpay\model\{
    Recharge,
    User,
    EacpayOrder,
    EacpayAddress,
};

class IndexController extends PluginBaseController
{

    //pc扫码支付
    public function qrcode()
    {
        $eacpay_order_id = Input('eacpay_order_id');
        $order=array(
            'order_id'=>$eacpay_order_id,
            'eac'=>1,
            'to_address'=>'asghduyadsgfuiygsdiufdfgauisdg',
        );
	    Eacpay::qrcode("earthcoin:{$order['to_address']}?amount={$order['eac']}&message=".$order['order_id']);
    }
    public function check()
    {
        $config = $this->getPlugin()->getConfig();
        $EacpayOrder = new EacpayOrder();
        $Eacpay = new Eacpay($config);
        $eacorder = $EacpayOrder->where('order_id',Input('eacpay_order_id'))->find();
        $ret = $Eacpay->check($eacorder);
        echo json_encode($ret);
    }

}
