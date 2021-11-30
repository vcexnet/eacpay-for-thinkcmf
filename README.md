##Eacpay支付插件使用帮助
+ 插件丢插件目录下\public\plugins\

+ 安装插件并配置各个参数<br>
加入hook配置 \app\portal\hooks.php<br>
 >
    'eacpay' => [
        "type"        => 3,
        "name"        => '调用Eacpay支付',
        "description" => "调用Eacpay支付",
        "once"        => 0
    ],


+ 前台在需要使用eacpay的地方加上
 >
    <?php 
        $order=array(
            "out_trade_no"=>"订单号",
            "amount"=>"金额",
            "callback"=>"回调控制器,如:\app\portal\controller\ArticleController"
        );
    ?>
    <hook name="eacpay" param="param"/>

+ 在回调控制器中添加方法eacpay_notify;
 >
    function eacpay_notify(\$state=0,\$out_trade_no='',\$amount=0){
        if(\$state == 1){
            //用户已支付且金额一致
        }elseif(\$state == 2){
            //用户已支付,但是金额不一致
        }else{
        }
    }
+ 后台同步钩子
