<?php

use yii\db\Migration;

/**
 * Handles the creation of table `queue_manager`.
 */
class m180615_175356_create_queue_manager_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 ENGINE=InnoDB';
        }

        $this->createTable('{{%queue_manager}}', [
            'id'          => $this->primaryKey(),
            'queue_id'    => $this->integer(11)->notNull()->comment('队列id'),
            'type'        => $this->char(64)->notNull()->comment('队列的类名'),
            'channel'     => $this->string(255)->notNull(),
            'job'         => $this->binary()->notNull(),
            'pushed_at'   => $this->integer(11)->notNull(),
            'ttr'         => $this->integer(11)->notNull(),
            'delay'       => $this->integer(11)->defaultValue(0)->notNull(),
            'priority'    => $this->integer(11)->defaultValue(1024)->notNull(),
            'reserved_at' => $this->integer(11),
            'status'      => $this->tinyInteger(4)->defaultValue(1)->notNull()->comment('任务状态 1-未开始 2-执行中 3-执行成功 4-执行失败'),
            'done_at'     => $this->integer(11),
            'failed_at'   => $this->integer(11)->notNull(),
            'attempt'     => $this->integer(11),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('queue_manager');
    }
}
