<?php

use yii\db\Migration;

/**
 * Handles the creation for table `phone_table`.
 */
class m160712_152725_create_phone_table extends Migration
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

        $this->createTable('phone', [
            'id' => $this->primaryKey(),
            'firm_id' => $this->integer()->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'phone' => $this->string(11)->notNull()
        ], $tableOptions);

        $this->addForeignKey(
            'fk-phone-firm',
            'phone',
            'firm_id',
            'firm',
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
            'fk-phone-firm',
            'phone'
        );

        $this->dropTable('phone');
    }
}
