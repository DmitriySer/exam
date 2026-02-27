<?php

use yii\db\Migration;

/**
 * Class m260201_125454_create_group_role
 */
class m260201_125454_create_group_role extends Migration
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

        $this->createTable('{{%user_group}}', [
            'id' => $this->primaryKey()->comment('ID группы'),
            'name' => $this->string(50)->notNull()->unique()->comment('Название группы'),
            'description' => $this->string(255)->null()->comment('Описание группы'),
            'is_default' => $this->boolean()->notNull()->defaultValue(false)->comment('Группа по умолчанию для новых пользователей'),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата обновления'),
        ], $tableOptions);

        // Создаем таблицу связи пользователей и групп
        $this->createTable('{{%user_group_assignment}}', [
            'user_id' => $this->integer()->notNull()->comment('ID пользователя'),
            'group_id' => $this->integer()->notNull()->comment('ID группы'),
            'created_at' => $this->integer()->notNull()->comment('Дата назначения'),
        ], $tableOptions);

        // Добавляем первичный ключ для таблицы связи
        $this->addPrimaryKey('pk-user_group_assignment', '{{%user_group_assignment}}', ['user_id', 'group_id']);

        // Добавляем внешние ключи
        $this->addForeignKey(
            'fk-user_group_assignment-user_id',
            '{{%user_group_assignment}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-user_group_assignment-group_id',
            '{{%user_group_assignment}}',
            'group_id',
            '{{%user_group}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $timestamp = time();
        $this->batchInsert('{{%user_group}}',
            ['name', 'description', 'is_default', 'created_at', 'updated_at'],
            [
                ['admin', 'Администратор системы. Полный доступ ко всем функциям.', false, $timestamp, $timestamp],
                ['user', 'Обычный пользователь. Базовые права доступа.', true, $timestamp, $timestamp],
            ]
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_group_assignment}}');
        $this->dropTable('{{%user_group}}');
    }

}