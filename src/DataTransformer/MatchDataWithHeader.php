<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\DataTransformer;

use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Miquido\CsvFileReader\Line\CsvLine;
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
        Assert::notEmpty($header, 'Invalid header line.');
        Assert::allString(\array_values($header), 'Invalid header line.');
        Assert::allInteger(\array_keys($header), 'Invalid header line.');

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
    public function match($data, int $lineNumber): array
    {
        $data = $data ?? [];
        Assert::isArray($data, \sprintf('Invalid data at line %s', $lineNumber));
        $result = [];

        if (\count($data) > $this->count) {
            throw new InvalidCsvLineException(
                \sprintf('Data row has more data than a header (%s in a data, %s in a header), line number: %s', \count($data), $this->count, $lineNumber),
                new CsvLine($lineNumber, $data)
            );
        }

        foreach ($this->header as $colNumber => $colName) {
            $result[$colName] = $data[$colNumber] ?? null;
        }

        return $result;
    }
}
