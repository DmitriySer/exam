<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%request}}`.
 */
class m260122_211339_create_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->getTableSchema('{{%request}}', true) !== null) {
            echo "Таблица request уже существует.\n";
            return true;
        }

        $this->createTable('{{%request}}', [
            'id' => $this->primaryKey()->comment('ID заявки'),
            'user_id' => $this->integer()->notNull()->comment('ID пользователя'),
            'request_date' => $this->date()->notNull()->comment('Дата заявки'),
            'request_time' => $this->time()->notNull()->comment('Время заявки'),
            'guests_count' => $this->integer()->notNull()->defaultValue(1)->comment('Количество участников'),
            'contact_phone' => $this->string(20)->notNull()->comment('Контактный телефон'),
            'contact_name' => $this->string(100)->comment('Контактное лицо'),
            'request_type' => $this->string(50)->defaultValue('standard')
                ->comment('Тип заявки: standard, urgent, vip, group'),
            'special_requests' => $this->text()->comment('Особые пожелания'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')
                ->comment('Статус: pending, confirmed, canceled, completed'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата создания'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Дата обновления'),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-request-user_id',
            '{{%request}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-request-user_id', '{{%request}}');

        $this->dropTable('{{%request}}');
    }
}