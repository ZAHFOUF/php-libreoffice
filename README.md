# zahfouf/php-libreoffice

Framework-agnostic PHP library to convert Office documents (DOC/DOCX initially) to PDF using LibreOffice headless CLI (`soffice`).

## Install

```bash
composer require zahfouf/php-libreoffice
```

## Basic Usage

### Fluent API

```php
<?php

use LibreOffice\LibreOffice;

$lo = new LibreOffice([
    'binary' => 'soffice',
]);

$result = $lo->convert('/absolute/path/report.docx')->to('pdf');
```

### Static convenience API

```php
<?php

use LibreOffice\LibreOffice;

$result = LibreOffice::make([
    'timeout' => 180,
])->convert('/absolute/path/report.doc')->to('pdf');
```

`ConvertResult` includes:

- `outputPath` (absolute path)
- `inputPath` (absolute path)
- `durationMs`
- `stdout`
- `stderr`
- `command` (argv array used to run `soffice`)

## Options

| Option | Type | Default | Notes |
|---|---|---|---|
| `binary` | string | `soffice` | Binary path or executable name in PATH |
| `timeout` | int | `120` | Process timeout (seconds) |
| `profile_strategy` | string | Linux: `per_job`, Windows: `none` | `none`, `per_job`, `per_worker`, `shared_mutex` |
| `temp_dir` | string | `sys_get_temp_dir()` | Must be writable |
| `cleanup_policy` | string | `always` | `always`, `keep_on_failure` |
| `worker_id` | string\|null | `null` | Used with `per_worker` (defaults to PID if omitted) |

## Laravel Example (manual instantiation)

```php
<?php

use LibreOffice\LibreOffice;

$lo = new LibreOffice([
    'temp_dir' => storage_path('app/tmp/libreoffice'),
]);

$result = $lo->convert(storage_path('app/invoices/invoice.docx'))->to('pdf');
```

## Symfony Example (manual instantiation)

```php
<?php

use LibreOffice\LibreOffice;

$lo = new LibreOffice([
    'temp_dir' => '/var/tmp/libreoffice',
]);

$result = $lo->convert('/srv/documents/input.docx')->to('pdf');
```

## CLI Usage

After install, command is available as:

```bash
vendor/bin/libreoffice
```

### Probe installation

```bash
vendor/bin/libreoffice lo:probe --binary=soffice --temp-dir=/tmp
```

### Install LibreOffice (Ubuntu/Debian)

```bash
vendor/bin/libreoffice lo:install
```

### Convert file

```bash
vendor/bin/libreoffice lo:convert /path/file.docx --to=pdf --out=/path/out --timeout=180 --profile=per_job --temp-dir=/tmp/libreoffice --keep-temp -vv
```

## Troubleshooting

- **`User installation could not be completed` / `dconf-CRITICAL`**  
  Use a writable `temp_dir` and set `profile_strategy=per_job` (default on Linux) or `shared_mutex`.
- **Binary not found**  
  Install LibreOffice and ensure `soffice` is in `PATH`, or provide explicit `binary` option.
- **Slow startup**  
  Try `per_worker` strategy for long-lived workers, with a stable `worker_id`.
- **Permissions issues**  
  Confirm `temp_dir` is writable by the PHP process user.

## Testing

```bash
composer test
```

Optional integration test:

- set `RUN_LO_INTEGRATION=1` or `LO_BIN=/path/to/soffice`
- provide a fixture `tests/Fixtures/sample.docx`
