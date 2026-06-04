<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Daikazu\LaravelMeta\Concerns\HasMeta;
use Daikazu\LaravelMeta\Contracts\HasMetadata;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements HasMetadata
{
    use HasMeta;

    protected $guarded = [];

    public $timestamps = false;

    /** @var array<string, string> */
    protected array $metaCasts = [
        'featured' => 'boolean',
    ];
}
