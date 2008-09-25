<?php
class Test_Solar_Sql_ModelRelated_HasMany extends Test_Solar_Sql_ModelRelated {
    
    /**
     * 
     * Depends on the Solar_Example_Model_Nodes having added $this->_hasOne().
     * 
     */
    public function testAddRelated()
    {
        $areas = $this->_newModel('areas');
        
        // make sure that areas has-many nodes entry
        $expect = array (
            'name' => 'nodes',
            'type' => 'has_many',
            'foreign_class' => 'Solar_Example_Model_Nodes',
            'foreign_table' => 'test_solar_nodes',
            'foreign_alias' => 'nodes',
            'foreign_col' => 'area_id',
            'foreign_inherit_col' => NULL,
            'foreign_inherit_val' => NULL,
            'foreign_primary_col' => 'id',
            'native_class' => 'Solar_Example_Model_Areas',
            'native_table' => 'test_solar_areas',
            'native_alias' => 'areas',
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
            'order' => array('nodes.id'),
            'paging' => 10,
            'cols' => array (
                0 => 'id',
                1 => 'created',
                2 => 'updated',
                3 => 'area_id',
                4 => 'user_id',
                5 => 'node_id',
                6 => 'inherit',
                7 => 'subj',
                8 => 'body',
            ),
            'fetch' => 'all',
        );
        
        $actual = $areas->getRelated('nodes')->toArray();
        $this->assertSame($actual, $expect);
        
        // recover memory
        $areas->free();
        unset($areas);
    }
    
    public function testLazyFetchOne()
    {
        $this->_populateAll();
        
        // fetch one area, then see how many sql calls so far
        $areas = $this->_newModel('areas');
        $params = array(
            'where' => array(
                'id = ?' => rand(1, 2),
            ),
        );
        $area = $areas->fetchOne($params);
        $count_before = count($this->_sql->getProfile());
        
        // lazy-fetch the nodes and check that the area_id's match
        $nodes = $area->nodes;
        $this->assertInstance($nodes, 'Solar_Example_Model_Nodes_Collection');
        $this->assertEquals(count($nodes), 5);
        foreach ($nodes as $node) {
            $this->assertEquals($node->area_id, $area->id);
        }
        
        // the reference to $nodes should result in one extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before + 1);
        
        // a second check should *not* make a new SQL call
        $nodes = $area->nodes;
        $this->assertInstance($nodes, 'Solar_Example_Model_Nodes_Collection');
        $count_final = count($this->_sql->getProfile());
        $this->assertEquals($count_final, $count_after);
        
        // recover memory
        $areas->free();
        unset($areas);
    }
    
    public function testLazyFetchAll()
    {
        $this->_populateAll();
        
        // fetch all areas, then see how many sql calls so far
        $areas = $this->_newModel('areas');
        $collection = $areas->fetchAll();
        $count_before = count($this->_sql->getProfile());
        
        // lazy-fetch each node
        foreach ($collection as $area) {
            $nodes = $area->nodes;
            $this->assertInstance($nodes, 'Solar_Example_Model_Nodes_Collection');
            $this->assertEquals(count($nodes), 5);
            foreach ($nodes as $node) {
                $this->assertEquals($node->area_id, $area->id);
            }
        }
        
        // each reference to $nodes should result in one extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before + count($collection));
        
        // a second check should *not* make new SQL calls
        foreach ($collection as $area) {
            $nodes = $area->nodes;
            $this->assertInstance($nodes, 'Solar_Example_Model_Nodes_Collection');
            // @todo How to check that it has the right nodes in it?
        }
        
        $count_final = count($this->_sql->getProfile());
        $this->assertEquals($count_final, $count_after);
        
        // recover memory
        $areas->free();
        unset($areas);
    }
    
    public function testEagerFetchOne()
    {
        $this->_populateAll();
        
        // fetch one area with an eager nodes
        // then see how many sql calls so far
        $areas = $this->_newModel('areas');
        $params = array(
            'where' => array(
                'areas.id = ?' => rand(1, 2),
            ),
            'eager' => array('nodes'),
        );
        $area = $areas->fetchOne($params);
        $count_before = count($this->_sql->getProfile());
        
        // look at the nodes and make sure the area_id's match
        $nodes = $area->nodes;
        $this->assertInstance($nodes, 'Solar_Example_Model_Nodes_Collection');
        $this->assertEquals(count($nodes), 5);
        foreach ($nodes as $node) {
            $this->assertEquals($node->area_id, $area->id);
        }
        
        // **should not** have been an extra SQL call
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $areas->free();
        unset($areas);
    }
    
    public function testEagerFetchAll()
    {
        $this->_populateAll();
        
        // fetch all areas with eager nodes
        // then see how many sql calls so far
        $areas = $this->_newModel('areas');
        $params = array('eager' => 'nodes');
        $collection = $areas->fetchAll($params);
        $count_before = count($this->_sql->getProfile());
        
        // look at each area
        foreach ($collection as $area) {
            $nodes = $area->nodes;
            $this->assertInstance($nodes, 'Solar_Example_Model_Nodes_Collection');
            $this->assertEquals(count($nodes), 5);
            foreach ($nodes as $node) {
                $this->assertEquals($node->area_id, $area->id);
            }
        }
        
        // **should not** have been extra SQL calls
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $areas->free();
        unset($areas);
    }
}
