<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader;

use Miquido\CsvFileReader\DataTransformer\MatchDataWithHeader;
use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Miquido\CsvFileReader\Line\CsvLine;
use Miquido\CsvFileReader\Line\CsvLineInterface;
use SplFileObject as FileObject;
use Webmozart\Assert\Assert;

final class CsvFile
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var bool
     */
    private $firstLineHeader;

    /**
     * @var CsvControl|null
     */
    private $csvControl;

    /**
     * @var callable|null
     */
    private $invalidLineHandler;

    public function __construct(string $filePath, bool $firstLineHeader = true, CsvControl $csvControl = null)
    {
        $this->filePath = $filePath;
        $this->firstLineHeader = $firstLineHeader;
        $this->csvControl = $csvControl;
    }

    public function setInvalidLineHandler(callable $callback): void
    {
        $this->invalidLineHandler = $callback;
    }

    public function hasInvalidLineHandler(): bool
    {
        return \is_callable($this->invalidLineHandler);
    }

    /**
     * @param InvalidCsvLineException $e
     *
     * @throws InvalidCsvLineException
     */
    private function handleInvalidLineException(InvalidCsvLineException $e): void
    {
        if (!\is_callable($this->invalidLineHandler)) {
            throw $e;
        }
        \call_user_func($this->invalidLineHandler, $e);
    }

    /**
     * @return int
     */
    public function countLines(): int
    {
        $count = 0;
        $file = $this->openFile();
        while (!$file->eof()) {
            ++$count;
            $file->fgetcsv();
        }

        return $count;
    }

    /**
     * @throws InvalidCsvLineException
     *
     * @return iterable|CsvLineInterface[]
     */
    public function readLines(): iterable
    {
        $file = $this->openFile();

        $lineNumber = 0;
        $dataProxy = null;
        if ($this->firstLineHeader) {
            ++$lineNumber;
            $header = $file->fgetcsv();

            Assert::isArray($header);
            $dataProxy = new MatchDataWithHeader((array) $header); // make phpstan happy
        }

        while (!$file->eof()) {
            ++$lineNumber;
            $data = (array) $file->fgetcsv();

            try {
                yield new CsvLine(
                    $lineNumber,
                    $dataProxy ? $dataProxy->match($data, $lineNumber) : $data
                );
            } catch (InvalidCsvLineException $e) {
                $this->handleInvalidLineException($e);
            }
        }

        $file = null; // close file
    }

    /**
     * @throws \RuntimeException
     * @throws \LogicException
     *
     * @return FileObject
     */
    private function openFile(): FileObject
    {
        $file = new FileObject($this->filePath, 'r');
        $file->setFlags(FileObject::READ_CSV | FileObject::READ_AHEAD | FileObject::DROP_NEW_LINE);
        if ($this->csvControl instanceof CsvControl) {
            $file->setCsvControl(
                $this->csvControl->getDelimiter(),
                $this->csvControl->getEnclosure(),
                $this->csvControl->getEscape()
            );
        }

        return $file;
    }
}
