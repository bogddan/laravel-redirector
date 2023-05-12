<?php

namespace Bogddan\Redirects\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Bogddan\Redirects\Contracts\RedirectModelContract;
use Bogddan\Redirects\Exceptions\RedirectException;

/**
 * @property string $new_url
 * @property string $old_url
 * @property-write string $new_url_external
 * @property int $status
 *
 * @method static Builder whereNewUrl(string $url)
 * @method static Builder whereOldUrl(string $url)
 */
class Redirect extends Model implements RedirectModelContract
{
    /**
     * The database table.
     *
     * @var string
     */
    protected $table = 'redirects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'old_url',
        'new_url',
        'new_url_external',
        'status',
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if (trim(mb_strtolower($model->old_url), '/') == trim(mb_strtolower($model->new_url), '/')) {
                throw RedirectException::sameUrls();
            }

            static::whereOldUrl($model->new_url)->whereNewUrl($model->old_url)->delete();

            $model->syncOldRedirects($model, $model->new_url);
        });
    }

    /**
     * The mutator to set the "old_url" attribute.
     *
     * @param  string  $value
     */
    public function setOldUrlAttribute(string $value)
    {
        $this->attributes['old_url'] = $this->parseRelativeUrl($value, false);
    }

    protected function parseRelativeUrl(string $url, $fragment = true): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'];
        if (! empty($parsed['query'])) {
            $path .= '?'.$parsed['query'];
        }
        if ($fragment && ! empty($parsed['fragment'])) {
            $path .= '#'.$parsed['fragment'];
        }

        return trim($path, '/');
    }

    /**
     * The mutator to set the "new_url" attribute.
     *
     * @param  string  $value
     */
    public function setNewUrlAttribute(string $value)
    {
        $this->attributes['new_url'] = $this->parseRelativeUrl($value);
    }

    /**
     * The mutator to set the "new_url" attribute if the new url is external.
     *
     * @param  string  $value
     */
    public function setNewUrlExternalAttribute(string $value)
    {
        $this->attributes['new_url'] = trim($value, '/');
    }

    /**
     * Filter the query by an old url.
     *
     * @param  Builder  $query
     * @param  string  $url
     * @return Builder
     */
    public function scopeWhereOldUrl(Builder $query, string $url): Builder
    {
        return $query->where('old_url', $url);
    }

    /**
     * Filter the query by a new url.
     *
     * @param  Builder  $query
     * @param  string  $url
     * @return Builder
     */
    public function scopeWhereNewUrl(Builder $query, string $url): Builder
    {
        return $query->where('new_url', $url);
    }

    /**
     * Get all redirect statuses defined inside the "config/redirects.php" file.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return (array) config('redirects.statuses', []);
    }

    /**
     * Sync old redirects to point to the new (final) url.
     *
     * @param  RedirectModelContract  $model
     * @param  string  $finalUrl
     * @return void
     */
    public function syncOldRedirects(RedirectModelContract $model, string $finalUrl): void
    {
        $items = static::whereNewUrl($model->old_url)->get();

        foreach ($items as $item) {
            $item->update(['new_url' => $finalUrl]);
            $item->syncOldRedirects($model, $finalUrl);
        }
    }

    /**
     * Return a valid redirect entity for a given path (old url).
     * A redirect is valid if:
     * - it has an url to redirect to (new url)
     * - it's status code is one of the statuses defined on this model.
     *
     * @param  string  $path
     * @return Redirect|Model|null
     */
    public static function findValidOrNull(string $path): ?RedirectModelContract
    {
        return static::whereOldUrl($path === '/' ? $path : trim($path, '/'))
            ->whereNotNull('new_url')
            ->whereIn('status', array_keys(self::getStatuses()))
            ->latest()->first();
    }
}
