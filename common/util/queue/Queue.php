<?php

namespace common\util\queue;

use common\models\QueueManager;
use yii\base\BaseObject;

/*
 * 如何配置
 * 步骤一：安装 composer require "jacky-zeng/yii2-queue:2.0.3" --prefer-source
 * 步骤二：在 my_yii2\common\config\main-local.php中进行配置
 *        与components同级加入如下配置
 *        'bootstrap' => [
 *             'queue', // 把这个组件注册到控制台 才可运行队列任务
 *         ],
 *         在components内加入如下配置
 *        'queue' => [
 *                 'class' => \yii\queue\db\Queue::class,
 *                 'db' => 'db', // DB connection component or its config
 *                 'tableName' => '{{%queue}}', // Table name
 *                 'channel' => 'default', // Queue channel key
 *                 'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
 *             ],
 * 步骤三：生成queue queue_manager test_queue 3个数据表
 * 如何使用队列
 * 步骤一：开启监听   yii queue/listen (在生产环境需要配置定时任务)
 * 步骤二：写队列类：
 *     1.在 console\jobs 文件夹内新增一个类文件
 *     2.继承QueueInterface接口
 *     3.在exec()方法中写需要做的业务逻辑，只有在业务中抛出异常，业务才会重试执行
 * 步骤三：调起队列(可参考 my_yii2\frontend\controllers\TestController.php)：
 *     队列立即异步执行：       (new Queue())->send(new yourClass($your_params));
 *     或者队列10秒后异步执行： (new Queue())->delay(10)->send(new yourClass($your_params));
 * */

class Queue extends BaseObject implements \yii\queue\JobInterface,\yii\queue\RetryableJobInterface
{
    public $obj;

    private $pushDelay = 0;

    /**
     * 延迟多少秒后执行队列
     * @param $value  秒数
     * @return $this
     */
    public function delay($value)
    {
        $this->pushDelay = $value;

        return $this;
    }

    /**
     * 加入到队列
     * @param $obj
     * @throws \Exception
     */
    public function send($obj)
    {
        $this->obj = $obj;
        $id        = \Yii::$app->queue->delay($this->pushDelay)->push(new Queue(['obj' => $obj]));
        QueueManager::QueueReady($id, $this->obj->getTypeValue());
    }

    /* 执行队列*/
    public function execute($id, $queue)
    {
        QueueManager::QueueActive($id, $this->obj->getTypeValue());

        $rs = $this->obj->exec();
        if ($rs) {
            QueueManager::QueueSuccess($id, $this->obj->getTypeValue());
        }
    }

    /*设置失败队列多少秒后重试*/
    public function getTtr()
    {
        return $this->obj->getTtrValue();
    }

    /*队列重试*/
    public function canRetry($id, $attempt, $error)
    {
        if ($attempt < $this->obj->getAttemptTimesValue() && ($error)) {
            return true;                    //继续重试任务
        } else {
            if ($error) {                    //重试多次后 依然未成功
                //记录未成功的任务到t_queue_manager表
                QueueManager::QueueFailed($id, $this->obj->getTypeValue());
            } else {
                //重试多次后,成功了
            }

            return false;                  //不再重试了
        }
    }
}