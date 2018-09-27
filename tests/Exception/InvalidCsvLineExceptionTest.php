<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Tests\Exception;

use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Miquido\CsvFileReader\Line\CsvLine;
use PHPUnit\Framework\TestCase;

final class InvalidCsvLineExceptionTest extends TestCase
{
    public function testGetters(): void
    {
        $csvLine = new CsvLine(100, $data = ['a' => 1, 'b' => 2]);
        $e = new InvalidCsvLineException('message', $csvLine);

        $this->assertSame('message', $e->getMessage());
        $this->assertSame($data, $e->getData());
        $this->assertSame(100, $e->getLineNumber());
        $this->assertSame($csvLine, $e->getCsvLine());
    }
}
