<?php

declare(strict_types=1);

namespace plugins\eacpay\model;
use think\Model;

class EacpayOrder extends Model
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'order_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'eacpay_order';

}
