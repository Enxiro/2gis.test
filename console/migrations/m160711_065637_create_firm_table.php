<?php

use yii\db\Migration;

/**
 * Handles the creation for table `firm_table`.
 */
class m160711_065637_create_firm_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('firm', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'building_id' => $this->integer()->notNull()
        ], $tableOptions);

        $this->addForeignKey(
            'fk-building-firm',
            'firm',
            'building_id',
            'building',
            'id',
            'RESTRICT'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey(
            'fk-building-firm',
            'firm'
        );

        $this->dropTable('firm');
    }
}
