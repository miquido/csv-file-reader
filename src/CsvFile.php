<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader;

use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Miquido\CsvFileReader\Exception\InvalidCsvHeaderException;
use Miquido\CsvFileReader\DataTransformer\MatchDataWithHeader;
use Miquido\CsvFileReader\Line\CsvLine;
use \SplFileObject as FileObject;


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
     * @var CsvControl
     */
    private $csvControl;

    /**
     * @var callable|null
     */
    private $invalidLineHandler;

    /**
     * @var callable|null
     */
    private $dataTransformer;

    public function __construct(string $filePath, bool $firstLineHeader = true, CsvControl $csvControl = null)
    {
        $this->filePath = $filePath;
        $this->firstLineHeader = $firstLineHeader;
        $this->csvControl = $csvControl;
    }

    public function setInvalidLineHandler(callable $handler = null): void
    {
        $this->invalidLineHandler = $handler;
    }

    public function setDataTransformer(callable $transformer = null): void
    {
        $this->dataTransformer = $transformer;
    }

    /**
     * @param InvalidCsvLineException $e
     * @throws InvalidCsvLineException
     */
    private function handleInvalidLineException(InvalidCsvLineException $e): void
    {
        if (\is_callable($this->invalidLineHandler)) {
            \call_user_func($this->invalidLineHandler, $e);
        } else {
            throw $e;
        }
    }

    /**
     * @param array $data
     * @param int $lineNumber
     * @return array|mixed
     * @throws InvalidCsvLineException
     */
    private function transformData(array $data, int $lineNumber)
    {
        if (\is_callable($this->dataTransformer)) {
            try {
                return \call_user_func($this->dataTransformer, $data, $lineNumber);
            }
            catch (\Exception $e) {
                throw new InvalidCsvLineException($e->getMessage(), $data, $lineNumber);
            }
        }

        return $data;
    }

    /**
     * @return int
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function count(): int
    {
        // not the most efficient way, but we need to have the same result as getData()
        $file = $this->openFile();
        $count = 0;
        while (!$file->eof()) {
            $data = $file->fgetcsv();
            if (!\is_array($data)) {
                continue; // skip empty lines or last enter
            }

            ++$count;
        }

        $file = null; // close file

        return $this->firstLineHeader ? $count - 1 : $count;
    }

    /**
     * @return iterable
     * @throws InvalidCsvHeaderException
     * @throws InvalidCsvLineException
     */
    public function getData(): iterable
    {
        $file = $this->openFile();

        $dataProxy = null;
        if ($this->firstLineHeader) {
            $header = $file->fgetcsv();
            if (!\is_array($header)) {
                throw new InvalidCsvHeaderException('Invalid header of the file');
            }
            $dataProxy = new MatchDataWithHeader($header);
        }

        $lineNumber = 0;
        while (!$file->eof()) {
            $data = $file->fgetcsv();
            if (!\is_array($data)) {
                continue; // skip empty lines or last enter
            }

            try {
                yield new CsvLine(
                    ++$lineNumber,
                    $this->transformData(
                        $dataProxy ? $dataProxy->match($data, $lineNumber) : $data,
                        $lineNumber
                    )
                );
            }
            catch (InvalidCsvLineException $e) {
                $this->handleInvalidLineException($e);
            }
        }

        $file = null; // close file
    }

    /**
     * @return FileObject
     * @throws \RuntimeException
     * @throws \LogicException
     */
    private function openFile(): FileObject
    {
        $file = new FileObject($this->filePath, 'r');
        $file->setFlags(FileObject::READ_CSV | FileObject::READ_AHEAD | FileObject::SKIP_EMPTY | FileObject::DROP_NEW_LINE);
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
