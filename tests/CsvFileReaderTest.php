<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Tests;

use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\CsvFileReader;
use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
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
        $linesObserver->expects($this->exactly(3))->method('__invoke');

        $dataObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $dataObserver->expects($this->exactly(3))->method('__invoke');

        $errorsObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $errorsObserver->expects($this->never())->method('__invoke');

        $streamObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $streamObserver->expects($this->exactly(3))->method('__invoke');

        $reader->lines()->subscribe($linesObserver);
        $reader->data()->subscribe($dataObserver);
        $reader->errors()->subscribe($errorsObserver);
        $reader->stream()->subscribe($streamObserver);

        $reader->loop();
    }

    public function testCsvFileReader_WithDataTransformer(): void
    {
        $file = new CsvFile('data://text/plain,'.\urlencode($this->csv));
        $reader = new CsvFileReader($file, function (array $data) {
            if (\in_array(null, \array_values($data), true)) {
                throw new \InvalidArgumentException('Data contains null');
            }

            return $data;
        });

        $linesObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $linesObserver->expects($this->exactly(2))->method('__invoke');

        $dataObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $dataObserver->expects($this->exactly(2))->method('__invoke');

        $errorsObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $errorsObserver->expects($this->once())->method('__invoke')->with($this->isInstanceOf(InvalidCsvLineException::class));

        $streamObserver = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $streamObserver->expects($this->exactly(3))->method('__invoke');

        $reader->lines()->subscribe($linesObserver);
        $reader->data()->subscribe($dataObserver);
        $reader->errors()->subscribe($errorsObserver);
        $reader->stream()->subscribe($streamObserver);

        $reader->loop();
    }
}
