<?php

declare(strict_types=1);

namespace plugins\eacpay\model;
use think\Model;

class EacpayAddress extends Model
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'address';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'eacpay_address';

}
