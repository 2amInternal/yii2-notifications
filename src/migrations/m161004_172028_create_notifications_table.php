<?php

use yii\db\Migration;

class m161004_172028_create_notifications_table extends Migration
{
    public function up()
    {
        $this->createTable('notification', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'data' => $this->string()->notNull()->defaultValue('{}'),
            'created_at' => $this->bigInteger()->notNull(),
            'updated_at' => $this->bigInteger(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer()
        ]);

        $this->createIndex('notifications_created_by_indx', 'notifications', 'created_by');
        $this->createIndex('notifications_updated_by_indx', 'notifications', 'updated_by');
    }

    public function down()
    {
        $this->dropIndex('notifications_created_by_indx', 'notifications');
        $this->dropIndex('notifications_updated_by_indx', 'notifications');
        $this->dropTable('notifications');
    }
}
