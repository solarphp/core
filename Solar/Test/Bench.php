<?php
/**
 * 
 * Class for benchmarking the speed of different methods.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Test.php 1464 2006-07-11 04:29:35Z pmjones $
 * 
 */

/**
 * 
 * Class for benchmarking the speed of different methods.
 * 
 * Benchmark methods are prefixed with "bench" and are automatically
 * run $loops number of times when you call Solar_Test_Bench::run($loops).
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Bench extends Solar_Base {
    
    /**
     * 
     * User-defined configuration.
     * 
     * Keys are:
     * 
     * : \\loops\\ : (int) The number of times the benchmarking methods
     *   should be run; default 1000.
     * 
     * @var array
     * 
     */
    protected $_Solar_Test_Bench = array(
        'loops'   => 1000,
    );
    
    /**
     * 
     * Executes this code before running any benchmarks.
     * 
     * @reutrn void
     * 
     */
    public function setup()
    {
    }
    
    /**
     * 
     * Executes this code after running all benchmarks.
     * 
     * @reutrn void
     * 
     */
    public function teardown()
    {
    }
    
    /**
     * 
     * Runs all the benchmark methods in this class.
     * 
     * @param int $loops Run benchmark methods this number of times.
     * 
     * @return string The Solar_Debug_Timer profile table.
     * 
     */
    public function run($loops = null)
    {
        if (empty($loops)) {
            $loops = $this->_config['loops'];
        }
        
        // get the list of bench*() methods
        $reflect = new ReflectionClass($this);
        $bench = array();
        $methods = $reflect->getMethods();
        foreach ($methods as $method) {
            $name = $method->getName();
            if (substr($name, 0, 5) == 'bench') {
                $bench[] = $name;
            }
        }
        
        // get a timer object
        $timer = Solar::factory(
            'Solar_Debug_Timer',
            array('auto_start' => false)
        );
        
        // pre-run
        $this->setup();
        
        // start timing
        $timer->start();
        
        // run each benchmark method...
        foreach ($bench as $method) {
            // ... multiple times.
            for ($i = 0; $i < $loops; ++$i) {
                $this->$method();
            }
            // how long did the method run take?
            $timer->mark($method);
        }
        
        // stop timing
        $timer->stop();
        
        // post-run
        $this->teardown();
        
        // done!
        return $timer->fetch();
    }
}

?>