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
    
    protected $_sql;
    
    protected $_catalog;
    
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
    public function __construct($config)
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
        
        $this->_sql = Solar::factory('Solar_Sql');
        $this->_sql->setProfiling(true);
        
        // $this->_catalog = Solar::factory('Solar_Sql_Model_Catalog', array(
        //     'sql' => $this->_sql
        // ));
        
        $this->_dropAll();
        
        $before = memory_get_usage();
        
        $this->_populateAll();
        
        $after = memory_get_usage();
        $diff = $after - $before;
        
        echo "# setup memory loss : $diff\n";
    }
    
    /**
     * 
     * Setup; runs after each test method.
     * 
     */
    public function teardown()
    {
        $this->_dropAll();
        parent::teardown();
    }
    
    protected function _dropAll()
    {
        // remove test_solar_* tables
        $list = $this->_sql->fetchTableList();
        foreach ($list as $name) {
            if (substr($name, 0, 11) == 'test_solar_') {
                $this->_sql->dropTable($name);
            }
        }
        
        // remove test_solar_* sequences
        $drop = array('foo', 'bar');
        foreach ($drop as $name) {
            $this->_sql->dropSequence("test_solar_$name");
        }
        
        // reset the catalog, so that new model instances re-create
        // the tables correctly
        $this->_catalog->reset();
    }
    
    protected function _newModel($name)
    {
        $class = "Solar_Example_Model_" . ucfirst($name);
        $model = Solar::factory($class, array('sql' => $this->_sql));
        return $model;
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
        $users = $this->_newModel('users');
        $handles = array('zim', 'dib', 'gir');
        foreach ($handles as $key => $val) {
            $user = $users->fetchNew();
            $user->handle = $val;
            $user->save();
            $user = null;
            unset($user);
        }
        unset($users);
    }
    
    protected function _populateAreas()
    {
        $areas = $this->_newModel('areas');
        $names = array('Irk', 'Earth');
        foreach ($names as $key => $val) {
            $area = $areas->fetchNew();
            $area->user_id = $key + 1;
            $area->name = $val;
            $area->save();
            $area = null;
            unset($area);
        }
        unset($areas);
    }
    
    protected function _populateNodes()
    {
        // create some nodes, some for area 1 and some for 2,
        // and some for user 1 and some for user 2.
        // five nodes for each area.
        $nodes = $this->_newModel('nodes');
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
        unset($nodes);
    }
    
    protected function _populateMetas()
    {
        // one meta for each node
        $nodes = $this->_newModel('nodes');
        $metas = $this->_newModel('metas');
        $collection = $nodes->fetchAll();
        foreach ($collection as $node) {
            $meta = $metas->fetchNew();
            $meta->node_id = $node->id;
            $meta->save();
            $meta = null;
            unset($meta);
        }
        unset($metas);
    }
    
    protected function _populateTags()
    {
        // some generic tags
        $list = array('foo', 'bar', 'baz', 'zab', 'rab', 'oof');
        
        // save them
        $tags = $this->_newModel('tags');
        foreach ($list as $name) {
            $tag = $tags->fetchNew();
            $tag->name = $name;
            $tag->save();
            $tag = null;
            unset($tag);
        }
        unset($tags);
    }
    
    protected function _populateTaggings()
    {
        $tags = $this->_newModel('tags');
        $tag_coll = $tags->fetchAll();
        $tag_last = count($tag_coll) - 1;
        
        $nodes = $this->_newModel('nodes');
        $node_coll = $nodes->fetchAll();
        
        $taggings = $this->_newModel('taggings');
        
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
        
        unset($taggings);
        unset($node_coll);
        unset($nodes);
        unset($tag_coll);
        unset($tags);
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
        
        
        $this->todo('stub');
        
        
        $obj = Solar::factory('Solar_Sql_Model_Collection');
        $this->assertInstance($obj, 'Solar_Sql_Model_Collection');
    }
    
    /**
     * 
     * Test -- Magic getter.
     * 
     */
    public function test__get()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAssoc($params);
        
        $record = $coll->zim;
        $this->assertEquals($record->handle, 'zim');
    }
    
    /**
     * 
     * Test -- Checks if a data key is set.
     * 
     */
    public function test__isset()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAssoc($params);
        
        $this->assertTrue(isset($coll->zim));
        $this->assertFalse(isset($coll->no_such_handle));
    }
    
    /**
     * 
     * Test -- Magic setter.
     * 
     */
    public function test__set()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
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
    }
    
    /**
     * 
     * Test -- Sets a key in the data to null.
     * 
     */
    public function test__unset()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
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
    }
    
    /**
     * 
     * Test -- Countable: how many keys are there?
     * 
     */
    public function testCount()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        $this->assertEquals($coll->count(), 3);
    }
    
    /**
     * 
     * Test -- Counts the number of records in a related model for a given record.
     * 
     */
    public function testCountRelatedPages()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Iterator: returns the current record from the collection.
     * 
     */
    public function testCurrent()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Deletes all the records from this collection from the database.
     * 
     */
    public function testDelete()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Fetches the related record or collection for a named relationship and primary key.
     * 
     */
    public function testFetchRelated()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the validation failure message for one or more properties.
     * 
     */
    public function testGetInvalid()
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
     * Test -- Returns the current page number for a named relation.
     * 
     */
    public function testGetRelatedPage()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the status of this record.
     * 
     */
    public function testGetStatus()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Iterator: what is the key at the current position?
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
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        
        $this->assertTrue(isset($coll[0]));
        $this->assertFalse(isset($coll[9]));
    }
    
    /**
     * 
     * Test -- ArrayAccess: get a key value.
     * 
     */
    public function testOffsetGet()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
        $params = array(
            'cols'  => array('handle', 'id', 'created', 'updated'),
            'order' => 'handle',
        );
        $coll = $model->fetchAll($params);
        
        $record = $coll[0];
        $this->assertEquals($record->handle, 'dib');
    }
    
    /**
     * 
     * Test -- ArrayAccess: set a key value.
     * 
     */
    public function testOffsetSet()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
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
    }
    
    /**
     * 
     * Test -- ArrayAccess: unset a key (sets it to null).
     * 
     */
    public function testOffsetUnset()
    {
        
        
        $this->todo('stub');
        
        
        $model = $this->_newModel('users');
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
    }
    
    /**
     * 
     * Test -- Refreshes data for this record from the database.
     * 
     */
    public function testRefresh()
    {
        $this->todo('stub');
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
     * Test -- Saves all the records from this collection to the database, inserting or updating as needed.
     * 
     */
    public function testSave()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Forces one property to be "invalid" and sets a validation failure message for it.
     * 
     */
    public function testSetInvalid()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- 
     * 
     */
    public function testSetInvalids()
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
     * Test -- Sets the page number for a named relation, so that only records from that page are loaded.
     * 
     */
    public function testSetRelatedPage()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Forces the status of this record.
     * 
     */
    public function testSetStatus()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Converts the properties of this model Record or Collection to an array, including related models stored in properties.
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
    
    /**
     * 
     * Test -- Validate and sanitize the data.
     * 
     */
    public function testValidate()
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
     * Test -- 
     * 
     */
    public function test_postSave()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- 
     * 
     */
    public function test_preDelete()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- 
     * 
     */
    public function test_preSave()
    {
        $this->todo('stub');
    }


}
