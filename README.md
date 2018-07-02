# 介绍
>  对官方yiisoft/yii2-queue进行过小修改：
>  修改了JobInterface里的execute接口 和RetryableJobInterface里的canRetry接口，暴露处理队列的id，便于业务层做二次处理。
>  得到jacky-zeng/yii2-queue:2.0.3
>  安装我的版本 composer require "jacky-zeng/yii2-queue:2.0.3" --prefer-source

# 如何配置
``` bash
步骤一：安装 composer require "jacky-zeng/yii2-queue:2.0.3" --prefer-source
步骤二：在 my_yii2\common\config\main-local.php中进行配置
       与components同级加入如下配置
       'bootstrap' => [
            'queue', // 把这个组件注册到控制台 才可运行队列任务
        ],
        在components内加入如下配置
       'queue' => [
                'class' => \yii\queue\db\Queue::class,
                'db' => 'db', // DB connection component or its config
                'tableName' => '{{%queue}}', // Table name
                'channel' => 'default', // Queue channel key
                'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
            ],
步骤三：生成queue queue_manager test_queue 3个数据表
如何使用队列
步骤一：开启监听   yii queue/listen (在生产环境需要配置定时任务)
步骤二：写队列类：
    1.在 console\jobs 文件夹内新增一个类文件
    2.继承QueueInterface接口
    3.在exec()方法中写需要做的业务逻辑，只有在业务中抛出异常，业务才会重试执行
步骤三：调起队列(可参考 my_yii2\frontend\controllers\TestController.php)：
    队列立即异步执行：       (new Queue())->send(new yourClass($your_params));
    或者队列10秒后异步执行： (new Queue())->delay(10)->send(new yourClass($your_params));
```

# 说明

>  queue表是队列表记录   queue_manager表是我扩展的队列表(含所有队列的状态及记录)，可以使用RabbitMQ对其做处理

>  本项目主要是为了解决官方的队列模块的缺陷：
   ``` bash
   1.没有完整记录所有队列的状态及执行详细情况
   2.对失败的队列抛弃不管
   ```
 