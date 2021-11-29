<?php
namespace plugins\eacpay\lib;
use plugins\eacpay\lib\QRcode;
use plugins\eacpay\model\{
    EacpayOrder,
    EacpayAddress,
};
/**
 * 
 * 接口访问类，包含所有微信支付API列表的封装，类中方法为static方法，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 * @author widyhu
 *
 */
 if(!function_exists('P')){
     function P($arr){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
     }
 }
class Eacpay
{
	/**
	 * SDK版本号
	 * @var string
	 */
	public static $VERSION = "1.0.0";
	public $debug = true;
    function __construct($config){
        $this->config = $config;
    }
	static function qrcode($str=''){
	    ob_clean();
	    QRcode::png($str,false,QR_ECLEVEL_L, 7,2, false);
	    exit();
	}    
    function check($vo=array()){
    	if($vo['status'] == 'complete'){
            return array('code'=>1,'msg'=>'ok');
        }
    	$exp = ceil((time()-$vo['create_time'])/60);
        $exp = $exp < 10 ? 10 : $exp;
    	$url = $this->config['eacpay_server']."/checktransaction/".$this->config['recive_token']."/".$vo['eac'].'/'.$vo['order_id'].'/'.$vo['block_height'].'/100';

        //echo $url;
        $ret = $this->get($url);
        //P($ret);
        $ret = json_decode($ret,true);
        if($ret['Error']){
            return array(
                'code'=>4,
                'msg'=>$ret['Error'] == 'Payment not found' ? '等待用户支付' : $ret['Error'],
                //'url'=>$url
            );
        }
        $data =array(
            'last_time' => time(),
            'pay_time' => time(),
            'status' 	=> 'payed',
            'real_eac'	=>0
        );
        $receiptConfirmation = $this->config['receipt_confirmation'];
        if ($ret['confirmations'] >= $receiptConfirmation) {
            $data['from_address'] = $ret['vout'][0]['scriptPubKey']['addresses'][0];
            $data['real_eac'] = round($ret['vout'][1]['value'],strlen(explode('.',$vo['eac'])[1]));
            if($data['from_address'] == $vo['to_address']){
                $data['from_address'] = $ret['vout'][1]['scriptPubKey']['addresses'][0];
                $data['real_eac'] = round($ret['vout'][0]['value'],strlen(explode('.',$vo['eac'])[1]));
            }
            $ret = array(
                'code'=>1,
                'msg'=>'ok',
                'url'=>$this->config['eacpay_redirect_url']
            );
            if($data['real_eac'] == $vo['eac']){
                $data['status']		=	'complete';
            }else{
    			$ret['code'] = 3;
    			$ret['msg']="交易数值不一致，请自行联系站长解决";
            }
			EacpayOrder::update($data, array("order_id"=>$vo["order_id"]));
			$callback = $vo['callback'];
			if($callback){
			    $cb = new $callback();
			    $cb->eacpay_notify(1,$vo['out_trade_no'],$vo['amount']);
			}
            return $ret;
            
        }else{
            return array(
                'code'=>2,
                'msg'=>'正在确认订单，请稍等...',
                'confirmations'=>$ret['confirmations'],
                'receiptConfirmation'=>$receiptConfirmation
            );
        }
    }
    
    function get_block_height(){
        if($this->debug){
            return 132852;
        }
    	return $this->get($this->config['eacpay_server']."/getblockcount/Block_height");
    }
    function getExchange($priceType = ''){
        if($this->debug){
            return 0.013285;
        }
        $priceType = $priceType ? $priceType : $this->config['bizhong'];
        $priceType = $priceType ? $priceType : 'CNY';
    	$ret = $this->post($this->config['exhangeapi'],array('mk_type'=>'usdt','coinname'=>'eac'));
    	$ret = json_decode($ret,true);
    	$unitPrice = 0;
    	$ret = $ret['data']['bids'];
    	
    	foreach( $ret as $k=>$v){
    		$unitPrice +=$v[0];
    		if($k==4){
    			break;
    		}
    	}
    	$unitPrice = round($unitPrice/5,6);
    	$hl = $this->huiulv($priceType);
    	$unitPrice=$unitPrice * $hl;
    	return round($unitPrice,6);
    }
    
    function huiulv($priceType='CNY'){
        if($priceType =='USD'){return 1;}
    	$hlret = $this->get('https://api.exchangerate-api.com/v4/latest/USD');
    	$hlret=json_decode($hlret,true);
    	$rate = $hlret['rates'];
    	switch($priceType){
    		case 'CNY':
    			return $rate['CNY'];
    			break;
    		case 'EUR':
    			return $rate['EUR'];
    			break;
    		default:
    			return 1;
    			break;
    	}
    }
    function get($url) {
    	if (function_exists('curl_init')) {
    		$curl = curl_init(); 
    		curl_setopt($curl, CURLOPT_URL, $url); 
    		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    		$result = curl_exec($curl); 
    		curl_close($curl);
    	} else {
    		$result = file_get_contents($url);
    	}
    	return $result;
    }
    function post($url,$data=array()) {
    	if (function_exists('curl_init')) {
    		$curl = curl_init(); 
    		curl_setopt($curl, CURLOPT_URL, $url); 
    		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    		curl_setopt($curl, CURLOPT_POST, 1);
    		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    
    		$result = curl_exec($curl); 
    		curl_close($curl);
    	} else {
    		return false;
    	}
    	return $result;
    }
}
