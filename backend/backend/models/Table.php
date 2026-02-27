<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Table extends ActiveRecord
{
    const TYPE_WINDOW = 'window';
    const TYPE_CORNER = 'corner';
    const TYPE_VIP = 'vip';
    const TYPE_REGULAR = 'regular';

    const ZONE_MAIN_HALL = 'main_hall';
    const ZONE_TERRACE = 'terrace';
    const ZONE_PRIVATE_ROOM = 'private_room';

    public static function tableName(): string
    {
        return '{{%table}}';
    }

    public function rules()
    {
        return [
            [['number', 'capacity', 'type'], 'required'],
            ['number', 'string', 'max' => 10],
            ['number', 'unique'],
            ['name', 'string', 'max' => 100],
            ['capacity', 'integer', 'min' => 1, 'max' => 20],
            ['type', 'in', 'range' => [
                self::TYPE_WINDOW,
                self::TYPE_CORNER,
                self::TYPE_VIP,
                self::TYPE_REGULAR
            ]],
            ['zone', 'string', 'max' => 50],
            ['is_active', 'boolean'],
            ['is_active', 'default', 'value' => true],
            [['position_x', 'position_y'], 'integer'],
            ['description', 'string'],
        ];
    }

    public function getTypeLabel()
    {
        $labels = [
            self::TYPE_WINDOW => 'У окна',
            self::TYPE_CORNER => 'Угловой',
            self::TYPE_VIP => 'VIP',
            self::TYPE_REGULAR => 'Обычный',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getZoneLabel()
    {
        $labels = [
            self::ZONE_MAIN_HALL => 'Основной зал',
            self::ZONE_TERRACE => 'Терраса',
            self::ZONE_PRIVATE_ROOM => 'Отдельная комната',
        ];
        return $labels[$this->zone] ?? $this->zone;
    }

    /**
     * Проверяет свободен ли стол на указанное время
     */
    public function isAvailable($date, $time)
    {
        $conflictingReservation = Reservation::find()
            ->where([
                'table_id' => $this->id,
                'reservation_date' => $date,
                'reservation_time' => $time,
                'status' => [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED]
            ])
            ->exists();

        return !$conflictingReservation && $this->is_active;
    }

    /**
     * Получает расписание стола на день
     */
    public function getSchedule($date)
    {
        $reservations = Reservation::find()
            ->where([
                'table_id' => $this->id,
                'reservation_date' => $date,
                'status' => [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED]
            ])
            ->orderBy(['reservation_time' => SORT_ASC])
            ->all();

        $schedule = [];
        foreach ($reservations as $reservation) {
            $schedule[] = [
                'time' => $reservation->reservation_time,
                'status' => $reservation->status,
                'guests_count' => $reservation->guests_count,
                'reservation_id' => $reservation->id
            ];
        }

        return $schedule;
    }
}