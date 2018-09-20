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
        $this->file = $file;
        $this->subject = new Subject();
        $this->file->setInvalidLineHandler(function (InvalidCsvLineException $e): void {
            $this->subject->next($e);
        });
        $this->file->setDataTransformer($dataTransformer);
    }

    /**
     * @param int $skipLines
     *
     * @throws Exception\InvalidCsvHeaderException
     * @throws InvalidCsvLineException
     */
    public function loop(int $skipLines = 0): void
    {
        $skipped = 0;
        foreach ($this->file->getData() as $csvLine) {
            if ($skipLines > 0 && $skipped < $skipLines) {
                ++$skipped;
            } else {
                $this->subject->next($csvLine);
            }
        }

        $this->subject->complete();
    }

    /**
     * @throws \RuntimeException
     * @throws \LogicException
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
