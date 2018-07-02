<?php

use yii\db\Migration;

/**
 * Handles the creation of table `test_queue`.
 */
class m180615_175356_create_test_queue_table extends Migration
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

        $this->createTable('{{%test_queue}}', [
            'id'         => $this->primaryKey(11)->unsigned()->comment('主键id'),
            'info'       => $this->string(40)->notNull()->comment('插入的数据'),
            'created_at' => $this->dateTime()->notNull()->defaultValue('1900-01-01 00:00:00')->comment('创建时间'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('更新时间'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('test_queue');
    }
}
