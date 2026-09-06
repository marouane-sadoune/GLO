<?php

namespace App\Support\Http;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Base for per-resource filter classes. Subclasses list the query-string
 * keys they accept in filters() and implement a matching filter{Key}()
 * method; unlisted or empty values are ignored.
 */
abstract class QueryFilter
{
    public function __construct(protected Request $request) {}

    public function apply(Builder $query): Builder
    {
        foreach ($this->filters() as $key) {
            $value = $this->request->query($key);

            if ($value === null || $value === '') {
                continue;
            }

            $method = 'filter'.Str::studly($key);

            if (method_exists($this, $method)) {
                $this->$method($query, $value);
            }
        }

        return $query;
    }

    /**
     * @return list<string>
     */
    abstract protected function filters(): array;
}
