<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $is_default
 * @property int $created_at
 * @property int $updated_at
 */
class UserGroup extends ActiveRecord
{
    const GROUP_ADMIN = 'admin';
    const GROUP_USER = 'user';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['is_default'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'unique'],
            ['is_default', 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'description' => 'Описание',
            'is_default' => 'По умолчанию',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Получить пользователей этой группы
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('{{%user_group_assignment}}', ['group_id' => 'id']);
    }

    /**
     * Получить группу по умолчанию
     */
    public static function getDefaultGroup()
    {
        return self::findOne(['is_default' => 1]);
    }
}