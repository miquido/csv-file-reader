<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\DataTransformer;

use Webmozart\Assert\Assert;

final class MatchDataWithHeader
{
    /**
     * @var array
     */
    private $header;

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
    }

    /**
     * @param array $data
     * @param int   $lineNumber
     *
     * @return array
     */
    public function match($data, int $lineNumber): array
    {
        $data = $data ?? [];
        Assert::isArray($data, \sprintf('Invalid data at line %s', $lineNumber));
        $result = [];

        foreach ($this->header as $colNumber => $colName) {
            $result[$colName] = $data[$colNumber] ?? null;
        }

        return $result;
    }
}
