<?php
abstract class Test_Solar_Sql_ModelRelated extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql_Model = array(
    );
    
    /**
     * 
     * The SQL connection.
     * 
     */
    protected $_sql;
    
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
        $this->_dropAll();
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
    
    /**
     * 
     * Convenience method to get model objects with SQL dependency injection.
     * 
     */
    protected function _newModel($name)
    {
        $class = "Solar_Example_Model_" . ucfirst($name);
        $model = Solar::factory($class, array('sql' => $this->_sql));
        return $model;
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
        }
        
        // recover memory
        $users->free();
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
        }   
        
        // recover memory
        $areas->free();
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
        }
        
        // recover memory
        $nodes->free();
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
        }
        
        // recover memory
        $nodes->free();
        unset($nodes);
        
        // recover memory
        $metas->free();
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
        }
        
        // recover memory
        $tags->free();
        unset($tags);
    }
    
    protected function _populateTaggings()
    {
        $tags = $this->_newModel('tags');
        $nodes = $this->_newModel('nodes');
        $taggings = $this->_newModel('taggings');
        
        $tag_coll = $tags->fetchAll();
        $tag_last = count($tag_coll) - 1;
        
        $node_coll = $nodes->fetchAll();
        
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
            }
        }
        
        // recover memory
        $tags->free();
        unset($tags);
        
        // recover memory
        $nodes->free();
        unset($nodes);
        
        // recover memory
        $taggings->free();
        unset($taggings);
    }
}