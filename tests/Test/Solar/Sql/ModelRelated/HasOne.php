<?php
class Test_Solar_Sql_ModelRelated_HasOne extends Test_Solar_Sql_ModelRelated {
    
    /**
     * 
     * Depends on the Solar_Example_Model_Nodes having added $this->_hasOne().
     * 
     */
    public function testAddRelated()
    {
        $nodes = $this->_newModel('nodes');
        
        // make sure that nodes has-one meta entry
        $expect = array(
            'name' => 'meta',
            'type' => 'has_one',
            'foreign_class' => 'Solar_Example_Model_Metas',
            'foreign_table' => 'test_solar_metas',
            'foreign_alias' => 'meta',
            'foreign_col' => 'node_id',
            'foreign_inherit_col' => NULL,
            'foreign_inherit_val' => NULL,
            'foreign_primary_col' => 'id',
            'native_class' => 'Solar_Example_Model_Nodes',
            'native_table' => 'test_solar_nodes',
            'native_alias' => 'nodes',
            'native_col' => 'id',
            'through' => NULL,
            'through_table' => NULL,
            'through_alias' => NULL,
            'through_native_col' => NULL,
            'through_foreign_col' => NULL,
            'distinct' => false,
            'where' => NULL,
            'group' => NULL,
            'having' => NULL,
            'order' => array('meta.id'),
            'paging' => 10,
            'cols' => array(
                0 => 'id',
                1 => 'node_id',
                2 => 'last_comment_id',
                3 => 'last_comment_by',
                4 => 'last_comment_at',
                5 => 'comment_count',
            ),
            'fetch' => 'one',
        );
        
        $actual = $nodes->getRelated('meta')->toArray();
        $this->assertSame($actual, $expect);
    }
    
    public function testLazyFetchOne()
    {
        $this->_populateAll();
        
        // fetch one node, then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array(
            'where' => array(
                'id = ?' => rand(1, 10),
            ),
        );
        $node = $nodes->fetchOne($params);
        $count_before = count($this->_sql->getProfile());
        
        // lazy-fetch the meta; make sure it's an meta record with the right
        // ID
        $meta = $node->meta;
        $this->assertInstance($meta, 'Solar_Example_Model_Metas_Record');
        $this->assertEquals($meta->node_id, $node->id);
        
        // the reference to $meta should result in one extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before + 1);
        
        // a second check should *not* make a new SQL call
        $meta = $node->meta;
        $this->assertInstance($meta, 'Solar_Example_Model_Metas_Record');
        $this->assertEquals($meta->node_id, $node->id);
        $count_final = count($this->_sql->getProfile());
        $this->assertEquals($count_final, $count_after);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    public function testLazyFetchAll()
    {
        $this->_populateAll();
        
        // fetch all nodes, then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $collection = $nodes->fetchAll();
        $count_before = count($this->_sql->getProfile());
        
        // lazy-fetch each meta
        foreach ($collection as $node) {
            $meta = $node->meta;
            $this->assertInstance($meta, 'Solar_Example_Model_Metas_Record');
            $this->assertEquals($meta->node_id, $node->id);
        }
        
        // each reference to $meta should result in one extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before + count($collection));
        
        // a second check should *not* make new SQL calls
        foreach ($collection as $node) {
            $meta = $node->meta;
            $this->assertInstance($meta, 'Solar_Example_Model_Metas_Record');
            $this->assertEquals($meta->node_id, $node->id);
        }
        
        $count_final = count($this->_sql->getProfile());
        $this->assertEquals($count_final, $count_after);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    public function testEagerFetchOne()
    {
        $this->_populateAll();
        
        // fetch one node with an eager meta
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array(
            'where' => array(
                'nodes.id = ?' => rand(1, 10),
            ),
            'eager' => array('meta'),
        );
        $node = $nodes->fetchOne($params);
        $count_before = count($this->_sql->getProfile());
        
        // look at the meta
        $meta = $node->meta;
        $this->assertInstance($meta, 'Solar_Example_Model_Metas_Record');
        $this->assertEquals($meta->node_id, $node->id);
        
        // **should not** have been an extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    public function testEagerFetchAll()
    {
        $this->_populateAll();
        
        // fetch all nodes with eager meta
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array('eager' => 'meta');
        $collection = $nodes->fetchAll($params);
        $count_before = count($this->_sql->getProfile());
        
        // look at each meta
        foreach ($collection as $node) {
            $meta = $node->meta;
            $this->assertInstance($meta, 'Solar_Example_Model_Metas_Record');
            $this->assertEquals($meta->node_id, $node->id);
        }
        
        // **should not** have been extra SQL calls
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
}
