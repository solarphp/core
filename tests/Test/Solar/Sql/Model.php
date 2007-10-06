<?php
/**
 * 
 * Multiple concrete classes test.
 * 
 * Test Series for Models
 * ======================
 * 
 * - Architecture thoughts
 * 
 *     - Do we still actually *need* Catalog, now that main models are somewhat lighter?
 *     
 *         - Could put cache in the Model, not the catalog
 *     
 *     - Should we skip auto-creation in favor of migrations?
 *     
 * - construction
 * 
 *     - creates table if not present ... does that go in Catalog?
 *     
 *     - does not create table if present ... does that go in Catalog?
 *     
 *     - reads table properly (and overrides local settings)
 *     
 * - test honoring of $_fetch_cols
 * 
 * - collections
 * 
 *     - append to a collection
 * 
 *     - save a collection
 * 
 *     - delete from a collection
 * 
 *     - do unit-of-work on a collection
 * 
 * - automatic setting of related ID for belongs_to, has_one, and has_many
 * 
 */
class Test_Solar_Sql_Model extends Solar_Test {
    
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
        $class = "Solar_Example_Model_$name";
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
    
    protected function _populateSpecialColsTable()
    {
        $model = $this->_newModel('TestSolarSpecialCols');
        for ($i = 1; $i <= 10; $i++) {
            $record = $model->fetchNew();
            $record->name = chr($i+96); //ascii 'a', 'b', etc
            $record->save();
        }
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
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
        $model = $this->_newModel('TestSolarSpecialCols');
        $this->assertInstance($model, 'Solar_Sql_Model');
        $this->assertInstance($model, 'Solar_Example_Model_TestSolarSpecialCols');
        
        // did it create the table automatically?
        $list = $this->_sql->fetchTableList();
        $this->assertTrue(in_array('test_solar_special_cols', $list));
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Magic call implements "fetchOneBy...()" and "fetchAllBy...()"
     * for columns listed in the method name.
     * 
     */
    public function test__call()
    {
        /**
         * populate the table, along with some extra records so we have more
         * to work with
         */
        $this->_populateSpecialColsTable();
        $model = $this->_newModel('TestSolarSpecialCols');
        for ($i = 0; $i < 5; $i++) {
            // id's are 11-15, all named 'z' with seq_foo of 88
            $record = $model->fetchNew();
            $record->name = 'z'; 
            $record->seq_foo = 88;
            $model->insert($record);
        }
        
        // add some extras so we can see if fetchAll() grabs more than it should
        for ($i = 0; $i < 5; $i++) {
            // id's are 16-20, all named 'z' with seq_foo of 88
            $record = $model->fetchNew();
            $record->name = 'z'; 
            $record->seq_foo = 99;
            $model->insert($record);
        }
        
        /**
         * fetchOneBy*() single-col
         */
        
        // make sure the method "fetchOneByName" does not actually exist
        $exists = method_exists($model, 'fetchOneByName');
        $this->assertFalse($exists);
        
        // call the magic method
        $record = $model->fetchOneByName('z');
        $this->assertInstance($record, 'Solar_Sql_Model_Record');
        $this->assertInstance($record, 'Solar_Example_Model_TestSolarSpecialCols_Record');
        $this->assertEquals($record->id, 11);
        $this->assertEquals($record->name, 'z');
        
        /**
         * fetchOneBy*() multi-col
         */
        
        // make sure the method "fetchOneByNameAndSeqFoo" does not actually exist
        $exists = method_exists($model, 'fetchOneByNameAndSeqFoo');
        $this->assertFalse($exists);
        
        // call the magic method
        $record = $model->fetchOneByNameAndSeqFoo('z', 88);
        $this->assertInstance($record, 'Solar_Sql_Model_Record');
        $this->assertInstance($record, 'Solar_Example_Model_TestSolarSpecialCols_Record');
        $this->assertEquals($record->id, 11);
        $this->assertEquals($record->name, 'z');
        $this->assertEquals($record->seq_foo, '88');
        
        /**
         * fetchAllBy*() single-col
         */
        
        // make sure the method "fetchAllByName" does not actually exist
        $exists = method_exists($model, 'fetchAllByName');
        $this->assertFalse($exists);
        
        // call the magic method
        $collection = $model->fetchAllByName('z');
        $this->assertInstance($collection, 'Solar_Sql_Model_Collection');
        $this->assertInstance($collection, 'Solar_Example_Model_TestSolarSpecialCols_Collection');
        $this->assertEquals(count($collection), 10);
        foreach ($collection as $key => $record) {
            $this->assertEquals($record->id, $key + 11);
            $this->assertEquals($record->name, 'z');
        }
        
        /**
         * fetchAllBy*() multi-col
         */
        
        // make sure the method "fetchAllByNameAndSeqFoo" does not actually exist
        $exists = method_exists($model, 'fetchAllByNameAndSeqFoo');
        $this->assertFalse($exists);
        
        // call the magic method
        $collection = $model->fetchAllByNameAndSeqFoo('z', 88);
        $this->assertInstance($collection, 'Solar_Sql_Model_Collection');
        $this->assertInstance($collection, 'Solar_Example_Model_TestSolarSpecialCols_Collection');
        $this->assertEquals(count($collection), 5);
        foreach ($collection as $key => $record) {
            $this->assertEquals($record->id, $key + 11);
            $this->assertEquals($record->name, 'z');
            $this->assertEquals($record->seq_foo, 88);
        }
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Read-only access to protected model properties.
     * 
     */
    public function test__get()
    {
        $model = $this->_newModel('TestSolarSpecialCols');
        
        // reads from protected $_primary_col; should be no exception
        $actual = $model->primary_col;
        $expect = 'id';
        $this->assertSame($actual, $expect);
        
        // try for a property that doesn't exist
        try {
            $actual = $model->no_such_property;
            $this->fail('should have thrown an exception here');
        } catch (Solar_Exception $e) {
            // do nothing, this is the expected case :-)
        }
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Fetches count and pages of available records.
     * 
     */
    public function testCountPages()
    {
        $this->_populateSpecialColsTable();
        $model = $this->_newModel('TestSolarSpecialCols');
        $model->setPaging(3);
        $actual = $model->countPages();
        $expect = array('count' => 10, 'pages' => 4);
        $this->assertEquals($actual, $expect);
        
        // now count on a WHERE clause
        $where = array(
            'id > 5'
        );
        $actual = $model->countPages(array('where' => $where));
        $expect = array('count' => 5, 'pages' => 2);
        $this->assertEquals($actual, $expect);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Deletes a record from the database.
     * 
     */
    public function testDelete()
    {
        $this->_populateSpecialColsTable();
        $model = $this->_newModel('TestSolarSpecialCols');
        
        $record = $model->fetch(7);
        $this->assertEquals($record->id, 7);
        $model->delete($record);
        
        // the record should not allow modification now
        try {
            $record->name = 'foo';
            $this->fail('should not have been able to modify deleted record');
        } catch (Solar_Exception $e) {
            // this is the expected case
        }
        
        // should not be able to retrieve the record
        $record = $model->fetch(7);
        $this->assertNull($record);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Fetches a record or collection by primary key value(s).
     * 
     */
    public function testFetch()
    {
        // insert a set of records
        $this->_populateSpecialColsTable();
        
        // fetch by number to get a Record
        $model = $this->_newModel('TestSolarSpecialCols');
        $record = $model->fetch(3);
        $this->assertInstance($record, 'Solar_Sql_Model_Record');
        $this->assertInstance($record, 'Solar_Example_Model_TestSolarSpecialCols_Record');
        $this->assertEquals($record->name, 'c'); // make sure it's the right one ;-)
        
        // fetch by array to get a Collection
        $list = array(2, 3, 5, 7);
        $collection = $model->fetch($list);
        $this->assertInstance($collection, 'Solar_Sql_Model_Collection');
        $this->assertInstance($collection, 'Solar_Example_Model_TestSolarSpecialCols_Collection');
        $this->assertEquals(count($collection), 4);
        foreach ($collection as $record) {
            // make sure they're the right ones ;-)
            $this->assertEquals($record->name, chr($record->id + 96));
        }
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Fetches all records by arbitrary parameters.
     * 
     */
    public function testFetchAll()
    {
        // insert a set of records
        $this->_populateSpecialColsTable();
        
        // fetch by some WHERE clause
        $model = $this->_newModel('TestSolarSpecialCols');
        $collection = $model->fetchAll(array('where' => 'id > 5'));
        
        // tests
        $this->assertInstance($collection, 'Solar_Sql_Model_Collection');
        $this->assertInstance($collection, 'Solar_Example_Model_TestSolarSpecialCols_Collection');
        $this->assertEquals(count($collection), 5);
        foreach ($collection as $record) {
            // make sure they're the right ones ;-)
            $this->assertEquals($record->name, chr($record->id + 96));
        }
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- The same as fetchAll(), except the record collection is keyed on the first column of the results (instead of being a strictly sequential array.)  Recognized parameters for the fetch are:  `cols` : (string|array) Return only these columns.
     * 
     */
    public function testFetchAssoc()
    {
        // insert a set of records
        $this->_populateSpecialColsTable();
        
        // fetch by some WHERE clause
        $model = $this->_newModel('TestSolarSpecialCols');
        $collection = $model->fetchAssoc(array(
            'where' => 'id > 5',
            'order' => 'name',
            'cols' => array(
                'name', 'id', 'seq_foo'
            ),
        ));
        
        // generic
        $this->assertInstance($collection, 'Solar_Sql_Model_Collection');
        $this->assertInstance($collection, 'Solar_Example_Model_TestSolarSpecialCols_Collection');
        $this->assertEquals(count($collection), 5);
        
        // specific: array keys should be on 'name', not 'id'
        $array = $collection->toArray();
        $actual = array_keys($array);
        $expect = array('f', 'g', 'h', 'i', 'j');
        $this->assertSame($actual, $expect);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Fetches a sequential array of values from the model, using only the first column of the results.
     * 
     */
    public function testFetchCol()
    {
        // insert a set of records
        $this->_populateSpecialColsTable();
        
        // fetch by some WHERE clause
        $model = $this->_newModel('TestSolarSpecialCols');
        $actual = $model->fetchCol(array(
            'where' => 'id > 5',
            'order' => 'name',
            'cols' => array(
                'name',
            ),
        ));
        
        $expect = array('f', 'g', 'h', 'i', 'j');
        $this->assertSame($actual, $expect);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Returns a new record with default values.
     * 
     */
    public function testFetchNew()
    {
        $model = $this->_newModel('TestSolarSqlDescribe');
        $record = $model->fetchNew();
        
        // these are the default values on the test_solar_sql_describe table
        $this->assertNull($record->test_default_null);
        $this->assertEquals($record->test_default_string, 'literal');
        $this->assertEquals($record->test_default_integer, 7);
        $this->assertEquals($record->test_default_numeric, 1234.567);
        $this->assertNull($record->test_default_ignore);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Fetches one record by arbitrary parameters.
     * 
     */
    public function testFetchOne()
    {
        // insert a set of records
        $this->_populateSpecialColsTable();
        
        // fetch by number to get a Record
        $model = $this->_newModel('TestSolarSpecialCols');
        $record = $model->fetchOne(array(
            'where' => array('name = ?' => 'c'),
        ));
        
        $this->assertInstance($record, 'Solar_Sql_Model_Record');
        $this->assertInstance($record, 'Solar_Example_Model_TestSolarSpecialCols_Record');
        $this->assertEquals($record->name, 'c'); // make sure it's the right one ;-)
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Fetches an array of key-value pairs from the model, where the first column is the key and the second column is the value.
     * 
     */
    public function testFetchPairs()
    {
        // insert a set of records
        $this->_populateSpecialColsTable();
        
        // fetch by some WHERE clause
        $model = $this->_newModel('TestSolarSpecialCols');
        $actual = $model->fetchPairs(array(
            'where' => 'id > 5',
            'order' => 'name',
            'cols' => array(
                'name', 'id',
            ),
        ));
        
        // should get back key-value pairs
        $expect = array(
            'f' => '6',
            'g' => '7',
            'h' => '8',
            'i' => '9',
            'j' => '10',
        );
        $this->assertEquals($actual, $expect);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Gets the number of records per page.
     * 
     */
    public function testGetPaging()
    {
        $model = $this->_newModel('TestSolarSpecialCols');
        
        $expect = 50;
        $model->setPaging($expect);
        
        $actual = $model->getPaging();
        $this->assertEquals($actual, $expect);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and inserts a Record into the table.
     * 
     */
    public function testInsert()
    {
        $model = $this->_newModel('TestSolarFoo');
        $record = $model->fetchNew();
        
        $email = 'nobody@example.com';
        $uri   = 'http://example.com';
        $name  = 'Nobody Example';
        
        $record->email = $email;
        $record->uri   = $uri;
        $record->name  = $name;
        
        // insert and make sure we got the ID back
        $model->insert($record);
        $this->assertEquals($record->id, 1);
        
        // now fetch and make sure the insert "took"
        $record = $model->fetch(1);
        $this->assertEquals($record->email, $email);
        $this->assertEquals($record->uri, $uri);
        $this->assertEquals($record->name, $name);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and inserts a Record into the table, checking
     * for invalidation at the record level.
     * 
     */
    public function testInsert_invalid()
    {
        $model = $this->_newModel('TestSolarFoo');
        
        // insert should fail
        $record = $model->fetchNew();
        $record->email = 'not-an-email';
        try {
            $model->insert($record);
            $this->fail('should have thrown ERR_INVALID');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Sql_Model_Record_Exception_Invalid');
        }
        
        // should have failed on email
        $invalid = $record->getInvalid();
        $actual = array_keys($invalid);
        $expect = array('email');
        $this->assertSame($actual, $expect);
        
        // insert should pass
        $record->email = 'nobody@example.com';
        $model->insert($record);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and inserts a Record into the table, handling exceptions
     * from the database.
     * 
     */
    public function testInsert_invalidAtDatabase()
    {
        $model = $this->_newModel('TestSolarFoo');
        
        // insert should succeed
        $record = $model->fetchNew();
        $record->email = 'nobody@example.com';
        $model->insert($record);
        
        // insert should fail **at database** because of unique index on the
        // email column.
        $record = $model->fetchNew();
        $record->email = 'nobody@example.com';
        try {
            $model->insert($record);
            $this->fail('should have thrown ERR_QUERY_FAILED');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Sql_Adapter_Exception_QueryFailed');
        }
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and inserts a Record into the table, checking
     * for single-table inheritance value.
     * 
     */
    public function testInsert_inherit()
    {
        $model = $this->_newModel('TestSolarFoo_Bar');
        
        // it should self-set its inheritance value
        $record = $model->fetchNew();
        $this->assertEquals($record->inherit, 'Bar');
        
        // if we clear the inheritance value, it should self-set again
        $record->inherit = null;
        $model->insert($record);
        $this->assertEquals($record->inherit, 'Bar');
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Returns the appropriate collection object for this model.
     * 
     * Takes single-table inheritance into account.
     * 
     */
    public function testNewCollection()
    {
        $data = array();
        
        $model = $this->_newModel('TestSolarFoo');
        $collection = $model->newCollection($data);
        $this->assertInstance($collection, 'Solar_Example_Model_TestSolarFoo_Collection');
        
        // the Foo_Bar model doesn't have its own collection, should fall back to foo
        $model = $this->_newModel('TestSolarFoo_Bar');
        $collection = $model->newCollection($data);
        $this->assertInstance($collection, 'Solar_Example_Model_TestSolarFoo_Collection');
        
        // the Dib mode has no collection and is not inherited, should fall back to Solar_Sql
        $model = $this->_newModel('TestSolarDib');
        $collection = $model->newCollection($data);
        $this->assertInstance($collection, 'Solar_Sql_Model_Collection');
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Returns the appropriate record object for an inheritance model.
     * 
     * Takes single-table inheritance into account.
     * 
     */
    public function testNewRecord()
    {
        $data = array(
          'id'      => '88',
          'created' => date('Y-m-d H:i:s'),
          'updated' => date('Y-m-d H:i:s'),
          'inherit' => null,
          'name'    => null,
          'email'   => null,
          'uri'     => null,
        );
        
        
        // non-inherited
        $model = $this->_newModel('TestSolarFoo');
        $record = $model->newRecord($data);
        $this->assertInstance($record, 'Solar_Example_Model_TestSolarFoo_Record');
        
        // single-table inherited when available
        $data['inherit'] = 'Bar';
        $record = $model->newRecord($data);
        $this->assertInstance($record, 'Solar_Example_Model_TestSolarFoo_Bar_Record');
        
        // parent when inherited not available
        $data['inherit'] = 'No_Such_Class';
        $record = $model->newRecord($data);
        $this->assertInstance($record, 'Solar_Example_Model_TestSolarFoo_Record');
        
        // the Dib model has no record of its own, should use Solar_Sql_Model_Record
        $model = $this->_newModel('TestSolarDib');
        $record = $model->newRecord($data);
        $this->assertInstance($record, 'Solar_Sql_Model_Record');
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Sets the number of records per page.
     * 
     */
    public function testSetPaging()
    {
        $this->_populateSpecialColsTable();
        
        // set it
        $model = $this->_newModel('TestSolarSpecialCols');
        $expect = 3;
        $model->setPaging($expect);
        
        // make sure it's recognized
        $actual = $model->getPaging();
        $this->assertEquals($actual, $expect);
        
        /**
         * make sure the setting is honored
         */
         
        // get the first page of 3 records
        $collection = $model->fetchAll(array('order' => 'id', 'page' => 1));
        $this->assertEquals(count($collection), 3);
        
        // make sure they're the right ones: 1, 2, 3
        foreach ($collection as $key => $record) {
            $this->assertEquals($record->id, $key + 1);
        }
        
        // get the third page of 3 records; this should also be 3 records
        $collection = $model->fetchAll(array('order' => 'id', 'page' => 3));
        $this->assertEquals(count($collection), 3);
        
        // make sure they're the right ones: 7, 8, 9
        foreach ($collection as $key => $record) {
            $this->assertEquals($record->id, $key + 7);
        }
        
        // get the 4th page of 3 records: this should be 1 record, #10
        $collection = $model->fetchAll(array('order' => 'id', 'page' => 4));
        $this->assertEquals(count($collection), 1);
        $this->assertEquals($collection[0]->id, 10);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and updates a Record in the table.
     * 
     */
    public function testUpdate()
    {
        $model = $this->_newModel('TestSolarFoo');
        
        /**
         * insert a valid record
         */
        $record = $model->fetchNew();
        
        $email = 'nobody@example.com';
        $uri   = 'http://example.com';
        $name  = 'Nobody Example';
        
        $record->email = $email;
        $record->uri   = $uri;
        $record->name  = $name;
        
        // insert and make sure we got the ID back
        $model->insert($record);
        $this->assertEquals($record->id, 1);
        
        /**
         * fetch and update the record
         */
         
        // fetch and make sure the insert "took"
        $record = $model->fetch(1);
        $this->assertEquals($record->email, $email);
        $this->assertEquals($record->uri, $uri);
        $this->assertEquals($record->name, $name);
        
        // change something and update
        $name = 'Another Example';
        $record->name = $name;
        $model->update($record, null);
        
        // did it change in the record?
        $this->assertEquals($record->email, $email);
        $this->assertEquals($record->uri, $uri);
        $this->assertEquals($record->name, $name);
        
        // did the update "take"?
        $record = $model->fetch(1);
        $this->assertEquals($record->email, $email);
        $this->assertEquals($record->uri, $uri);
        $this->assertEquals($record->name, $name);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and updates a Record into the table, checking
     * for invalidation at the record level.
     * 
     */
    public function testUpdate_invalid()
    {
        $model = $this->_newModel('TestSolarFoo');
        
        // insert should pass
        $record = $model->fetchNew();
        $record->email = 'nobody@example.com';
        $model->insert($record);
        
        // update should fail
        $record->email = 'not-an-email';
        try {
            $model->update($record, null);
            $this->fail('should have thrown ERR_INVALID');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Sql_Model_Record_Exception_Invalid');
        }
        
        // should have failed on email
        $invalid = $record->getInvalid();
        $actual = array_keys($invalid);
        $expect = array('email');
        $this->assertSame($actual, $expect);
        
        // update should pass
        $record->email = 'another@example.com';
        $model->update($record, null);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and inserts a Record into the table, handling exceptions
     * from the database.
     * 
     */
    public function testUpdate_invalidAtDatabase()
    {
        $model = $this->_newModel('TestSolarFoo');
        
        // insert should succeed
        $record = $model->fetchNew();
        $record->email = 'nobody@example.com';
        $model->insert($record);
        
        // insert another record to work with
        $record = $model->fetchNew();
        $record->email = 'another@example.com';
        $model->insert($record);
        
        // now modify the more-recent record, and fail the uniqueness index
        // at the database.
        $record->email = 'nobody@example.com';
        try {
            $model->update($record, null);
            $this->fail('should have thrown ERR_QUERY_FAILED');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Sql_Adapter_Exception_QueryFailed');
        }
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- Filters and updates a Record into the table, checking
     * for single-table inheritance value.
     * 
     */
    public function testUpdate_inherit()
    {
        $model = $this->_newModel('TestSolarFoo_Bar');
        
        // it should self-set its inheritance value
        $record = $model->fetchNew();
        $this->assertEquals($record->inherit, 'Bar');
        
        // if we clear the inheritance value, it should self-set again
        $record->inherit = null;
        $model->insert($record);
        $this->assertEquals($record->inherit, 'Bar');
        
        // if we fetch, clear, and update, it should self-set *again*
        $record = $model->fetch(1);
        $this->assertEquals($record->inherit, 'Bar');
        $record->inherit = null;
        $model->update($record, null);
        $this->assertEquals($record->inherit, 'Bar');
        $record = $model->fetch(1);
        $this->assertEquals($record->inherit, 'Bar');
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }
    
    /**
     * 
     * Test -- special column behaviors.
     * 
     */
    public function testSpecialColumns()
    {
        $model = $this->_newModel('TestSolarSpecialCols');
        
        /**
         * Correct population of new columns
         */
        
        $record = $model->fetchNew();
        $model->insert($record);
        $now = date('Y-m-d H:i:s');
        
        // autoincremented id
        $this->assertEquals($record->id, 1);
        
        // created & updated
        $created = $record->created;
        $this->assertEquals($record->created, $now);
        $this->assertEquals($record->updated, $now);
        
        // auto-sequence foo & bar
        $this->assertEquals($record->seq_foo, 1);
        $this->assertEquals($record->seq_bar, 1);
        
        /**
         * Correct "updated" and sequence numbering
         */
        
        $record = $model->fetch(1);
        $record->seq_bar = null;
        $model->update($record, null);
        $now = date('Y-m-d H:i:s');
        
        // created should be as original
        $this->assertEquals($record->created, $created);
        
        // updated should have changed
        $this->assertEquals($record->updated, $now);
        
        // seq_foo should still be 1, but seq_bar should have been increased
        $this->assertEquals($record->seq_foo, 1);
        $this->assertEquals($record->seq_bar, 2);
        
        /**
         * Serializing
         */
        // first, save something to be serialized
        $expect = array('foo', 'bar', 'baz');
        $record->serialize = $expect;
        $model->update($record, null);
        
        // should have been unserialized after saving
        $this->assertSame($record->serialize, $expect);
        
        // now retrieve from the database and see if it unserialized
        $record = $model->fetch(1);
        $this->assertSame($record->serialize, $expect);
        
        /**
         * 
         * Autoinc and sequences on a second record
         * 
         */
        $record = $model->fetchNew();
        $model->insert($record);
        $this->assertEquals($record->id, 2);
        $this->assertEquals($record->seq_foo, 2);
        $this->assertEquals($record->seq_bar, 3);
        
        /**
         * recover memory
         */
        $model->__destruct();
        unset($model);
    }


    
    /**
     * 
     * Test -- Counts the number of records in a related model for a given record.
     * 
     */
    public function testCountPagesRelated()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- 
     * 
     */
    public function testFetchRelatedArray()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Given a record, fetches a related record or collection for a named relationship.
     * 
     */
    public function testFetchRelatedObject()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns a new Solar_Sql_Select tool, with the proper SQL object injected automatically, and with eager "to-one" associations joined.
     * 
     */
    public function testNewSelect()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns a new Solar_Sql_Select tool for selecting related records.
     * 
     */
    public function testNewSelectRelated()
    {
        $this->todo('stub');
    }

    
    /**
     * 
     * Test -- Fetches a single value from the model (i.e., the first column of the  first record of the returned page set).
     * 
     */
    public function testFetchValue()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- 
     * 
     */
    public function testGetRelatedModel()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Serializes data values in-place based on $this->_serialize_cols.
     * 
     */
    public function testSerializeCols()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Unerializes data values in-place based on $this->_serialize_cols.
     * 
     */
    public function testUnserializeCols()
    {
        $this->todo('stub');
    }


}
