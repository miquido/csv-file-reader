[![Build](https://travis-ci.org/miquido/csv-file-reader.svg?branch=master)](https://travis-ci.org/miquido/observable)
[![Maintainability](https://api.codeclimate.com/v1/badges/d7b1addb4a14eab9cb48/maintainability)](https://codeclimate.com/github/miquido/csv-file-reader/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/d7b1addb4a14eab9cb48/test_coverage)](https://codeclimate.com/github/miquido/csv-file-reader/test_coverage)
[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)

# CSV File Reader

Set of classes for data streams.

- [Installation guide](#installation)
- [Code Samples](#code-samples)
- [Contributing](#contributing)

## Installation 
Use [Composer](https://getcomposer.org) to install the package:

```shell
composer require miquido/csv-file-reader
```

## Code Samples
- [Simple read a csv file example](#simple-read-a-csv-file-example)
- [Process a file as a stream](#process-a-file-as-a-stream)
- [Using data transformer and error handler](#using-data-transformer)
- [Batch processing](#batch-processing)

### Simple read a csv file example
```php
<?php

use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\Line\CsvLineInterface;

// open a file
$csv = new CsvFile('./examples/users.csv');

$count = $csv->countLines(); // 101

// don't worry about memory, it reads a file line-by-line 
foreach ($csv->readLines() as $line) {
    /** @var CsvLineInterface $line */
    $line->getLineNumber(); // 2 ... 101
    $line->getData(); // ['id' => '1', 'name' => 'Miriam', 'surname' => 'Mccoy', 'age' => '79'] ...
}
```

### Process a file as a stream
*Miquido\CsvFileReader\CsvFileReader* class uses [miquido/observable](https://github.com/miquido/observable) library for data processing.   
```php
<?php

use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\CsvFileReader;
use Miquido\CsvFileReader\Line\CsvLineInterface;

$reader = new CsvFileReader(new CsvFile('./examples/users.csv'));
$reader->lines()->subscribe(function (CsvLineInterface $line) {
    // do something with a line
    $line->getLineNumber(); // 2 ... 101
    $line->getData(); // ['id' => '1', 'name' => 'Miriam', 'surname' => 'Mccoy', 'age' => '79'] ...
});

$reader->loop(); // start reading a file
```

### Using data transformer and error handler
Please check [miquido/data-structure](https://github.com/miquido/data-structure) library for more details about classes used in examples below. 
```php
<?php

use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\CsvFileReader;
use Miquido\CsvFileReader\Line\CsvLineInterface;
use Miquido\DataStructure\Map\MapInterface;
use Miquido\DataStructure\Map\Map;

// change data to Map object
$transformer = function (array $data, int $line): MapInterface {
    return new Map($data);
};

$reader = new CsvFileReader(new CsvFile('./examples/users.csv'), $transformer);
$reader->lines()->subscribe(function (CsvLineInterface $line): void {
    $line->getData(); // getData() now returns Map() object
});

$reader->loop(); // start reading a file

```

If transformer throws an error, it will appear in $reader->errors() stream

```php
<?php

use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\CsvFileReader;
use Miquido\CsvFileReader\Exception\InvalidCsvLineException;

// check user's age
$transformer = function (array $data, int $line): array {
    $age = (int) $data['age'];
    if ($age < 18) {
        throw new \Exception('Invalid age');
    }
    
    return $data;
};

$reader = new CsvFileReader(new CsvFile('./examples/users.csv'), $transformer);
$reader->data()->subscribe(function (array $lineData): void {
    // do something with data
});
$reader->errors()->subscribe(function (InvalidCsvLineException $e): void {
    // do something with an error
    $e->getMessage();
    $e->getCsvLine();
});

$reader->loop(); // start reading a file
``` 

### Batch processing
Simply use *Miquido\Observable\Operator*:
```php
<?php

use Miquido\CsvFileReader\CsvFile;
use Miquido\CsvFileReader\CsvFileReader;
use Miquido\Observable\Operator;

$batchSize = 10;

$reader = new CsvFileReader(new CsvFile('./examples/users.csv'));
$reader->lines()->pipe(new Operator\BufferCount($batchSize))->subscribe(function (array $lines): void {
    // do something with 10 lines
});

$reader->loop(); // start reading a file
```

## Contributing

Pull requests, bug fixes and issue reports are welcome.
Before proposing a change, please discuss your change by raising an issue.

