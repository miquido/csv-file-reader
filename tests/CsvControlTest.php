<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Tests;

use Miquido\CsvFileReader\CsvControl;
use PHPUnit\Framework\TestCase;

final class CsvControlTest extends TestCase
{
    public function testCreateCsvControlWithDefaults(): void
    {
        $csvControl = new CsvControl();

        $this->assertEquals(',', $csvControl->getDelimiter());
        $this->assertEquals('"', $csvControl->getEnclosure());
        $this->assertEquals('\\', $csvControl->getEscape());
    }

    public function testCreateCsvControlWithCustomProperties(): void
    {
        $csvControl = new CsvControl(';', '\'', '/');
        $this->assertEquals(';', $csvControl->getDelimiter());
        $this->assertEquals("'", $csvControl->getEnclosure());
        $this->assertEquals('/', $csvControl->getEscape());
    }

    public function testCreateCsvControlWithInvalidArgument1(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CsvControl(';;');
    }

    public function testCreateCsvControlWithInvalidArgument2(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CsvControl(',', "''");
    }

    public function testCreateCsvControlWithInvalidArgument3(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CsvControl(';', '\'', '///');
    }
}
