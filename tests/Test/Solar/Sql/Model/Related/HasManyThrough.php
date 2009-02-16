<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Sql_Model_Related_HasManyThrough extends Test_Solar_Sql_Model_Related {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql_Model_Related_HasManyThrough = array(
    );
    
    // -----------------------------------------------------------------
    // 
    // Support methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration parameters.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    /**
     * 
     * Destructor; runs after all methods are complete.
     * 
     * @param array $config User-defined configuration parameters.
     * 
     */
    public function __destruct()
    {
        parent::__destruct();
    }
    
    /**
     * 
     * Setup; runs before each test method.
     * 
     */
    public function setup()
    {
        parent::setup();
        $this->_class = 'Solar_Sql_Model_Related_HasMany';
    }
    
    /**
     * 
     * Setup; runs after each test method.
     * 
     */
    public function teardown()
    {
        parent::teardown();
    }
    
    // -----------------------------------------------------------------
    // 
    // Test methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Test -- Constructor.
     * 
     */
    public function test__construct()
    {
        $obj = Solar::factory('Solar_Sql_Model_Related_HasMany');
        $this->assertInstance($obj, 'Solar_Sql_Model_Related_HasMany');
    }
    
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
        $taggings = $this->_newModel('taggings');
        $tags = $this->_newModel('tags');
        
        $related = $this->_newRelated($nodes, array(
            'name'    => 'tags',
            'through' => 'taggings',
        ));
        
        $actual = $related->toArray();
        
        // make sure that areas has-many nodes entry
        $expect = array (
            'cols'                  => array (
                0 => 'id',
                1 => 'name',
                2 => 'summ',
            ),
            'distinct'               => false,
            'fetch'                  => 'all',
            'foreign_alias'          => 'tags',
            'foreign_class'          => 'Solar_Example_Model_Tags',
            'foreign_col'            => 'id',
            'foreign_inherit_col'    => null,
            'foreign_inherit_val'    => null,
            'foreign_key'            => 'id',
            'foreign_primary_col'    => 'id',
            'foreign_table'          => 'test_solar_tags',
            'group'                  => null,
            'having'                 => null,
            'name'                   => 'tags',
            'native_alias'           => 'nodes',
            'native_class'           => 'Solar_Example_Model_Nodes',
            'native_col'             => 'id',
            'native_table'           => 'test_solar_nodes',
            'order'                  => array('tags.id'),
            'paging'                 => 10,
            'through'                => 'taggings',
            'through_alias'          => 'taggings',
            'through_foreign_col'    => 'tag_id',
            'through_key'            => null,
            'through_native_col'     => 'node_id',
            'through_table'          => 'test_solar_taggings',
            'type'                   => 'has_many',
            'where'                  => null,
        );
        
        $this->assertSame($actual, $expect);
        
        // recover memory
        $tags->free();
        $taggings->free();
        $nodes->free();
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
        $count0 = count($this->_sql->getProfile());
        
        // lazy-fetch the taggings and check that the node_id's match
        $taggings = $node->taggings;
        $this->assertInstance($taggings, 'Solar_Example_Model_Taggings_Collection');
        foreach ($taggings as $tagging) {
            $this->assertEquals($tagging->node_id, $node->id);
        }
        
        // make sure we got an extra SQL call
        $count1 = count($this->_sql->getProfile());
        $this->assertEquals($count1, $count0 + 1);
        
        // lazy fetch the tags through the taggings
        $tags = $node->tags;
        $this->assertInstance($tags, 'Solar_Example_Model_Tags_Collection');
        
        // make sure the tags/taggings counts match
        $this->assertEquals(count($taggings), count($tags));
        
        // make sure each tag has a match with a tagging
        foreach ($tags as $tag) {
            $found = false;
            foreach ($taggings as $tagging) {
                if ($tagging->tag_id == $tag->id) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
        
        // make sure we got only one extra SQL call overall
        $count2 = count($this->_sql->getProfile());
        $this->assertEquals($count2, $count1 + 1);
        
        // a second check should *not* make a new SQL call
        $tags = $node->tags;
        $this->assertInstance($tags, 'Solar_Example_Model_Tags_Collection');
        $count3 = count($this->_sql->getProfile());
        $this->assertEquals($count3, $count2);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    public function test_lazyFetchAll()
    {
        // fetch all nodes, then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $node_coll = $nodes->fetchAll();
        $count_before = count($this->_sql->getProfile());
        
        // lazy-fetch each collection of taggings and tags on each node
        $extra_calls = 0;
        foreach ($node_coll as $node) {
            
            // get the taggings
            $taggings = $node->taggings;
            $this->assertInstance($taggings, 'Solar_Example_Model_Taggings_Collection');
            $extra_calls ++;
            
            // get the tags
            $tags = $node->tags;
            $this->assertInstance($tags, 'Solar_Example_Model_Tags_Collection');
            $extra_calls ++;
            
            // make sure the taggings/tags counts match
            $this->assertEquals(count($taggings), count($tags));
            
            // make sure each tagging has the right node ID
            foreach ($taggings as $tagging) {
                $this->assertEquals($tagging->node_id, $node->id);
            }
            
            // make sure each tag has a match with a tagging
            foreach ($tags as $tag) {
                $found = false;
                foreach ($taggings as $tagging) {
                    if ($tagging->tag_id == $tag->id) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found);
            }
        }
        
        // make sure we have the right number of SQL calls
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before + $extra_calls);
        
        // a second check should *not* make new SQL calls
        foreach ($node_coll as $node) {
            $taggings = $node->taggings;
            $this->assertInstance($taggings, 'Solar_Example_Model_Taggings_Collection');
            $tags = $node->tags;
            $this->assertInstance($tags, 'Solar_Example_Model_Tags_Collection');
        }
        
        $count_final = count($this->_sql->getProfile());
        $this->assertEquals($count_final, $count_after);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    public function test_eagerFetchOne()
    {
        // fetch one node with an eager tags
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array(
            'where' => array(
                'nodes.id = ?' => rand(1, 10),
            ),
            'eager' => 'tags',
        );
        
        $node = $nodes->fetchOne($params);
        $count_before = count($this->_sql->getProfile());
        
        // get the tags, make sure there are some.
        // (can't tell how many there should have been without taggings.)
        $tags = $node->tags;
        $this->assertInstance($tags, 'Solar_Example_Model_Tags_Collection');
        $this->assertTrue(count($tags) > 0);
        
        // should have been no extra SQL calls
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    public function test_eagerFetchAll()
    {
        // fetch all nodes with eager tags
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array('eager' => 'tags');
        $node_coll = $nodes->fetchAll($params);
        $count_before = count($this->_sql->getProfile());
        
        // get the tags, make sure there are some.
        // (can't tell how many there should have been without taggings.)
        foreach ($node_coll as $node) {
            $tags = $node->tags;
            $this->assertInstance($tags, 'Solar_Example_Model_Tags_Collection');
            $this->assertTrue(count($tags) > 0);
        }
        
        // should have been no extra SQL calls
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
    
    public function test_eagerFetchOne_noneRelated()
    {
        // remove taggings on one of the nodes
        $node_id = rand(1,10);
        $taggings = $this->_newModel('taggings');
        $table = $taggings->table_name;
        $cmd = "DELETE FROM $table WHERE node_id = $node_id";
        $this->_sql->query($cmd);
        
        // fetch one node with an eager tags
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array(
            'where' => array(
                'nodes.id = ?' => $node_id,
            ),
            'eager' => 'tags',
        );
        
        $node = $nodes->fetchOne($params);
        $count_before = count($this->_sql->getProfile());
        
        // get the tags, make sure there aren't any.
        $tags = $node->tags;
        $this->assertInstance($tags, 'Solar_Example_Model_Tags_Collection');
        $this->assertTrue(count($tags) == 0);
        
        // should have been no extra SQL calls
        $count_after = count($this->_sql->getProfile());
        $this->assertEquals($count_after, $count_before);
        
        // recover memory
        $taggings->free();
        unset($taggings);
        
        // recover memory
        $nodes->free();
        unset($nodes);
    }
}
