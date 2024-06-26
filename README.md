Laravel Nova on Laravel Vapor
==============

[![Tests](https://github.com/nova-kit/nova-on-vapor/workflows/tests/badge.svg?branch=1.x)](https://github.com/nova-kit/nova-on-vapor/actions?query=workflow%3Atests+branch%3A1.x)
[![Latest Stable Version](https://poser.pugx.org/nova-kit/nova-on-vapor/v/stable)](https://packagist.org/packages/nova-kit/nova-on-vapor)
[![Total Downloads](https://poser.pugx.org/nova-kit/nova-on-vapor/downloads)](https://packagist.org/packages/nova-kit/nova-on-vapor)
[![Latest Unstable Version](https://poser.pugx.org/nova-kit/nova-on-vapor/v/unstable)](https://packagist.org/packages/nova-kit/nova-on-vapor)
[![License](https://poser.pugx.org/nova-kit/nova-on-vapor/license)](https://packagist.org/packages/nova-kit/nova-on-vapor)

This library attempts to solves several limitations when using Laravel Nova on Laravel Vapor including:

* [x] Unable to use interactive mode on Artisan affecting `nova:user` command.
* [x] Ability to use `VaporFile` and `VaporImage` locally via Minio.
* [x] `ExportAsCsv` supports for Laravel Vapor

## Installation

To install through composer, run the following command from terminal:

```bash 
composer require "nova-kit/nova-on-vapor"
```

## Usages

### New `nova:vapor-user` Command

The command swaps interactive mode questions to artisan command options, so you can use `--email`, `--name` and optionally `--password`, as an example:

```bash
php artisan nova:vapor-user --name="Administrator" --email="nova@laravel.com"
```

> **Note**: Without passing `--password`, the code would generate a random 8 character password and you can use the Forgot Password feature to reset the value.

> **Warning**: Using `--password` is possible but be aware that the value will be logged to CloudWatch.

### Minio for `VaporFile` and `VaporImage` locally

You can setup Minio locally and use it as a replacement for S3 locally. First you need to install `league/flysystem-aws-s3-v3` by running the following command:

```bash
composer require "league/flysystem-aws-s3-v3"
```

Next, you need to enable and configure Minio:

```ini
NOVA_ON_VAPOR_ENABLES_MINIO=(true)

MINIO_USERNAME=
MINIO_PASSWORD=
MINIO_ENDPOINT=

AWS_ACCESS_KEY_ID="${MINIO_USERNAME}"
AWS_SECRET_ACCESS_KEY="${MINIO_PASSWORD}"
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=local
AWS_ENDPOINT="${MINIO_ENDPOINT}"
AWS_USE_PATH_STYLE_ENDPOINT=(true)
```

### CSV Export

You can replace `Laravel\Nova\Actions\ExportAsCsv` with `NovaKit\NovaOnVapor\Actions\VaporExportAsCsv`:

```php
use Laravel\Nova\Actions\ExportAsCsv;
use NovaKit\NovaOnVapor\Actions\VaporExportAsCsv;

/**
 * Get the actions available for the resource.
 *
 * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
 * @return array
 */
public function actions(NovaRequest $request)
{
    return [
        VaporExportAsCsv::make(),
    ];
}
```

If you would like to change the storage disk to store the CSV file that is available for download, you may invoke the `withStorageDisk()` method when registering the action:

```php
return [
    VaporExportAsCsv::make()->withStorageDisk('s3'),
];
```

### `downloadViaTemporaryUrl` Mixin

Laravel Vapor has a response limit of 6MB and this would cause issue when you need to download large files. You can avoid this by utilising `Storage::temporaryUrl()` to download the file:

```php
VaporFile::make('Filename')->downloadViaTemporaryUrl(),
```
