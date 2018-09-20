<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Exception;

final class InvalidCsvLineException extends CsvFileReaderException
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $lineNumber;

    public function __construct(string $message, array $data, int $lineNumber, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->data = $data;
        $this->lineNumber = $lineNumber;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }
}
