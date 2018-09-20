<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Tests\Line;

use Miquido\CsvFileReader\Line\CsvLine;
use PHPUnit\Framework\TestCase;

class CsvLineTest extends TestCase
{
    public function testCsvLineValidInput_AssocArray(): void
    {
        $data = ['col1' => 'value1', 'col2' => 'value2'];
        $csvLine = new CsvLine(123, $data);

        $this->assertEquals(123, $csvLine->getLineNumber());
        $this->assertSame($data, $csvLine->getData());

        $this->assertEquals('value1', $csvLine->getData()['col1']);
        $this->assertEquals('value2', $csvLine->getData()['col2']);
    }

    public function testCsvLineValidInput_NormalArray(): void
    {
        $data = ['value1', 'value2'];
        $csvLine = new CsvLine(1000, $data);

        $this->assertEquals(1000, $csvLine->getLineNumber());
        $this->assertSame($data, $csvLine->getData());

        $this->assertEquals('value1', $csvLine->getData()[0]);
        $this->assertEquals('value2', $csvLine->getData()[1]);
    }
}
