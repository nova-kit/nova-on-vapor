Laravel Nova on Laravel Vapor
==============

[![tests](https://github.com/nova-kit/nova-on-vapor/workflows/tests/badge.svg?branch=main)](https://github.com/nova-kit/nova-on-vapor/actions?query=workflow%3Atests+branch%3Amain)
[![Latest Stable Version](https://poser.pugx.org/nova-kit/nova-on-vapor/v/stable)](https://packagist.org/packages/nova-kit/nova-on-vapor)
[![Total Downloads](https://poser.pugx.org/nova-kit/nova-on-vapor/downloads)](https://packagist.org/packages/nova-kit/nova-on-vapor)
[![Latest Unstable Version](https://poser.pugx.org/nova-kit/nova-on-vapor/v/unstable)](https://packagist.org/packages/nova-kit/nova-on-vapor)
[![License](https://poser.pugx.org/nova-kit/nova-on-vapor/license)](https://packagist.org/packages/nova-kit/nova-on-vapor)

This library attempts to solves several limitations when using Laravel Nova on Laravel Vapor including:

* [x] Unable to use interactive mode on Artisan affecting `nova:user` command.
* [x] Ability to use `VaporFile` and `VaporImage` locally via Minio.
* [ ] `ExportAsCsv` supports for Laravel Vapor

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

> Without passing `--password`, the code would generate a random 8 character password and you can use the Forgot Password feature to reset the value. Using `--password` is possible but be aware that the value will be logged to CloudWatch.

### Minio for `VaporFile` and `VaporImage` locally

You can setup Minio locally and use it as a replacement for S3 locally. 

#### Installation
