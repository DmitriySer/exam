<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Request model
 *
 * @property int $id
 * @property int $user_id
 * @property string $request_date
 * @property string $request_time
 * @property int $guests_count
 * @property string $contact_phone
 * @property string|null $contact_name
 * @property string|null $request_type
 * @property string|null $special_requests
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 */
class Request extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_COMPLETED = 'completed';

    const TYPE_STANDARD = 'standard';
    const TYPE_URGENT = 'urgent';
    const TYPE_VIP = 'vip';
    const TYPE_GROUP = 'group';

    public static function tableName()
    {
        return '{{%request}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => function() {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }

    public function rules()
    {
        return [
            [['user_id', 'request_date', 'request_time', 'contact_phone'], 'required'],

            ['user_id', 'integer'],
            ['user_id', 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],

            ['request_date', 'date', 'format' => 'php:Y-m-d'],
            ['request_date', 'validateFutureDate'],

            ['request_time', 'string'],
            ['request_time', 'validateWorkingHours'],

            ['guests_count', 'integer', 'min' => 1, 'max' => 50],

            ['contact_phone', 'string', 'max' => 20],
            ['contact_phone', 'match', 'pattern' => '/^\+?[0-9\s\-\(\)]+$/'],

            ['contact_name', 'string', 'max' => 100],

            ['request_type', 'in', 'range' => [
                self::TYPE_STANDARD,
                self::TYPE_URGENT,
                self::TYPE_VIP,
                self::TYPE_GROUP
            ]],
            ['request_type', 'default', 'value' => self::TYPE_STANDARD],

            ['status', 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_CONFIRMED,
                self::STATUS_CANCELED,
                self::STATUS_COMPLETED
            ]],
            ['status', 'default', 'value' => self::STATUS_PENDING],

            ['special_requests', 'string'],

            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function validateFutureDate($attribute, $params)
    {
        $today = date('Y-m-d');
        if ($this->$attribute < $today) {
            $this->addError($attribute, 'Дата заявки не может быть в прошлом');
        }
    }

    public function validateWorkingHours($attribute, $params)
    {
        $time = strtotime($this->$attribute);
        $hour = date('H', $time);

        // Режим работы с 09:00 до 18:00
        if ($hour < 9 || $hour > 18) {
            $this->addError($attribute, 'Прием заявок осуществляется с 09:00 до 18:00');
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->status)) {
                $this->status = self::STATUS_PENDING;
            }

            if (empty($this->request_type)) {
                $this->request_type = self::TYPE_STANDARD;
            }

            return true;
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID заявки',
            'user_id' => 'Пользователь',
            'request_date' => 'Дата заявки',
            'request_time' => 'Время заявки',
            'contact_phone' => 'Контактный телефон',
            'contact_name' => 'Контактное лицо',
            'request_type' => 'Тип заявки',
            'special_requests' => 'Особые пожелания',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_PENDING => 'Ожидает обработки',
            self::STATUS_CONFIRMED => 'Подтверждена',
            self::STATUS_CANCELED => 'Отменена',
            self::STATUS_COMPLETED => 'Завершена',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getRequestTypeLabel()
    {
        $labels = [
            self::TYPE_STANDARD => 'Стандартная',
            self::TYPE_URGENT => 'Срочная',
            self::TYPE_VIP => 'VIP',
            self::TYPE_GROUP => 'Групповая',
        ];
        return $labels[$this->request_type] ?? $this->request_type;
    }

    public function isActive()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function canCancel()
    {
        if (!$this->isActive()) {
            return false;
        }

        $requestDateTime = strtotime($this->request_date . ' ' . $this->request_time);
        $oneHourBefore = strtotime('-1 hour', $requestDateTime);

        return time() < $oneHourBefore;
    }

    /**
     * Получить статистику заявок за период
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getStatistics($startDate, $endDate)
    {
        return self::find()
            ->select(['status', 'request_type', 'COUNT(*) as count'])
            ->where(['between', 'request_date', $startDate, $endDate])
            ->groupBy(['status', 'request_type'])
            ->asArray()
            ->all();
    }
}