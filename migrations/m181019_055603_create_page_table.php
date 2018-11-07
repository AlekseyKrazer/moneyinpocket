<?php

use yii\db\Migration;

/**
 * Handles the creation of table `page`.
 */
class m181019_055603_create_page_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('page', [
            'id'               => $this->primaryKey(),
            'title'            => $this->string(255)->notNull(),
            'parent_id'        => $this->integer()->null(),
            'weight'           => $this->integer(11)->notNull()->defaultValue(1),
        ]);

        $this->createIndex('idx-page-parent_id', 'page', 'parent_id');
        $this->addForeignKey('fk-page-parent_id-page-id', 'page', 'parent_id', 'page', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('page');
    }
}
