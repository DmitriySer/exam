<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $user_id
 * @property int $group_id
 * @property int $created_at
 */
class UserGroupAssignment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_group_assignment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id'], 'required'],
            [['user_id', 'group_id', 'created_at'], 'integer'],
            [['user_id', 'group_id'], 'unique', 'targetAttribute' => ['user_id', 'group_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Пользователь',
            'group_id' => 'Группа',
            'created_at' => 'Дата назначения',
        ];
    }
}