<?php

require_once dirname(__FILE__) . '/SolarUnitTest.config.php';
require_once 'Solar.php';

class SolarTest extends PHPUnit_Framework_TestCase
{
    public function testLoadClassThrowsExceptionOnEmptyString() {
        try {
            Solar::autoload('');
            $this->fail('Should throw exception on empty string');
        }
        catch (Solar_Exception $e) {
            return;
        }
    }
    
    public function testLoadClassLoadsAnObjectOnKnownClass() {
        try {
            $this->assertFalse(
                class_exists('Solar_LoadClassObject', false),
                'Insure class has not been loaded.'
            );
            
            Solar::autoload('Solar_LoadClassObject');
            
            $this->assertTrue(
                class_exists('Solar_LoadClassObject', false),
                'Insure Solar::autoload() loaded the requested class.'
            );
        } catch (Exception $e) {
            echo $e;
            $this->fail('Threw exception while trying to load an object?');
        }
    }
    
    public function testFactoryLoadsAndReturnsObject() {
        $this->assertFalse(
            class_exists('Solar_FactoryObject', false),
            'Insure class has not been loaded.'
        );
        
        $object = Solar::factory('Solar_FactoryObject');
            
        $this->assertTrue($object instanceof Solar_FactoryObject);
    }
    
    
}
