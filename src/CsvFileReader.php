<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader;

use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Miquido\CsvFileReader\Line\CsvLineInterface;
use Miquido\Observable\ObservableInterface;
use Miquido\Observable\Operator;
use Miquido\Observable\Subject\Subject;

class CsvFileReader implements ObservableFileInterface
{
    /**
     * @var CsvFile
     */
    private $file;

    /**
     * @var Subject
     */
    private $subject;

    public function __construct(CsvFile $file, callable $dataTransformer = null)
    {
        if ($file->hasInvalidLineHandler()) {
            throw new \InvalidArgumentException('CsvFile already has invalid line handler');
        }
        if ($file->hasDataTransformer()) {
            throw new \InvalidArgumentException('CsvFile already has data transformer');
        }
        $this->file = $file;
        $this->subject = new Subject();
        $this->file->setInvalidLineHandler(function (InvalidCsvLineException $e): void {
            $this->subject->next($e);
        });
        $this->file->setDataTransformer($dataTransformer);
    }

    /**
     * @throws InvalidCsvLineException
     */
    public function loop(): void
    {
        foreach ($this->file->getData() as $csvLine) {
            $this->subject->next($csvLine);
        }

        $this->subject->complete();
    }

    /**
     * @throws InvalidCsvLineException
     *
     * @return int
     */
    public function count(): int
    {
        return $this->file->count();
    }

    public function lines(): ObservableInterface
    {
        return $this->subject->pipe(new Operator\Filter(function ($data): bool {
            return $data instanceof CsvLineInterface;
        }));
    }

    public function data(): ObservableInterface
    {
        return $this->lines()->pipe(new Operator\Map(function (CsvLineInterface $line) {
            return $line->getData();
        }));
    }

    public function errors(): ObservableInterface
    {
        return $this->subject->pipe(new Operator\Filter(function ($data): bool {
            return $data instanceof InvalidCsvLineException;
        }));
    }

    public function stream(): ObservableInterface
    {
        return $this->subject->asObservable();
    }
}
