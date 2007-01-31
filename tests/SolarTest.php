<?php

require_once dirname(__FILE__) . '/SolarUnitTest.config.php';
require_once 'Solar.php';

class SolarTest extends PHPUnit_Framework_TestCase
{
    public function testLoadClassThrowsExceptionOnEmptyString() {
        try {
            Solar::loadClass('');
            $this->fail('Should throw exception on empty string');
        }
        catch (Solar_Exception $e) {
            return;
        }
    }
    
    public function testLoadClassLoadsAnObjectOnKnownClass() {
        try {
            $this->assertFalse(
                class_exists('Solar_LoadClassObject'),
                'Insure class has not been loaded.'
            );
            
            Solar::loadClass('Solar_LoadClassObject');
            
            $this->assertTrue(
                class_exists('Solar_LoadClassObject'),
                'Insure Solar::loadClass() loaded the requested class.'
            );
        } catch (Exception $e) {
            echo $e;
            $this->fail('Threw exception while trying to load an object?');
        }
    }
    
    public function testFactoryLoadsAndReturnsObject() {
        $this->assertFalse(
            class_exists('Solar_FactoryObject'),
            'Insure class has not been loaded.'
        );
        
        $object = Solar::factory('Solar_FactoryObject');
            
        $this->assertTrue($object instanceof Solar_FactoryObject);
    }
    
    
}
