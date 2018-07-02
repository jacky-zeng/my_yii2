<?php

namespace common\util\queue;

interface QueueInterface
{
    /*任务的类型*/
    public function getTypeValue();

    /*任务重试次数*/
    public function getAttemptTimesValue();

    /*每多少秒重试一次*/
    public function getTtrValue();

    /*需要执行的业务*/
    public function exec();
}
