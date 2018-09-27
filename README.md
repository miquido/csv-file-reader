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

### Simple read a csv file example
```php
<?php

use Miquido\CsvFileReader\CsvFile;

// open a file
$csv = new CsvFile('./examples/users.csv');

$count = $csv->countLines(); // 101

// read a file line-by-line 
foreach ($csv->readLines() as $line) {
    $line->getLineNumber(); // 2 ... 101
    $line->getData(); // ['id' => '1', 'name' => 'Wallace', 'surname' => 'Smith'] ...
}
```

### 

## Contributing

Pull requests, bug fixes and issue reports are welcome.
Before proposing a change, please discuss your change by raising an issue.

