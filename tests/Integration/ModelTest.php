<?php

namespace Integration;

use Exolnet\Translation\Tests\Integration\TestCase;
use Exolnet\Translation\Tests\Mocks\Models\ExampleModel;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('translatable.locales', ['en', 'fr', 'es']);
    }

    /**
     * @return void
     * @test
     */
    public function testScopeWhereHasTranslationDefaultParameters(): void
    {
        $query = ExampleModel::query()->whereHasTranslation(function () {
            //
        });

        $this->assertEquals(
            'select * from `examples` where exists (select * from `example_translations` where ' .
            '`examples`.`id` = `example_translations`.`example_id` and `locale` = ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['en'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testScopeWhereHasTranslationWithParameters(): void
    {
        $query = ExampleModel::query()->whereHasTranslation(function () {
            //
        }, 'fr', '>', 2);

        $this->assertEquals(
            'select * from `examples` where (select count(*) from `example_translations` where ' .
            '`examples`.`id` = `example_translations`.`example_id` and `locale` = ?) > 2',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testScopeWhereHasTranslationWithCallback(): void
    {
        $query = ExampleModel::query()->whereHasTranslation(function (Builder $query) {
            return $query->where('description', 'like', 'test');
        });

        $this->assertEquals(
            'select * from `examples` where exists (select * from `example_translations` where ' .
            '`examples`.`id` = `example_translations`.`example_id` and `locale` = ? and `description` like ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['en', 'test'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testScopeHasTranslation(): void
    {
        $query = ExampleModel::query()->HasTranslation('description', 'test', 'fr', 'like');

        $this->assertEquals(
            'select * from `examples` where exists (select * from `example_translations` where ' .
            '`examples`.`id` = `example_translations`.`example_id` and `locale` = ? and `description` like ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr', 'test'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testScopeWhereTranslation(): void
    {
        $query = ExampleModel::query()->WhereTranslation('name', 'like', 'name1', 'fr');

        $this->assertEquals(
            'select * from `examples` where exists (select * from `example_translations` where ' .
            '`examples`.`id` = `example_translations`.`example_id` and `locale` = ? and `name` like ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr', 'name1'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testScopeWhereTranslationMixedValue(): void
    {
        $query = ExampleModel::query()->WhereTranslation('name', '>=', '2', 'fr');

        $this->assertEquals(
            'select * from `examples` where exists (select * from `example_translations` where ' .
            '`examples`.`id` = `example_translations`.`example_id` and `locale` = ? and `name` >= ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr', '2'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testScopeJoinTranslation(): void
    {
        $query = ExampleModel::query()->JoinTranslation();

        $this->assertEquals(
            'select * from `examples` left join `example_translations` on ' .
            '`example_translations`.`example_id` = `examples`.`id` where `example_translations`.`locale` = ?',
            $query->toSql()
        );

        $this->assertEquals(
            ['en'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     * @test
     */
    public function testScopeJoinTranslationWithLocaleParameter(): void
    {
        $query = ExampleModel::query()->JoinTranslation('fr');

        $this->assertEquals(
            'select * from `examples` left join `example_translations` on ' .
            '`example_translations`.`example_id` = `examples`.`id` where `example_translations`.`locale` = ?',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr'],
            $query->getBindings()
        );
    }
}
