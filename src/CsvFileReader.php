<?php

declare(strict_types=1);

namespace Miquido\CsvFileReader;

use Miquido\CsvFileReader\Exception\InvalidCsvLineException;
use Miquido\CsvFileReader\Line\CsvLine;
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

    /**
     * @var callable|null
     */
    private $dataTransformer;

    public function __construct(CsvFile $file, callable $dataTransformer = null)
    {
        $this->subject = new Subject();
        $this->file = $file;
        $this->dataTransformer = $dataTransformer;
    }

    public function loop(): void
    {
        foreach ($this->file->readLines() as $csvLine) {
            try {
                $this->subject->next($this->transformLine($csvLine));
            } catch (InvalidCsvLineException $e) {
                $this->subject->next($e);
            }
        }

        $this->subject->complete();
    }

    /**
     * @param CsvLineInterface $line
     *
     * @throws InvalidCsvLineException
     *
     * @return CsvLineInterface
     */
    private function transformLine(CsvLineInterface $line): CsvLineInterface
    {
        if (\is_callable($this->dataTransformer)) {
            try {
                return new CsvLine(
                    $line->getLineNumber(),
                    \call_user_func($this->dataTransformer, $line->getData(), $line->getLineNumber())
                );
            } catch (\Exception $e) {
                throw new InvalidCsvLineException($e->getMessage(), $line, $e);
            }
        }

        return $line;
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
