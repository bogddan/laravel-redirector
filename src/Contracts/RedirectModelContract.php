<?php

namespace Bogddan\Redirects\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface RedirectModelContract
{
    public function setOldUrlAttribute(string $value);

    public function setNewUrlAttribute(string $value);

    public function setNewUrlExternalAttribute(string $value);

    public function scopeWhereOldUrl(Builder $query, string $url): Builder;

    public function scopeWhereNewUrl(Builder $query, string $url): Builder;

    public static function getStatuses(): array;

    public function syncOldRedirects(self $model, string $finalUrl): void;

    public static function findValidOrNull(string $path): ?self;
}
