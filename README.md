# BestChange Client

This is a PHP library for interacting with the bestchange.ru API.

## Description

The `bestchange.ru` library provides a simple and efficient way to interact with the BestChange API. It allows you to retrieve and manipulate data related to currency exchange rates.

## Requirements

- PHP 7.4 or higher
- PHP Extensions: zip, iconv, curl, json

## Installation

This library can be installed via Composer:

```bash
composer require valentinnikolaev/bestchange
```

## Usage

```php
use BestChange\Client;
$bestChange = new BestChange();
```

