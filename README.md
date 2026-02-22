# PHP LibreOffice wrapper for converting Word to PDF

Simple PHP package to convert Word files (`.doc`, `.docx`) to PDF using LibreOffice (`soffice`) in headless mode.

![LibreOffice](https://sm.pcmag.com/t/pcmag_au/review/l/libreoffic/libreoffice_q1be.3840.jpg)
![PHP Web Development](https://www.sectorlink.com/img/blog/php-web-development.jpg)

## Installation

```bash
composer require zahfouf/php-libreoffice
```

## CLI Commands

After installation, the CLI binary is available at:

```bash
vendor/bin/libreoffice
```

### `lo:install`

Installs LibreOffice automatically on Ubuntu/Debian.

```bash
vendor/bin/libreoffice lo:install
```

Notes:
- Windows: automatic installation is not supported (the command prints the official download URL).
- Non-Debian/Ubuntu Linux: install LibreOffice using your distribution package manager.

### `lo:probe`

Checks that the LibreOffice binary works and saves default values:
- binary path (`binary`)
- temporary directory (`temp_dir`)

```bash
vendor/bin/libreoffice lo:probe --binary="C:\Program Files\LibreOffice\program\soffice.exe" --temp-dir="C:\laragon\tmp"
```

These values are stored in `src/Config/global_options.php`.

### `lo:convert`

Converts a Word document to PDF.

```bash
vendor/bin/libreoffice lo:convert "C:\docs\invoice.docx" --to=pdf --out="C:\docs\out"
```

Useful options:
- `--binary`: path to `soffice`
- `--timeout`: timeout in seconds
- `--temp-dir`: temporary directory
- `--profile`: strategy (`none|per_job|per_worker|shared_mutex`)
- `--worker-id`: worker id (for `per_worker`)
- `--keep-temp`: keep temporary files on failure

## Usage example (code)

```php
<?php

use LibreOffice\LibreOffice;

$lo = new LibreOffice();

$result = $lo->convert('C:/docs/report.docx')->to('pdf', ['output_dir' => 'C:/docs/out']);

echo $result->outputPath . PHP_EOL;
echo $result->durationMs . PHP_EOL;

// Static usage (without instantiating):

$result = LibreOffice::make()->convert('C:/docs/report.docx')->to('pdf', ['output_dir' => 'C:/docs/out']);
```



## Test

```bash
composer test
```
