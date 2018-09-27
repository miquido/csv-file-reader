<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Line;

final class CsvLine implements CsvLineInterface
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @param int   $lineNumber
     * @param mixed $data
     */
    public function __construct(int $lineNumber, $data)
    {
        $this->lineNumber = $lineNumber;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function getData()
    {
        return $this->data;
    }
}
