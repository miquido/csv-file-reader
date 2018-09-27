<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Exception;

use Miquido\CsvFileReader\Line\CsvLineInterface;

final class InvalidCsvLineException extends CsvFileReaderException
{
    /**
     * @var CsvLineInterface
     */
    private $csvLine;

    public function __construct(string $message, CsvLineInterface $csvLine, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->csvLine = $csvLine;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->csvLine->getData();
    }

    /**
     * @return int
     */
    public function getLineNumber(): int
    {
        return $this->csvLine->getLineNumber();
    }

    public function getCsvLine(): CsvLineInterface
    {
        return $this->csvLine;
    }
}
