<?php

use yii\db\Migration;

/**
 * Handles the creation for table `location`.
 */
class m160806_125246_create_location_table extends Migration
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
        
        $this->createTable('location', [
            'id' => $this->primaryKey(),
            'street' => $this->string(),
            'postcode' => $this->string(10),
            'city' => $this->string()->notNull(),
            'state' => $this->string(),
            'country' => $this->string()->notNull(),
            'latitude' => $this->decimal(10,6),
            'longitude' => $this->decimal(10,6),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('location');
    }
}
