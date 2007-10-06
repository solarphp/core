<?php
class Test_Solar_Sql_ModelRelated_BelongsTo extends Test_Solar_Sql_ModelRelated {
    
    /**
     * 
     * Depends on the Solar_Example_Model_Nodes having added $this->_belongsTo().
     * 
     */
    public function testAddRelated()
    {
        try {
            $nodes = $this->_newModel('nodes');
        } catch (Exception $e) {
            Solar::dump($this->_sql->getProfile());
            die();
        }
        
        // make sure that nodes belongs-to area
        $expect = array (
            'name' => 'area',
            'type' => 'belongs_to',
            'foreign_class' => 'Solar_Example_Model_Areas',
            'foreign_table' => 'test_solar_areas',
            'foreign_alias' => 'area',
            'foreign_col' => 'id',
            'foreign_inherit_col' => NULL,
            'foreign_inherit_val' => NULL,
            'foreign_primary_col' => 'id',
            'native_class' => 'Solar_Example_Model_Nodes',
            'native_table' => 'test_solar_nodes',
            'native_alias' => 'nodes',
            'native_col' => 'area_id',
            'through' => NULL,
            'through_table' => NULL,
            'through_alias' => NULL,
            'through_native_col' => NULL,
            'through_foreign_col' => NULL,
            'distinct' => false,
            'where' => NULL,
            'group' => NULL,
            'having' => NULL,
            'order' =>  array('area.id'),
            'paging' => 10,
            'cols' =>  array(
                0 => 'id',
                1 => 'created',
                2 => 'updated',
                3 => 'user_id',
                4 => 'name',
            ),
            'fetch' => 'one',
        );
        
        $actual = $nodes->getRelated('area')->toArray();
        
        $this->assertSame($actual, $expect);
        
        // recover memory
        $nodes->__destruct();
        unset($nodes);
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
        
        // lazy-fetch the area; make sure it's an area record with the right
        // ID
        $area = $node->area;
        $this->assertInstance($area, 'Solar_Example_Model_Areas_Record');
        $this->assertEquals($node->area_id, $area->id);
        
        // the reference to $area should result in one extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before + 1);
        
        // a second check should *not* make a new SQL call
        $area = $node->area;
        $this->assertInstance($area, 'Solar_Example_Model_Areas_Record');
        $this->assertEquals($node->area_id, $area->id);
        $count_final = count($this->_sql->getProfile());
        $this->assertEquals($count_final, $count_after);
        
        // recover memory
        $nodes->__destruct();
        unset($nodes);
    }
    
    public function testLazyFetchAll()
    {
        $this->_populateAll();
        
        // fetch all nodes, then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $collection = $nodes->fetchAll();
        $count_before = count($this->_sql->getProfile());
        
        // lazy-fetch each area
        foreach ($collection as $node) {
            $area = $node->area;
            $this->assertInstance($area, 'Solar_Example_Model_Areas_Record');
            $this->assertEquals($node->area_id, $area->id);
        }
        
        // each reference to $area should result in one extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before + count($collection));
        
        // a second check should *not* make new SQL calls
        foreach ($collection as $node) {
            $area = $node->area;
            $this->assertInstance($area, 'Solar_Example_Model_Areas_Record');
            $this->assertEquals($node->area_id, $area->id);
        }
        
        $count_final = count($this->_sql->getProfile());
        $this->assertEquals($count_final, $count_after);
        
        // recover memory
        $nodes->__destruct();
        unset($nodes);
    }
    
    public function testEagerFetchOne()
    {
        $this->_populateAll();
        
        // fetch one node with an eager area
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array(
            'where' => array(
                'nodes.id = ?' => rand(1, 10),
            ),
            'eager' => array('area'),
        );
        $node = $nodes->fetchOne($params);
        $count_before = count($this->_sql->getProfile());
        
        // look at the area
        $area = $node->area;
        $this->assertInstance($area, 'Solar_Example_Model_Areas_Record');
        $this->assertEquals($node->area_id, $area->id);
        
        // **should not** have been an extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $nodes->__destruct();
        unset($nodes);
    }
    
    public function testEagerFetchAll()
    {
        $this->_populateAll();
        
        // fetch all nodes with eager area
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array('eager' => 'area');
        $collection = $nodes->fetchAll($params);
        $count_before = count($this->_sql->getProfile());
        
        // look at each area
        foreach ($collection as $node) {
            $area = $node->area;
            $this->assertInstance($area, 'Solar_Example_Model_Areas_Record');
            $this->assertEquals($node->area_id, $area->id);
        }
        
        // **should not** have been extra SQL calls
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $nodes->__destruct();
        unset($nodes);
    }
}
