<?php

namespace common\models;

use yii\db\Query;

/**
 * This is the model class for table "t_queue_manager".
 *
 * @property int $id
 * @property int $queue_id
 * @property string $type 队列的类名（完全小写）
 * @property string $channel
 * @property resource $job
 * @property int $pushed_at
 * @property int $ttr
 * @property int $delay
 * @property string $priority
 * @property int $reserved_at
 * @property int $attempt
 * @property int $status 任务状态 1-未开始 2-执行中 3-执行成功 4-执行失败
 * @property int $done_at
 * @property int $failed_at
 */
class QueueManager extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'queue_manager';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_id', 'type', 'channel', 'job', 'pushed_at', 'ttr'], 'required'],
            [
                [
                    'queue_id',
                    'pushed_at',
                    'ttr',
                    'delay',
                    'priority',
                    'reserved_at',
                    'attempt',
                    'status',
                    'done_at',
                    'failed_at'
                ],
                'integer'
            ],
            [['job'], 'string'],
            [['type'], 'string', 'max' => 64],
            [['channel'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'queue_id'    => 'Queue ID',
            'type'        => 'Type',
            'channel'     => 'Channel',
            'job'         => 'Job',
            'pushed_at'   => 'Pushed At',
            'ttr'         => 'Ttr',
            'delay'       => 'Delay',
            'priority'    => 'Priority',
            'reserved_at' => 'Reserved At',
            'attempt'     => 'Attempt',
            'status'      => 'Status',
            'done_at'     => 'Done At',
            'failed_at'   => 'Failed At',
        ];
    }

    //任务状态 1-未开始 2-执行中 3-执行成功 4-执行失败
    const STATUS_READY   = 1;
    const STATUS_ACTIVE  = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_FAILED  = 4;

    /**
     * 队列入队时  加入到队列管理的主表
     * @param $queue_id
     * @param $type
     * @throws \Exception
     */
    public static function QueueReady($queue_id, $type)
    {
        try {
            $queue_data = (new Query())->from('{{%queue}}')->where(['id' => $queue_id])->one();
            $model      = new self();

            $model->queue_id    = $queue_data['id'];
            $model->type        = $type;
            $model->channel     = $queue_data['channel'];
            $model->job         = $queue_data['job'];
            $model->pushed_at   = $queue_data['pushed_at'];
            $model->ttr         = $queue_data['ttr'];
            $model->delay       = $queue_data['delay'];
            $model->priority    = $queue_data['priority'];
            $model->reserved_at = $queue_data['reserved_at'];
            $model->attempt     = $queue_data['attempt'];
            $model->status      = self::STATUS_READY;
            $model->failed_at   = time();

            $rs = $model->save();

            if (! $rs) {
                throw new \Exception('QueueReady failed (SQL error)');
            }
        } catch (\Exception $ex) {
            throw new \Exception('QueueReady failed '.$ex->getMessage());
        }
    }

    /**
     * 队列执行中
     * @param $queue_id
     * @param $type
     * @throws \Exception
     */
    public static function QueueActive($queue_id, $type)
    {
        try {
            $queue_data      = (new Query())->from('{{%queue}}')->where(['id' => $queue_id])->one();

            $update_data = [
                'status'      => self::STATUS_ACTIVE,
                'reserved_at' => $queue_data['reserved_at'],
                'attempt'     => $queue_data['attempt'],
            ];

            $rs = self::updateAll($update_data, ['queue_id' => $queue_id, 'type' => $type]);
            if (! $rs) {
                throw new \Exception('QueueActive failed (SQL error)');
            }
        } catch (\Exception $ex) {
            throw new \Exception('QueueActive failed '.$ex->getMessage());
        }
    }

    /**
     * 队列执行成功
     * @param $queue_id
     * @param $type
     * @throws \Exception
     */
    public static function QueueSuccess($queue_id, $type)
    {
        try {
            $queue_data = (new Query())->from('{{%queue}}')->where(['id' => $queue_id])->one();

            $update_data = [
                'status'      => self::STATUS_SUCCESS,
                'done_at'     => $queue_data['done_at'] ? $queue_data['done_at'] : time(),
                'reserved_at' => $queue_data['reserved_at'],
                'attempt'     => $queue_data['attempt'],
            ];

            $rs = self::updateAll($update_data, ['queue_id' => $queue_id, 'type' => $type]);
            if (! $rs) {
                throw new \Exception('QueueSuccess failed (SQL error)');
            }
        } catch (\Exception $ex) {
            throw new \Exception('QueueSuccess failed '.$ex->getMessage());
        }
    }

    /**
     * 队列执行失败
     * @param $queue_id
     * @param $type
     * @throws \Exception
     */
    public static function QueueFailed($queue_id, $type)
    {
        try {
            $queue_data = (new Query())->from('{{%queue}}')->where(['id' => $queue_id])->one();

            $update_data = [
                'status'      => self::STATUS_FAILED,
                'failed_at'   => time(),
                'reserved_at' => $queue_data['reserved_at'],
                'attempt'     => $queue_data['attempt'],
            ];

            $rs = self::updateAll($update_data, ['queue_id' => $queue_id, 'type' => $type]);
            if (! $rs) {
                throw new \Exception('QueueFailed failed (SQL error)');
            }
        } catch (\Exception $ex) {
            throw new \Exception('QueueFailed failed');
        }
    }

}
