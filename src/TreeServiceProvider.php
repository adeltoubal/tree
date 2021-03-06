<?php

namespace Alba\Tree;

use Alba\Tree\Console\FixTree;
use Alba\Tree\Console\GrowTree;
use Alba\Tree\Console\ShowTree;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Migrations\MigrationCreator;

class TreeServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->when(MigrationCreator::class)
            ->needs('$customStubPath')
            ->give(function ($app) {
                return $app->basePath('stubs');
            });
        
        $this->commands([
            FixTree::class,
            GrowTree::class,
            ShowTree::class,
        ]);

        Builder::macro('debugSql', function() {
            $bindings = array_map(function ($value) {
                return is_string($value) ? '"'.addcslashes($value, '"').'"' : $value;
            }, $this->getBindings());
            return vsprintf(str_replace('?', '%s', $this->toSql()), $bindings);
        });

        EloquentBuilder::macro('debugSql', function() {
            $bindings = array_map(function ($value) {
                return is_string($value) ? '"'.addcslashes($value, '"').'"' : $value;
            }, $this->getBindings());
            return vsprintf(str_replace('?', '%s', $this->toSql()), $bindings);
        });

        EloquentBuilder::macro('findInOrder', function ($ids, $columns = ['*']) {
            return $this->findMany($ids, $columns)->sortByKeys($ids);
        });

        Collection::macro('sortByKeys', function(array $ids) {
            $ids = array_flip(array_values($ids));
            $i = $this->count();
            return $this->sortBy(function ($model) use ($ids, &$i) {
                return $ids[$model->getKey()] ?? ++$i;
            });
        });
    }
}
