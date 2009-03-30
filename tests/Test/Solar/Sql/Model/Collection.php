<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Sql_Model_Collection extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql_Model_Collection = array(
    );
    
    protected $_sql_config = array(
        'adapter' => 'Solar_Sql_Adapter_Sqlite',
    );
    
    protected $_sql = null;
    
    protected $_catalog_config = array(
        'classes' => array(
            'Solar_Example_Model',
        ),
    );
    
    protected $_catalog = null;
    
    
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
        
        // set up an SQL connection
        $this->_sql = Solar::factory(
            'Solar_Sql',
            $this->_sql_config
        );
        $this->_sql->setProfiling(true);
        
        // set up a model catalog
        $this->_catalog = Solar::factory(
            'Solar_Sql_Model_Catalog',
            $this->_catalog_config
        );
        
        // register the connection and catalog
        Solar_Registry::set('sql', $this->_sql);
        Solar_Registry::set('model_catalog', $this->_catalog);
        
        // populate tables
        $this->_populateAll();
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
    
    protected function _populateAll()
    {
        $this->_populateUsers();
        $this->_populateAreas();
        $this->_populateNodes();
        $this->_populateMetas();
        $this->_populateTags();
        $this->_populateTaggings();
    }
    
    protected function _populateUsers()
    {
        $users = $this->_catalog->getModel('users');
        $handles = array('zim', 'dib', 'gir');
        foreach ($handles as $key => $val) {
            $user = $users->fetchNew();
            $user->handle = $val;
            $user->save();
            $user = null;
            unset($user);
        }
    }
    
    protected function _populateAreas()
    {
        $areas = $this->_catalog->getModel('areas');
        $names = array('Irk', 'Earth');
        foreach ($names as $key => $val) {
            $area = $areas->fetchNew();
            $area->user_id = $key + 1;
            $area->name = $val;
            $area->save();
            $area = null;
            unset($area);
        }
    }
    
    protected function _populateNodes()
    {
        // create some nodes, some for area 1 and some for 2,
        // and some for user 1 and some for user 2.
        // five nodes for each area.
        $nodes = $this->_catalog->getModel('nodes');
        for ($i = 1; $i <= 10; $i++) {
            $node = $nodes->fetchNew();
            $node->subj = "Subject Line $i: " . substr(md5($i), 0, 5);
            $node->body = "Body for $i ... " . md5($i);
            $node->area_id = $i % 2 + 1; // sometimes 1, sometimes 2
            $node->user_id = ($i + 1) % 2 + 1; // sometimes 2, sometimes 1
            $node->save();
            $node = null;
            unset($node);
        }
    }
    
    protected function _populateMetas()
    {
        // one meta for each node
        $nodes = $this->_catalog->getModel('nodes');
        $metas = $this->_catalog->getModel('metas');
        $collection = $nodes->fetchAll();
        foreach ($collection as $node) {
            $meta = $metas->fetchNew();
            $meta->node_id = $node->id;
            $meta->save();
            $meta = null;
            unset($meta);
        }
    }
    
    protected function _populateTags()
    {
        // some generic tags
        $list = array('foo', 'bar', 'baz', 'zab', 'rab', 'oof');
        
        // save them
        $tags = $this->_catalog->getModel('tags');
        foreach ($list as $name) {
            $tag = $tags->fetchNew();
            $tag->name = $name;
            $tag->save();
            $tag = null;
            unset($tag);
        }
    }
    
    protected function _populateTaggings()
    {
        $tags = $this->_catalog->getModel('tags');
        $tag_coll = $tags->fetchAll();
        $tag_last = count($tag_coll) - 1;
        
        $nodes = $this->_catalog->getModel('nodes');
        $node_coll = $nodes->fetchAll();
        
        $taggings = $this->_catalog->getModel('taggings');
        
        // add some tags on each node through taggings
        foreach ($node_coll as $node) {
            
            // add 2-5 tags on this node
            $tags_to_add = rand(2,5);
            
            // which tags have we used already?
            $tags_used = array();
            
            // add each of the tags
            for ($i = 0; $i < $tags_to_add; $i ++) {
                
                // pick a random tag that has not been used yet
                do {
                    $tagno = rand(0, $tag_last);
                } while (in_array($tagno, $tags_used));
                
                // mark it as used
                $tags_used[] = $tagno;
                
                // get the tag from the collection
                $tag = $tag_coll[$tagno];
                
                // match the node to the tag with a tagging
                $tagging = $taggings->fetchNew();
                $tagging->node_id = $node->id;
                $tagging->tag_id = $tag->id;
                $tagging->save();
                
                $tag = null;
                unset($tag);
                
                $tagging = null;
                unset($taagging);
            }
        }
        
        $node_coll->free();
        $tag_coll->free();
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
        $obj = Solar::factory('Solar_Sql_Model_Collection');
        $this->assertInstance($obj, 'Solar_Sql_Model_Collection');
    }
    
    /**
     * 
     * Test -- Returns a record from the collection based on its key value.
     * 
     */
    public function test__get()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAssoc($params);
        
        $record = $coll->zim;
        $this->assertEquals($record->handle, 'zim');
        
        $record->free();
    }
    
    /**
     * 
     * Test -- Does a certain key exist in the data?
     * 
     */
    public function test__isset()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAssoc($params);
        
        $this->assertTrue(isset($coll->zim));
        $this->assertFalse(isset($coll->no_such_handle));
        
        $coll->free();
    }
    
    /**
     * 
     * Test -- Sets a key value.
     * 
     */
    public function test__set()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAssoc($params);
        
        // get a record, make sure it's the right one
        $record = $coll->zim;
        $this->assertEquals($record->handle, 'zim');
        
        // clone it and replace within the collection
        $clone = clone $record;
        $clone->handle = 'zim-zim';
        $coll->zim = $clone;
        
        // make sure it was really replaced
        $this->assertSame($coll->zim, $clone);
        $this->assertNotSame($coll->zim, $record);
        
        $clone->free();
        $record->free();
        $coll->free();
    }
    
    /**
     * 
     * Test -- Sets a key in the data to null.
     * 
     */
    public function test__unset()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAssoc($params);
        
        // get a record, make sure it's the right one
        $record = $coll->zim;
        $this->assertEquals($record->handle, 'zim');
        
        // unset it from the collection
        unset($coll->zim);
        
        // make sure it's not set any more
        $this->assertFalse(isset($coll->zim));
        
        $coll->free();
        $record->free();
    }
    
    /**
     * 
     * Test -- Countable: how many keys are there?
     * 
     */
    public function testCount()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        $this->assertEquals($coll->count(), 3);
        
        $coll->free();
    }
    
    /**
     * 
     * Test -- Iterator: get the current value for the array pointer.
     * 
     */
    public function testCurrent()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Deletes each record in the collection one-by-one.
     * 
     */
    public function testDelete()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the model from which the data originates.
     * 
     */
    public function testGetModel()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the injected pager information for the collection.
     * 
     */
    public function testGetPagerInfo()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Iterator: get the current key for the array pointer.
     * 
     */
    public function testKey()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Loads the struct with data from an array or another struct.
     * 
     */
    public function testLoad()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Loads *related* data for the collection.
     * 
     */
    public function testLoadRelated()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Iterator: move to the next position.
     * 
     */
    public function testNext()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- ArrayAccess: does the requested key exist?
     * 
     */
    public function testOffsetExists()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        
        $this->assertTrue(isset($coll[0]));
        $this->assertFalse(isset($coll[9]));
        
        $coll->free();
    }
    
    /**
     * 
     * Test -- ArrayAccess: get a key value.
     * 
     */
    public function testOffsetGet()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        
        $record = $coll[0];
        $this->assertEquals($record->handle, 'dib');
        
        $record->free();
        $coll->free();
    }
    
    /**
     * 
     * Test -- ArrayAccess: set a key value; appends to the array when using [] notation.
     * 
     */
    public function testOffsetSet()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        
        // get a record, make sure it's the right one
        $record = $coll[0];
        $this->assertEquals($record->handle, 'dib');
        
        // clone it and replace within the collection
        $clone = clone $record;
        $clone->handle = 'dib-dib';
        $coll[0] = $clone;
        
        // make sure it was really replaced
        $this->assertSame($coll[0], $clone);
        $this->assertNotSame($coll[0], $record);
        
        $clone->free();
        $record->free();
        $coll->free();
    }
    
    /**
     * 
     * Test -- ArrayAccess: unset a key.
     * 
     */
    public function testOffsetUnset()
    {
        $model = $this->_catalog->getModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        
        // get a record, make sure it's the right one
        $record = $coll[0];
        $this->assertEquals($record->handle, 'dib');
        
        // unset it from the collection
        unset($coll[0]);
        
        // make sure it's not set any more
        $this->assertFalse(isset($coll[0]));
        
        $record->free();
        $coll->free();
    }
    
    /**
     * 
     * Test -- Iterator: move to the first position.
     * 
     */
    public function testRewind()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Saves all the records from this collection to the database one-by-one, inserting or updating as needed.
     * 
     */
    public function testSave()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Injects the model from which the data originates.
     * 
     */
    public function testSetModel()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Injects pager information for the collection.
     * 
     */
    public function testSetPagerInfo()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the data for each record in this collection as an array.
     * 
     */
    public function testToArray()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Iterator: is the current position valid?
     * 
     */
    public function testValid()
    {
        $this->todo('stub');
    }
}
