<?php
return [
    'allow_cash'    => [
        'title'   => '允许提现:',
        'type'    => 'radio',
        'options' => [
            '1' => '是',
            '0' => '否'
        ],
        'value'   => '1',
        'tip'     => 'EACPAY支付是基于区块链代币,价格会存在波动,您收到的EAC兑换法币可能会有变化, 介意请勿使用'
    ],
    'recive_token'     => [
        'title' => '收款地址:',
        'type'  => 'text',
        'value' => 'eZcwRzRDPiPvM6WUGQXMRLa5MAHkrwWP9t',
        'tip'   => '必填，请下载EAC钱包，生成一个地址，可以随时更换收款地址，严禁多个网站公用一个地址'
    ],
    'maxwaitpaytime'     => [
        'title' => '支付超时:',
        'type'  => 'number',
        'value' => 120,
        'tip'   => '单位:分钟'
    ],
    'bizhong'   => [
        'title'   => '定价基准币种:',
        'type'    => 'select',
        'options' => [
            'CNY' => '人民币',
            'USD' => '美元',
            'EUR' => '欧元'
        ],
        'value'   => '1',
        'tip'     => '必选，系统自动将对应的金额数量换成成同等价值的eac个数'
    ],
    'receipt_confirmation' => [
        'title'   => '确认数量:',
        'type'    => 'number',
        'value'   => 3,
        'tip'     => '必填，数值越大，确认充值的时间越长，但安全性越高，最低3个，建议不超过是10个'
    ],
    'notice' => [
        'title' => '通知提示:',
        'type'  => 'textarea',
        'value' => '请不要修改付款页面的任何信息，否则系统无法识别订单将导致不会自动发货',
        'tip'   => ''
    ],
    'exhangeapi' => [
        'title' => 'EAC定价基准交易所:',
        'type'  => 'text',
        'value' => 'https://api.aex.zone/v3/depth.php',
        'tip'   => '必填，目前默认http://www.aex.com(安银)'
    ],
    'eacpay_server'     => [
        'title' => 'Earthcoin区块链浏览器:',
        'type'  => 'text',
        'value' => 'https://blocks.deveac.com:4000',
        'tip'   => '必填，用于充值是到EAC区块链上查询支付情况，可以自行打架或者查询公共浏览器https://blocks.deveac.com:4040，https://api.eacpay.com:9000'
    ],
];