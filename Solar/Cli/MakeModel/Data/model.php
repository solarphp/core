/**
 * 
 * Model class.
 * 
 */
class {:class} extends {:extends}
{
    /**
     * 
     * Establish state of this object prior to _setup().
     * 
     * @return void
     * 
     */
    protected function _preSetup()
    {
        // chain to parent
        parent::_preSetup();
        
        // use setup files generated from make-model
        $setup_dir         = Solar_Class::dir(__CLASS__, 'Setup');
        $this->_table_name = Solar_File::load("{$setup_dir}/table_name.php");
        $this->_table_cols = Solar_File::load("{$setup_dir}/table_cols.php");
        $this->_index      = Solar_File::load("{$setup_dir}/index_info.php");
    }
    
    /**
     * 
     * Model-specific setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // chain to parent
        parent::_setup();
    }
}
