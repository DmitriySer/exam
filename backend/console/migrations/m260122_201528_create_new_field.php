<?php

use yii\db\Migration;

class m260122_201528_create_new_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'phone', $this->string(20));
        $this->addColumn('{{%user}}', 'first_name', $this->string(100));
        $this->addColumn('{{%user}}', 'last_name', $this->string(100));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'last_name');
        $this->dropColumn('{{%user}}', 'first_name');
        $this->dropColumn('{{%user}}', 'phone');

        return true;
    }
}
