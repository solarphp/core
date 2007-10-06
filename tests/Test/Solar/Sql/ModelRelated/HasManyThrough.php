<?php
class Test_Solar_Sql_ModelRelated_HasManyThrough extends Test_Solar_Sql_ModelRelated {
    
    /**
     * 
     * Depends on the Solar_Example_Model_Nodes having added $this->_hasOne().
     * 
     */
    public function testAddRelated()
    {
        $nodes = $this->_newModel('nodes');
        
        // make sure that nodes has-many taggings
        $expect = array (
            'name' => 'taggings',
            'type' => 'has_many',
            'foreign_class' => 'Solar_Example_Model_Taggings',
            'foreign_table' => 'test_solar_taggings',
            'foreign_alias' => 'taggings',
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
            'order' => array(
                0 => 'taggings.id',
            ),
            'paging' => 10,
            'cols' => array(
                0 => 'id',
                1 => 'node_id',
                2 => 'tag_id',
            ),
            'fetch' => 'all',
        );
        
        $actual = $nodes->getRelated('taggings')->toArray();
        $this->assertSame($actual, $expect);
        
        // make sure that nodes has-many tags through taggings
        $expect = array (
            'name' => 'tags',
            'type' => 'has_many',
            'foreign_class' => 'Solar_Example_Model_Tags',
            'foreign_table' => 'test_solar_tags',
            'foreign_alias' => 'tags',
            'foreign_col' => 'id',
            'foreign_inherit_col' => NULL,
            'foreign_inherit_val' => NULL,
            'foreign_primary_col' => 'id',
            'native_class' => 'Solar_Example_Model_Nodes',
            'native_table' => 'test_solar_nodes',
            'native_alias' => 'nodes',
            'native_col' => 'id',
            'through' => 'taggings',
            'through_table' => 'test_solar_taggings',
            'through_alias' => 'taggings',
            'through_native_col' => 'node_id',
            'through_foreign_col' => 'tag_id',
            'distinct' => false,
            'where' => NULL,
            'group' => NULL,
            'having' => NULL,
            'order' => array(
                0 => 'tags.id',
            ),
            'paging' => 10,
            'cols' => array(
                0 => 'id',
                1 => 'name',
                2 => 'summ',
            ),
            'fetch' => 'all',
        );
        
        $actual = $nodes->getRelated('tags')->toArray();
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
        $nodes->__destruct();
        unset($nodes);
    }
    
    public function testLazyFetchAll()
    {
        $this->_populateAll();
        
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
        $nodes->__destruct();
        unset($nodes);
    }
    
    public function testEagerFetchOne()
    {
        $this->_populateAll();
        
        // fetch one node with an eager tags
        // then see how many sql calls so far
        $nodes = $this->_newModel('nodes');
        $params = array(
            'where' => array(
                'id = ?' => rand(1, 10),
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
        $nodes->__destruct();
        unset($nodes);
    }
    
    public function testEagerFetchAll()
    {
        $this->_populateAll();
        
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
        $nodes->__destruct();
        unset($nodes);
    }
    
    public function testEagerFetchOne_noneRelated()
    {
        $this->_populateAll();
        
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
                'id = ?' => $node_id,
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
        $taggings->__destruct();
        unset($taggings);
        
        // recover memory
        $nodes->__destruct();
        unset($nodes);
    }
}
