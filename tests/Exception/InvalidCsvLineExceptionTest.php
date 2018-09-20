<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Tests\Exception;

use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use PHPUnit\Framework\TestCase;

final class InvalidCsvLineExceptionTest extends TestCase
{
    public function testGetters(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $e = new InvalidCsvLineException('message', $data, 100);

        $this->assertSame('message', $e->getMessage());
        $this->assertSame($data, $e->getData());
        $this->assertSame(100, $e->getLineNumber());
    }
}
