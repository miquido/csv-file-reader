<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\DataTransformer;

use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Webmozart\Assert\Assert;

final class MatchDataWithHeader
{
    /**
     * @var array
     */
    private $header;

    /**
     * @var int
     */
    private $count;

    /**
     * MatchDataWithHeader constructor.
     *
     * @param array $header
     */
    public function __construct(array $header)
    {
        Assert::allString($header);
        Assert::allInteger(\array_keys($header));

        $this->header = $header;
        $this->count = \count($header);
    }

    /**
     * @param array $data
     * @param int   $lineNumber
     *
     * @throws InvalidCsvLineException
     *
     * @return array
     */
    public function match(array $data, int $lineNumber): array
    {
        $result = [];

        if (\count($data) !== $this->count) {
            throw new InvalidCsvLineException(
                \sprintf('Data row has different length than a header (%s vs %s), line number: %s', \count($data), $this->count, $lineNumber),
                $data,
                $lineNumber
            );
        }

        foreach ($this->header as $colNumber => $colName) {
            if (!isset($data[$colNumber])) {
                throw new InvalidCsvLineException(
                    \sprintf('Array does not have index %s (column: %s), line number: %s', $colNumber, $colName, $lineNumber),
                    $data,
                    $lineNumber
                );
            }

            $result[$colName] = $data[$colNumber];
        }

        return $result;
    }
}
