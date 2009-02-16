<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Sql_Model_Related_BelongsTo extends Test_Solar_Sql_Model_Related {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql_Model_Related_BelongsTo = array(
    );
    
    /**
     * 
     * Test -- Fetches foreign data as an array.
     * 
     */
    public function testFetchArray()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Fetches foreign data as a record or collection object.
     * 
     */
    public function testFetchObject()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the related (foreign) model instance.
     * 
     */
    public function testGetModel()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Loads this relationship object with user-defined characteristics (options), and corrects them as needed.
     * 
     */
    public function testLoad()
    {
        $nodes = $this->_newModel('nodes');
        
        $related = $this->_newRelated($nodes, array(
            'name' => 'area',
        ));
        
        $actual = $related->toArray();
        
        $expect = array (
            'cols'                  =>  array(
                0 => 'id',
                1 => 'created',
                2 => 'updated',
                3 => 'user_id',
                4 => 'name',
            ),
            'distinct'              => false,
            'fetch'                 => 'one',
            'foreign_alias'         => 'area',
            'foreign_class'         => 'Solar_Example_Model_Areas',
            'foreign_col'           => 'id',
            'foreign_inherit_col'   => null,
            'foreign_inherit_val'   => null,
            'foreign_key'           => 'area_id',
            'foreign_primary_col'   => 'id',
            'foreign_table'         => 'test_solar_areas',
            'group'                 => null,
            'having'                => null,
            'name'                  => 'area',
            'native_alias'          => 'nodes',
            'native_class'          => 'Solar_Example_Model_Nodes',
            'native_col'            => 'area_id',
            'native_table'          => 'test_solar_nodes',
            'order'                 => array('area.id'),
            'paging'                => 10,
            'through'               => null,
            'through_alias'         => null,
            'through_foreign_col'   => null,
            'through_key'           => null,
            'through_native_col'    => null,
            'through_table'         => null,
            'type'                  => 'belongs_to',
            'where'                 => null,
        );
        
        $this->assertSame($actual, $expect);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    /**
     * 
     * Test -- Modifies the SELECT from a native model countPages() call to join with the foreign model (especially on eager fetches).
     * 
     */
    public function testModSelectCountPages()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- When the native model is doing a select and an eager-join is requested for this relation, this method modifies the select to add the eager join.
     * 
     */
    public function testModSelectEager()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Creates a new selection object for fetching records from this relation.
     * 
     */
    public function testNewSelect()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the native (origin) model instance.
     * 
     */
    public function testSetNativeModel()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the relation characteristics as an array.
     * 
     */
    public function testToArray()
    {
        $this->todo('stub');
    }
    
    public function test_lazyFetchOne()
    {
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
        $nodes->free();
        unset($nodes);
    }
    
    public function test_lazyFetchAll()
    {
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
        $nodes->free();
        unset($nodes);
    }
    
    public function test_eagerFetchOne()
    {
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
        $nodes->free();
        unset($nodes);
    }
    
    public function test_eagerFetchAll()
    {
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
        $nodes->free();
        unset($nodes);
    }
}
