<?php
namespace frontend\controllers;

use common\util\queue\Queue;
use console\jobs\TestQueue;
use yii\web\Controller;

class TestController extends Controller {

    public function actionTest() {
        (new Queue())->send(new TestQueue('666'));
        echo '调用成功';
    }

}