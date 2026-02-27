<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    const SCENARIO_REGISTER = 'register';
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_UPDATE = 'update';

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            [['username', 'email', 'password_hash'], 'required', 'on' => self::SCENARIO_REGISTER],
            [['username', 'email'], 'required', 'on' => self::SCENARIO_UPDATE],

            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/', 'message' => 'Только буквы, цифры и подчеркивание'],
            ['username', 'unique', 'message' => 'Это имя пользователя уже занято.'],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'message' => 'Этот email уже зарегистрирован.'],

            ['phone', 'string', 'max' => 20],
            ['phone', 'unique', 'message' => 'Этот номер телефона уже зарегистрирован.', 'skipOnEmpty' => true],

            [['first_name', 'last_name'], 'string', 'max' => 100],

            ['password_hash', 'string', 'min' => 6, 'on' => self::SCENARIO_REGISTER],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],

            [['created_at', 'updated_at'], 'integer'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password_hash', 'phone', 'first_name', 'last_name'];
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password_hash'];
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'email', 'phone', 'first_name', 'last_name'];
        return $scenarios;
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->generateAuthKey();
                $this->created_at = time();
            }
            $this->updated_at = time();
            return true;
        }
        return false;
    }

    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getGroups()
    {
        return $this->hasMany(UserGroup::class, ['id' => 'group_id'])
            ->viaTable('{{%user_group_assignment}}', ['user_id' => 'id']);
    }


    public function hasGroup($groupName)
    {
        foreach ($this->groups as $group) {
            if ($group->name === $groupName) {
                return true;
            }
        }
        return false;
    }


    public function isAdmin()
    {
        return $this->hasGroup(UserGroup::GROUP_ADMIN);
    }


    public function assignGroups($groupIds)
    {
        if (!is_array($groupIds)) {
            $groupIds = [$groupIds];
        }

        UserGroupAssignment::deleteAll(['user_id' => $this->id]);

        foreach ($groupIds as $groupId) {
            $assignment = new UserGroupAssignment();
            $assignment->user_id = $this->id;
            $assignment->group_id = $groupId;
            $assignment->created_at = time();
            $assignment->save();
        }
    }
}