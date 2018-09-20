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
        $users = $file->getData();

        $this->assertSame(2, $file->count());
        $this->assertFalse($file->hasInvalidLineHandler());
        $this->assertFalse($file->hasDataTransformer());
        $this->assertTrue(\is_iterable($users));
        $this->assertInstanceOf(\Traversable::class, $users);

        /** @var CsvLineInterface[] $users */
        $users = \iterator_to_array($users); // get all data
        $this->assertCount(2, $users); // empty lines should be skipped
        $this->assertContainsOnlyInstancesOf(CsvLineInterface::class, $users);

        $firstUserLine = $users[0];
        $this->assertSame(1, $firstUserLine->getLineNumber());
        $this->assertSame('John', $firstUserLine->getData()['name']);
        $this->assertSame('Smith', $firstUserLine->getData()['surname']);
        $this->assertSame('john@smith.com', $firstUserLine->getData()['email']);

        $secondUserLine = $users[1];
        $this->assertSame(2, $secondUserLine->getLineNumber());
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
        $data = $file->getData();

        $this->assertSame(2, $file->count());
        $this->assertFalse($file->hasInvalidLineHandler());
        $this->assertFalse($file->hasDataTransformer());
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

    public function testDataTransformer(): void
    {
        $csv = <<<CSV
name,surname
John,Smith
Jan,Kowalski
CSV;

        $file = new CsvFile('data://text/plain,'.\urlencode($csv));
        $file->setDataTransformer(function (array $data) {
            return \sprintf('%s %s', $data['name'], $data['surname']);
        });

        /** @var CsvLineInterface[] $data */
        $data = \iterator_to_array($file->getData());
        $this->assertSame(2, $file->count());
        $this->assertCount(2, $data); // empty lines should be skipped
        $this->assertContainsOnlyInstancesOf(CsvLineInterface::class, $data);

        $this->assertSame('John Smith', $data[0]->getData());
        $this->assertSame('Jan Kowalski', $data[1]->getData());
    }

    public function testDataTransformerWithInvalidLineHandler(): void
    {
        $csv = <<<CSV
name,surname,age
JOHN,GABRIEL,140
JOHN,SMITH,40
JAN,KOWALSKI,45
JAN,SMITH,180
SMITH
CSV;

        $file = new CsvFile('data://text/plain,'.\urlencode($csv));
        $file->setDataTransformer(function (array $data) {
            $age = (int) $data['age'];
            if ($age > 100) {
                throw new \Exception('Invalid age');
            }

            return [
                'firstName' => \ucfirst(\mb_strtolower($data['name'])),
                'lastName' => \ucfirst(\mb_strtolower($data['surname'])),
                'age' => $age,
            ];
        });
        $invalidLineHandlerMock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $invalidLineHandlerMock->expects($this->exactly(3))->method('__invoke');
        $file->setInvalidLineHandler($invalidLineHandlerMock);

        $users = $file->getData();

        $this->assertSame(2, $file->count());
        $this->assertTrue($file->hasInvalidLineHandler());
        $this->assertTrue($file->hasDataTransformer());
        $this->assertTrue(\is_iterable($users));
        $this->assertInstanceOf(\Traversable::class, $users);

        /** @var CsvLineInterface[] $users */
        $users = \iterator_to_array($users); // get all datavar_dump($users);``
        $this->assertCount(2, $users); // empty lines should be skipped
        $this->assertContainsOnlyInstancesOf(CsvLineInterface::class, $users);

        $firstUserLine = $users[0];
        $this->assertSame(2, $firstUserLine->getLineNumber());
        $this->assertSame('John', $firstUserLine->getData()['firstName']);
        $this->assertSame('Smith', $firstUserLine->getData()['lastName']);
        $this->assertSame(40, $firstUserLine->getData()['age']);

        $secondUserLine = $users[1];
        $this->assertSame(3, $secondUserLine->getLineNumber());
        $this->assertSame('Jan', $secondUserLine->getData()['firstName']);
        $this->assertSame('Kowalski', $secondUserLine->getData()['lastName']);
        $this->assertSame(45, $secondUserLine->getData()['age']);
    }

    public function testDataTransformerWithoutInvalidLineHandler(): void
    {
        $csv = <<<CSV
name,surname,age
JOHN,GABRIEL,140
JOHN,SMITH,40
JAN,KOWALSKI,45
CSV;

        $file = new CsvFile('data://text/plain,'.\urlencode($csv));
        $file->setDataTransformer(function (array $data) {
            $age = (int) $data['age'];
            if ($age > 100) {
                throw new \Exception('Invalid age');
            }

            return $data;
        });

        $this->assertFalse($file->hasInvalidLineHandler());
        $this->assertTrue($file->hasDataTransformer());
        $this->expectException(InvalidCsvLineException::class);
        $this->expectExceptionMessage('Invalid age');
        \iterator_to_array($file->getData());
    }
}
