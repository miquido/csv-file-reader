<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader;

use Miquido\Observable\ObservableInterface;

interface ObservableFileInterface extends \Countable
{
    public function stream(): ObservableInterface;

    public function lines(): ObservableInterface;

    public function data(): ObservableInterface;

    public function errors(): ObservableInterface;

    public function loop(): void;
}
