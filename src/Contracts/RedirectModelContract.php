<?php

namespace Tofandel\Redirects\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface RedirectModelContract
{
    /**
     * @param  string  $value
     */
    public function setOldUrlAttribute(string $value);

    /**
     * @param  string  $value
     */
    public function setNewUrlAttribute(string $value);

    public function setNewUrlExternalAttribute(string $value);

    /**
     * @param  Builder  $query
     * @param  string  $url
     * @return Builder
     */
    public function scopeWhereOldUrl(Builder $query, string $url): Builder;

    /**
     * @param  Builder  $query
     * @param  string  $url
     * @return Builder
     */
    public function scopeWhereNewUrl(Builder $query, string $url): Builder;

    /**
     * @return array
     */
    public static function getStatuses(): array;

    /**
     * @param  RedirectModelContract  $model
     * @param  string  $finalUrl
     * @return void
     */
    public function syncOldRedirects(self $model, string $finalUrl): void;

    /**
     * @param  string  $path
     * @return RedirectModelContract|null
     */
    public static function findValidOrNull(string $path): ?self;
}
