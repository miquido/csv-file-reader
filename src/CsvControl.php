<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader;

use Webmozart\Assert\Assert;

final class CsvControl
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;

    /**
     * @var string
     */
    private $escape;

    public function __construct(string $delimiter = ',', string $enclosure = '"', string $escape = '\\')
    {
        Assert::length($delimiter, 1);
        Assert::length($enclosure, 1);
        Assert::length($escape, 1);

        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @return string
     */
    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * @return string
     */
    public function getEscape(): string
    {
        return $this->escape;
    }
}
