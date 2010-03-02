<?php
class Test_Solar_Sql_Model_Related_Queries extends Solar_Test
{
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql_Model_Related_Queries = array(
    );
    
    protected $_sql_config = array(
        'adapter' => 'Solar_Sql_Adapter_Sqlite',
    );
    
    protected $_sql = null;
    
    protected $_catalog_config = array(
        'classes' => array(
            'Mock_Solar_Model',
        ),
    );
    
    protected $_catalog = null;
    
    protected $_fixture = null;
    
    // -----------------------------------------------------------------
    // 
    // Support methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Setup; runs before each test method.
     * 
     */
    public function preTest()
    {
        parent::preTest();
        
        // set up an SQL connection
        $this->_sql = Solar::factory(
            'Solar_Sql',
            $this->_sql_config
        );
        
        // set up a model catalog
        $this->_catalog = Solar::factory(
            'Solar_Sql_Model_Catalog',
            $this->_catalog_config
        );
        
        // register the connection and catalog
        Solar_Registry::set('sql', $this->_sql);
        Solar_Registry::set('model_catalog', $this->_catalog);
        
        // fixture to populate tables
        $this->_fixture = Solar::factory('Fixture_Solar_Sql_Model');
        $this->_fixture->setup();
        
        // preload all models to get discovery out of the way
        $this->_catalog->users;
        $this->_catalog->prefs;
        $this->_catalog->areas;
        $this->_catalog->nodes;
        $this->_catalog->metas;
        $this->_catalog->tags;
        $this->_catalog->taggings;
        $this->_catalog->comments;

        $this->_sql->setProfiling(true);
    }
    
    protected function _diagProfile()
    {
        $profile = $this->_sql->getProfile();
        foreach ($profile as $val) {
            $this->diag($val['data'], $val['stmt']);
        }
    }
    
    // -----------------------------------------------------------------
    // 
    // Test methods.
    // 
    // -----------------------------------------------------------------
    
    public function test_chainedEagerToOneUsesSingleQuery()
    {
        $this->diag('ticket 210');
        
        $area = $this->_catalog->areas->fetchOne(array(
            'eager' => array(
                'user' => array( // is in master fetch as it should be
                    'eager' => array(
                        'pref', // should also be in master fetch, but isn't
                    ),
                ),
            ),
        ));
        
        $this->todo();
    }
    
    // when you use native-by select, the native should drop unnecessary
    // joins (typically left joins).
    public function test_nativeBySelectOnEagerFetch()
    {
        $this->diag('ticket 211');
        
        $nodes = $this->_catalog->nodes->fetchAllAsArray(array(
            'where' => 'nodes.id <= 10',
            'eager' => array(
                'meta',
                'comments' => array(
                    'native_by' => 'select',
                ),
            ),
        ));
        
        // did we actually get nodes?
        $this->assertTrue(count($nodes) == 10);
        
        // get the profile and find the second statement
        // (first was the node+meta, second is comments)
        $profile = $this->_sql->getProfile();
        $actual = $profile[1]['stmt'];
        
        // the expected statement
        $expect = '
SELECT
    "comments"."id" AS "id",
    "comments"."created" AS "created",
    "comments"."updated" AS "updated",
    "comments"."node_id" AS "node_id",
    "comments"."email" AS "email",
    "comments"."uri" AS "uri",
    "comments"."body" AS "body"
FROM "test_solar_comments" "comments"
INNER JOIN (SELECT
    "id" AS "id"
FROM "test_solar_nodes" "nodes"
WHERE
    "nodes"."id" <= 10
) "nodes" ON "nodes"."id" = "comments"."node_id"
';
        
        // check it
        $this->assertEquals(trim($actual), trim($expect));
    }
}
