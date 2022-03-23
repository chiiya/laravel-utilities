# Laravel Utilities

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chiiya/laravel-utilities.svg?style=flat-square)](https://packagist.org/packages/chiiya/laravel-utilities)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/chiiya/laravel-utilities/lint?label=code%20style)](https://github.com/chiiya/laravel-utilities/actions?query=workflow%3Alint+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/chiiya/laravel-utilities.svg?style=flat-square)](https://packagist.org/packages/chiiya/laravel-utilities)

Common classes and utilities for Laravel projects.

## Installation

You can install the package via composer:

```bash
composer require chiiya/laravel-utilities
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="utilities-config"
```

This is the contents of the published config file:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Temporary path
    |--------------------------------------------------------------------------
    | Used for downloads and unzipping files.
    */
    'tmp_path' => storage_path('tmp'),
];
```

## Usage

<details>
 <summary><code>TimedCommand</code></summary>


Simple extension of the Laravel `Command` that prints execution time after completion.

```php
class YourCommand extends \Chiiya\Common\Commands\TimedCommand
```
</details>

<details>
 <summary><code>SetsSender</code></summary>

Trait to set the sender (return path) for mailables for e.g. bounce handling.

```php
class OrderShipped extends Mailables
{
    use \Chiiya\Common\Mail\SetsSender;

    public function build(): self
    {
        return $this
            ->subject('Order shipped')
            ->markdown('emails.orders.shipped')
            ->sender('return@example.com');
    }
}
```
</details>

<details>
 <summary><code>PresentableTrait</code></summary>

View presenter similar to the no longer maintained [`laracasts/presenter`](https://github.com/laracasts/Presenter)
package.

```php
class UserPresenter extends \Chiiya\Common\Presenter\Presenter
{
    public function name(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
```

```php
class User extends Model
{
    /** @use PresentableTrait<UserPresenter> */
    use \Chiiya\Common\Presenter\PresentableTrait;
    
    protected string $presenter = UserPresenter::class;
}
```

```html
<h1>Hello, {{ $user->present()->name }}</h1>
```
</details>

<details>
 <summary><code>AbstractRepository</code></summary>

Base repository for usage of the repository pattern.

```php
/**
 * @extends AbstractRepository<Post>
 */
class PostRepository extends \Chiiya\Common\Repositories\AbstractRepository
{
    protected string $model = Post::class;

    /**
     * @return Collection<Post>
     */
    public function postsDiscussedYesterday()
    {
        return $this->newQuery()
            ->whereHas('comments', function (Builder $builder) {
                $builder
                    ->where('created_at', '>=', now()->subDay()->startOfDay())
                    ->where('created_at', '<=', now()->subDay()->endOfDay());
            })
            ->get();
    }

    /**
     * @inheritDoc
     */
    protected function applyFilters(Builder $builder, array $parameters): Builder
    {
        if (isset($parameters['title'])) {
            $builder->where('title', '=', $parameters['title']);
        }

        return $builder;
    }
}
```

```php
// Find by primary key
$post = $repository->get(10);
// Find (first) by filters
$post = $repository->find(['title' => 'Lorem ipsum']);
// List all entities, optionally filtered
$posts = $repository->index();
$posts = $repository->index(['title' => 'Lorem ipsum']);
// Count entities, optionally filtered
$count = $repository->count();
$count = $repository->count(['title' => 'Lorem ipsum']);
// Create new entity
$post = $repository->create(['title' => 'Some title']);
// Update entity
$repository->update($post, ['title' => 'Lorem ipsum']);
// Delete entity
$repository->delete($post);

// Custom methods
$posts = $repository->postsDiscussedYesterday();
```
</details>

<details>
 <summary><code>CodeService</code></summary>

Service for generating unique random codes.

```php
$service = resolve(\Chiiya\Common\Services\CodeService::class);

// Optional, import previously generated codes so that we don't generate codes that already exist
$service->import(storage_path('app/exports'));
// Generate specified amount of random codes using the given pattern and character set
$service->generate(
    1_000_000,
    '####-####-####',
    CodeService::SET_NUMBERS_AND_UPPERCASE,
);
// Get generated codes for further processing (e.g. database inserts)
$codes = $service->getCodes();
// Export newly generated codes into (batched) CSV files. Optionally specify the amount of
// codes per file
$service->export(storage_path('app/exports'));
$service->export(path: storage_path('app/exports'), perFile: 500_000);
```
</details>


<details>
 <summary><code>CsvReader</code></summary>

Small wrapper around the [`box/spout`](https://opensource.box.com/spout/) csv reader for high-performance
reading of CSV files:

```php
$reader = resolve(\Chiiya\Common\Services\CsvReader::class);
$reader->open('/path/to/file.csv');
foreach ($reader->rows() as $row) {
    $values = $row->toArray();
}
$reader->close();
```
</details>


<details>
 <summary><code>CsvWriter</code></summary>

Small wrapper around the [`box/spout`](https://opensource.box.com/spout/) csv writer:

```php
$writer = resolve(\Chiiya\Common\Services\CsvWriter::class);
$writer->open('/path/to/file.csv');
$writer->write(['Value 1', 'Value 2']);
$writer->close();
```
</details>


<details>
 <summary><code>ExcelReader</code></summary>

Small wrapper around the [`box/spout`](https://opensource.box.com/spout/) excel reader for high-performance
reading of XLS/XLSX files:

```php
$reader = resolve(\Chiiya\Common\Services\ExcelReader::class);
$reader->open('/path/to/file.xlsx');
foreach ($reader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {
        $values = $row->toArray();
    }
}
$reader->close();
```
</details>


<details>
 <summary><code>ExcelWriter</code></summary>

Small wrapper around the [`box/spout`](https://opensource.box.com/spout/) excel writer:

```php
$writer = resolve(\Chiiya\Common\Services\ExcelWriter::class);
$writer->open('/path/to/file.xlsx');
$writer->setCurrentSheetName('Sheet 1');
$writer->addHeaderRow(['Name', 'Email']);
$writer->write(['John Doe', 'john.doe@example.com']);
$writer->addSheet('Sheet 2');
$writer->write(['Value 1', 'Value 2']);
$writer->close();
```
</details>


<details>
 <summary><code>FileDownloader</code></summary>

Utility class for downloading files from a remote URL.

```php
$downloader = resolve(\Chiiya\Common\Services\FileDownloader::class);
$file = $downloader->download('https://example.com/path/to/file.txt');
dump($file->getPath());
$file->delete();
```
</details>


<details>
 <summary><code>Zipper</code></summary>

Utility class for unzipping .zip files.

```php
$zipper = resolve(\Chiiya\Common\Services\Zipper::class);
$location = $zipper->unzip('/path/to/file.zip');
```
</details>

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
