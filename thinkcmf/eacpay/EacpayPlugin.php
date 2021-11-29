<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaoying <360963804@qq.com>
// +----------------------------------------------------------------------
namespace plugins\eacpay;

use cmf\lib\Plugin;

use think\Request;
use plugins\eacpay\lib\Eacpay;
use plugins\eacpay\model\{
    Recharge,
    User,
    EacpayOrder,
    EacpayAddress,
};

class EacpayPlugin extends Plugin
{

    public $info = [
        'name'        => 'Eacpay',    //插件名字
        'title'       => 'Eacpay支付插件',   //标题
        'description' => 'Eacpay支付插件',   //描述
        'status'      => 0,          //关
        'author'      => 'Eacpay',       //作者
        'version'     => '1.0'       //版本号
    ];

    public $hasAdmin = 1; //插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        $exist = $this->q('show tables like "cmf_recharge"');
        if (!$exist) {
            $sql = "CREATE TABLE IF NOT EXISTS `cmf_recharge` (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `uid` int(10) DEFAULT '0' COMMENT '用户id',
                `total_fee` decimal(20,2) DEFAULT '0.00' COMMENT '费用',
                `out_trade_no` varchar(30) DEFAULT '' COMMENT '商户订单号',
                `transaction_id` varchar(30) DEFAULT '' COMMENT '第三方支付的支付号',
                `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
                `end_time` int(10) DEFAULT '0' COMMENT '完成时间',
                `type` tinyint(1) DEFAULT '1' COMMENT '支付类型 1=微信支付 2=支付宝支付 3=paypal',
                `status` tinyint(1) DEFAULT '1' COMMENT '1=未支付 2=已支付',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;";
            $this->q($sql,false);
        }
        $exist = $this->q('show tables like "cmf_eacpay_address"');
        if (!$exist) {
            $sql = "CREATE TABLE IF NOT EXISTS `cmf_eacpay_address` (
              `uid` mediumint(11) NOT NULL,
              `address` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                PRIMARY KEY (`address`)
              ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;";
            $this->q($sql,false);
        }
        $exist = $this->q('show tables like "cmf_eacpay_order"');
        if (!$exist) {
            $sql = "CREATE TABLE IF NOT EXISTS `cmf_eacpay_order` (
                  `uid` mediumint(11) NOT NULL DEFAULT '0',
                  `out_trade_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
                  `order_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
                  `amount` float(10, 4) NULL DEFAULT 0.0000,
                  `eac` float(10, 4) NULL DEFAULT 0.0000,
                  `real_eac` float(10, 4) NULL DEFAULT 0.0000,
                  `from_address` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                  `to_address` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                  `block_height` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                  `create_time` int(11) NULL DEFAULT 0,
                  `pay_time` int(11) NULL DEFAULT 0,
                  `last_time` int(11) NULL DEFAULT 0,
                  `status` enum('cancel','reject','wait','complete','payed') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                  `type` enum('recharge','withdrawl') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
                  `callback` varchar(255) COLLATE 'utf8_general_ci' NULL DEFAULT NULL,
                PRIMARY KEY (`order_id`)
              ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;";
            $this->q($sql,false);
        }
        return true; //安装成功返回true，失败false
    }
    private function q($sql,$query=true){
        if (strpos(cmf_version(), '6.') === 0) {
            if($query){
                return \think\facade\Db::query($sql);
            }else{
                return \think\facade\Db::execute($sql);
            }
        } else {
            if($query){
                return \think\Db::query($sql);
            }else{
                return \think\Db::execute($sql);
            }
        }
    }
    // 插件卸载
    public function uninstall()
    {
        return true; //卸载成功返回true，失败false
    }
    public function __call($method,$args){
        echo $method;
    }
    //实现的WxPay钩子方法
    public function eacpay($param)
    {
        $config = $this->getConfig();
        $Eacpay = new Eacpay($config);
        /*
        ["allow_cash"]=> string(1) "1" 
        ["recive_token"]=> string(34) "eZcwRzRDPiPvM6WUGQXMRLa5MAHkrwWP9t" 
        ["maxwaitpaytime"]=> string(3) "120" 
        ["bizhong"]=> string(3) "CNY" 
        ["receipt_confirmation"]=> string(1) "3" 
        ["notice"]=> string(102) "请不要修改付款页面的任何信息，否则系统无法识别订单将导致不会自动发货" 
        ["exhangeapi"]=> string(33) "https://api.aex.zone/v3/depth.php" 
        ["eacpay_server"]=> string(30) "https://blocks.deveac.com:4000"
        */
        $exchangedata = $Eacpay->getExchange($config['bizhong']);
        $eac_order_data = array(
            'currency'   => $config['bizhong'],
            'out_trade_no'=> $param['out_trade_no'],
            'exchangedata'=>$exchangedata,
            'order_id'   => $_SERVER['HTTP_HOST'].'_recharge_'.$param['out_trade_no'],
            'amount'     => $param['amount'],
            'eac'        => $param['amount'] / $exchangedata,
            'real_eac'   => 0,
            'to_address' => $config['recive_token'],
            'block_height'=>$Eacpay->get_block_height(),
            'create_time'=> time(),
            'pay_time'   => 0,
            'last_time'  => time(),
            'status'     => 'wait',
            'type'       => 'recharge',
            'callback'   => array_key_exists('callback',$param) ? $param['callback'] : ""
        );
        $EacpayOrder = new EacpayOrder();
        // 查询单个数据
        $eacorder = $EacpayOrder->where(['out_trade_no' => $eac_order_data['out_trade_no']])->find();
        if(!$eacorder){
            $eac_order_data = EacpayOrder::create($eac_order_data);
        }else{
            EacpayOrder::update($eac_order_data,array('out_trade_no'=> $eac_order_data['out_trade_no']),array(
                'amount','eac','real_eac',
                'to_address','block_height','create_time',
                'pay_time','last_time','status','type','callback'
            ));
        }
        $eacorder = $EacpayOrder->where(['out_trade_no' => $eac_order_data['out_trade_no']])->find();
        $eac_order_data['currency'] = $config['bizhong'];
        $this->assign($config);
        $this->assign($eac_order_data);
        echo $this->fetch('index');
    }
}
