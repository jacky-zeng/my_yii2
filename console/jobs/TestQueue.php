<?php

namespace console\jobs;

use common\util\queue\QueueInterface;
use Yii;

class TestQueue implements QueueInterface
{
    const TYPE = self::class;    //调起队列时，传入的参数 (合同id)

    const ATTEMPTS_TIMES = 3;     //队列失败重试的次数

    const TTR = 5;                //多少秒后重试队列

    private $params;              //调起队列时，传入的参数

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function getTypeValue()
    {
        return self::TYPE;
    }

    public function getAttemptTimesValue()
    {
        return self::ATTEMPTS_TIMES;
    }

    public function getTtrValue()
    {
        return self::TTR;
    }

    /*需要执行的业务*/
    public function exec()
    {
        if (time() % 3 == 0) {  //故意使其失败 测试队列的重试功能
            $rs = Yii::$app->db->createCommand()->insert('test_queue', [
                'info'       => '入库时间:'.date('Y-m-d H:i:s').' 传入的参数'.$this->params,
                'created_at' => date('Y-m-d H:i:s')
            ])->execute();

            return $rs;
        } else {
            throw new \Exception('执行队列失败');
        }
    }
}
