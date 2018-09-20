<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Tests;

use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\CsvFileReader;
use PHPUnit\Framework\TestCase;

final class CsvFileReaderTest extends TestCase
{
    private $csv = <<<CSV
name,surname
John,Smith
John
Jan,Kowalski
CSV;

    public function testCsvFileReader(): void
    {
        $file = new CsvFile('data://text/plain,'.\urlencode($this->csv));
        $reader = new CsvFileReader($file);

        $linesObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $linesObserver->expects($this->exactly(2))->method('__invoke');

        $dataObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $dataObserver->expects($this->exactly(2))->method('__invoke');

        $errorsObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $errorsObserver->expects($this->exactly(1))->method('__invoke');

        $streamObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $streamObserver->expects($this->exactly(3))->method('__invoke');

        $reader->lines()->subscribe($linesObserver);
        $reader->data()->subscribe($dataObserver);
        $reader->errors()->subscribe($errorsObserver);
        $reader->stream()->subscribe($streamObserver);

        $this->assertCount(2, $reader);
        $reader->loop();
    }

    public function testCsvFileReaderConstructor_HasInvalidLineHandler(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CsvFile already has invalid line handler');

        $file = new CsvFile('data://text/plain,'.\urlencode($this->csv));
        $file->setInvalidLineHandler(function (): void {});
        new CsvFileReader($file);
    }

    public function testCsvFileReaderConstructor_HasDataTransformer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CsvFile already has data transformer');

        $file = new CsvFile('data://text/plain,'.\urlencode($this->csv));
        $file->setDataTransformer(function ($data) { return $data; });
        new CsvFileReader($file);
    }
}
