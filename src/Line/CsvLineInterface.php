<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader\Line;

interface CsvLineInterface
{
    public function getLineNumber(): int;
    public function getData();
}