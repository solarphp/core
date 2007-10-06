<?php
class Solar_Model_Nodes_Revisions_Record extends Solar_Sql_Model_Record {
    protected function __getId()
    {
        echo "I am the ID!";
        return $this->_data['id'];
    }
}
