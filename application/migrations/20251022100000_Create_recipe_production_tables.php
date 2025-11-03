<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_recipe_production_tables extends CI_Migration {

    public function up()
    {
        // Create 'recipes' table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'recipe_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
            ),
            'output_product_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'yield_quantity' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,4',
            ),
            'notes' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'created_by' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'created_at' => array(
                'type' => 'DATETIME',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('recipes');

        // Create 'recipe_items' table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'recipe_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'item_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'quantity' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,4',
            ),
            'unit' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('recipe_items');

        // Create 'production_batches' table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'recipe_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'produced_quantity' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,4',
            ),
            'status' => array(
                'type' => 'ENUM("draft", "approved", "cancelled")',
                'default' => 'draft',
            ),
            'warehouse_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'total_cost' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,4',
            ),
            'cost_per_unit' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,4',
            ),
            'notes' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'created_by' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'approved_by' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => TRUE,
            ),
            'created_at' => array(
                'type' => 'DATETIME',
            ),
            'approved_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('production_batches');
    }

    public function down()
    {
        $this->dbforge->drop_table('production_batches');
        $this->dbforge->drop_table('recipe_items');
        $this->dbforge->drop_table('recipes');
    }
}
