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

You can optionally publish the config file with:

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
 <summary><code>TimedCommand</code> - Print command execution time</summary>

<br>

Simple extension of the Laravel `Command` that prints execution time after completion.

```php
use Chiiya\Common\Commands\TimedCommand;  
  
class SendEmails extends TimedCommand
{
    protected $signature = 'mail:send {user}';
    
    public function handle(DripEmailer $drip)
    {
        $drip->send(User::find($this->argument('user')));
    }  
```
```bash
$ php artisan mail:send 1
> Execution time: 0.1s  
```
</details>

<details>
 <summary><code>SetsSender</code> - Set sender for mailables</summary>

<br>
  
Trait to set the sender (return path) for mailables for e.g. bounce handling.

```php
use Chiiya\Common\Mail\SetsSender;
  
class OrderShipped extends Mailables
{
    use SetsSender;

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
 <summary><code>PresentableTrait</code> - View presenters for eloquent models</summary>

<br>
  
View presenter similar to the no longer maintained [`laracasts/presenter`](https://github.com/laracasts/Presenter)
package. Useful for doing some manipulations before displaying data.

```php
use Chiiya\Common\Presenter\Presenter;

/** @extends Presenter<User> */  
class UserPresenter extends Presenter
{
    public function name(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
```

```php
use Chiiya\Common\Presenter\PresentableTrait;
  
class User extends Model
{
    /** @use PresentableTrait<UserPresenter> */
    use PresentableTrait;
    
    protected string $presenter = UserPresenter::class;
}
```

```html
<h1>Hello, {{ $user->present()->name }}</h1>
```
</details>

<details>
 <summary><code>AbstractRepository</code> - Base repository for the repository pattern</summary>

<br>
  
Base repository for usage of the repository pattern. It provides `get`, `find`, `index`, `search`, `count`, `create`,
`update` and `delete` methods for the configured `$model`. Most methods accept an optional `$filters` parameter,
that may be used to apply the filters configured in the `applyFilters` method to your queries.
  
A general recommendation is to only use repositories as a place to store your complex queries and/or queries that 
are used repeatedly in multiple places, since otherwise they might be considered an anti-pattern. For more complex queries
it can however be useful to separate them from your services. Repositories also serve as a way to self-document those
queries by using descriptive method names. This way developers don't have to parse database queries and try to understand
their purpose when going through your application logic.

<br>

```php
use Chiiya\Common\Repositories\AbstractRepository;

/**
 * @extends AbstractRepository<Post>
 */
class PostRepository extends AbstractRepository
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
 <summary><code>CodeService</code> - Generate large amounts of random codes</summary>

<br>

Service class for efficiently generating large amounts of random, unique codes in memory
for later processing.

```php
use Chiiya\Common\Services\CodeService::class;
          
class CouponService {
    public function __construct(
        private CodeService $service,
    ) {}
          
    public function generateCodes()
    {
        // Optional, import previously exported codes so that we don't generate codes that already exist
        $this->service->import(storage_path('app/exports'));
        // Generate specified amount of random codes using the given pattern and character set
        $this->service->generate(
            1_000_000,
            '####-####-####',
            CodeService::SET_NUMBERS_AND_UPPERCASE,
        );
        // Get generated codes for further processing
        $codes = $this->service->getCodes();
        // ... e.g. bulk insert $codes into database
        // Export newly generated codes into (batched) CSV files. Optionally specify the amount of codes per file
        $this->service->export(storage_path('app/exports'));
        $this->service->export(path: storage_path('app/exports'), perFile: 500_000);
    }
}
```
</details>


<details>
 <summary><code>CsvReader</code> - Read CSV files</summary>

<br>

Small wrapper around the [`openspout/openspout`](https://github.com/openspout/openspout) csv reader for high-performance
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
 <summary><code>CsvWriter</code> - Write CSV files</summary>

<br>

Small wrapper around the [`openspout/openspout`](https://github.com/openspout/openspout) csv writer:

```php
$writer = resolve(\Chiiya\Common\Services\CsvWriter::class);
$writer->open('/path/to/file.csv');
$writer->write(['Value 1', 'Value 2']);
$writer->close();
```
</details>

<details>
 <summary><code>ExcelReader</code> - Read XLS/XLSX files</summary>

<br>

Small wrapper around the [`openspout/openspout`](https://github.com/openspout/openspout) excel reader for high-performance
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
  <summary><code>ExcelWriter</code> - Write XLX/XLSX files</summary>

<br>

Small wrapper around the [`openspout/openspout`](https://github.com/openspout/openspout) excel writer:

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
 <summary><code>FileDownloader</code> - Download remote files</summary>

<br>

Utility class for downloading files from a remote URL.

```php
$downloader = resolve(\Chiiya\Common\Services\FileDownloader::class);
$file = $downloader->download('https://example.com/path/to/file.txt');
dump($file->getPath());
$file->delete();
```
</details>


<details>
 <summary><code>Zipper</code> - Unzip .zip files</summary>

<br>

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
