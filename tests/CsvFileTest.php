<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Tests;

use Miquido\CsvFileReader\CsvControl;
use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Miquido\CsvFileReader\Line\CsvLineInterface;
use PHPUnit\Framework\TestCase;

class CsvFileTest extends TestCase
{
    public function testCsvFile_WithHeader(): void
    {
        $csv = <<<CSV
name,surname,email
John,"Smith",john@smith.com
Jan,Kowalski,"jan@kowaslki.pl"
CSV;
        $file = new CsvFile('data://text/plain,'.\urlencode($csv), true);
        $users = $file->readLines();

        $this->assertSame(3, $file->countLines());
        $this->assertTrue(\is_iterable($users));
        $this->assertInstanceOf(\Traversable::class, $users);

        /** @var CsvLineInterface[] $users */
        $users = \iterator_to_array($users); // get all data
        $this->assertCount(2, $users);
        $this->assertContainsOnlyInstancesOf(CsvLineInterface::class, $users);

        $firstUserLine = $users[0];
        $this->assertSame(2, $firstUserLine->getLineNumber());
        $this->assertSame('John', $firstUserLine->getData()['name']);
        $this->assertSame('Smith', $firstUserLine->getData()['surname']);
        $this->assertSame('john@smith.com', $firstUserLine->getData()['email']);

        $secondUserLine = $users[1];
        $this->assertSame(3, $secondUserLine->getLineNumber());
        $this->assertSame('Jan', $secondUserLine->getData()['name']);
        $this->assertSame('Kowalski', $secondUserLine->getData()['surname']);
        $this->assertSame('jan@kowaslki.pl', $secondUserLine->getData()['email']);
    }

    public function testCsvFile_WithoutHeader_CustomCsvControl(): void
    {
        $csv = <<<CSV
1;'test';'some value'
2;age;55
CSV;
        $file = new CsvFile('data://text/plain,'.\urlencode($csv), false, new CsvControl(';', "'"));
        $data = $file->readLines();

        $this->assertSame(2, $file->countLines());
        $this->assertTrue(\is_iterable($data));
        $this->assertInstanceOf(\Traversable::class, $data);

        /** @var CsvLineInterface[] $data */
        $data = \iterator_to_array($data); // get all data
        $this->assertCount(2, $data); // empty lines should be skipped
        $this->assertContainsOnlyInstancesOf(CsvLineInterface::class, $data);

        $firstItem = $data[0];
        $this->assertSame(1, $firstItem->getLineNumber());
        $this->assertSame('1', $firstItem->getData()[0]);
        $this->assertSame('test', $firstItem->getData()[1]);
        $this->assertSame('some value', $firstItem->getData()[2]);

        $secondItem = $data[1];
        $this->assertSame(2, $secondItem->getLineNumber());
        $this->assertSame('2', $secondItem->getData()[0]);
        $this->assertSame('age', $secondItem->getData()[1]);
        $this->assertSame('55', $secondItem->getData()[2]);
    }

    public function testCsvFile_WithInvalidLines(): void
    {
        $csv = <<<CSV
name,surname,email
John,"Smith",john@smith.com,extra
Jan,Kowalski,"jan@kowaslki.pl"
CSV;

        $file = new CsvFile('data://text/plain,'.\urlencode($csv));

        $this->expectException(InvalidCsvLineException::class);
        $this->expectExceptionMessage('Data row has more data than a header (4 in a data, 3 in a header), line number: 2');
        \iterator_to_array($file->readLines());
    }


}
