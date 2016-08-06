# Yii 2 Location
Yii2 extension to provide location management.

The preferred way to install this extension is through [composer](https://getcomposer.org/).

Either run
```bash
composer require "kmergen/yii2-location: "*"
```
or add
```
"kmergen/yii2-location": "*",
```
to the `require` section of your `composer.json` file.

## Installation

### 1. Run Migrations
Run `$ yii migrate --migrationPath=@vendor/kmergen/yii2-location/migrations`

# Use Yii2-location with Yii2 basic template

## Configure application
In your configuration file set the following:
```php
'bootstrap' => [
    'kmergen\location\Bootstrap',
    ...
],
'modules' => [
    'location' => [
        'class' => 'kmergen\location\Module'
    ],
    ...
]
```
That's all. Yii-2 location is ready to go.

# Use Yii2-location with Yii2 advanced template

When using advanced template, you may have some special restrictions for frontend and backend.

## Configure application

Let's start with defining module in `@common/config/main.php`:

```php
'bootstrap' => [
    'kmergen\location\Bootstrap',
    ...
],
'modules' => [
    'location' => [
        'class' => 'kmergen\location\Module',
        // you will configure your module inside this file
        // or if need different configuration for frontend and backend you may
        // configure in needed configs
    ],
],
```

Restrict access to admin controller from frontend. Open `@frontend/config/main.php` and add following:

```
'modules' => [
    'location' => [
        // following line will restrict access to admin controller from frontend application
        'as frontend' => 'kmergen\location\filters\FrontendFilter',
    ],
],
```

Restrict access to frontend controller actions from backend. Open `@backend/config/main.php` and add the following:
```
'modules' => [
    'location' => [
        // following line will restrict access to actions of controller from backend
        'as backend' => 'kmergen\location\filters\BackendFilter',
    ],
],
```
That's all, now you have module installed and configured in advanced template.


> Note: This extension is under development. Use it not in production.

