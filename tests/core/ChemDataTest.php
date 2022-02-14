<?php
require_once __DIR__ . '/../../core/ChemData.php';
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\any;
use function PHPUnit\Framework\anything;

class ChemDataTest extends TestCase
{
    public function test_getRecords() : void {
        $chemData = new core\ChemData();
        $actual = $chemData->getRecords(['filter', 'element'] , ['includeElements'=>'Na']);
        $expected = anything();
        $this->assertEquals($expected, $actual, (__LINE__ - 2) . ' failed miserably.');
    }
    public function test_getRecordId() :void {
        $chemData = new core\ChemData();
        $actual = $chemData->getRecordId('92e98172-541e-4ff4-bf98-8cea2c5edb7d');
        $expected = [];
        $this->assertEquals($expected, $actual, (__LINE__ - 2) . ' failed miserably.');
}}