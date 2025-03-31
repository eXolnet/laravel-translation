<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Tests\Mocks\Models\Example;
use Illuminate\Database\Eloquent\Builder;

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
     */
    public function testScopeWhereHasTranslationDefaultParameters(): void
    {
        $query = Example::query()->whereHasTranslation(function () {
            //
        });

        $this->assertEquals(
            'select * from "examples" where exists (select * from "example_translations" where ' .
            '"examples"."id" = "example_translations"."example_id" and "locale" = ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['en'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     */
    public function testScopeWhereHasTranslationWithParameters(): void
    {
        $query = Example::query()->whereHasTranslation(function () {
            //
        }, 'fr', '>', 2);

        $this->assertEquals(
            'select * from "examples" where (select count(*) from "example_translations" where ' .
            '"examples"."id" = "example_translations"."example_id" and "locale" = ?) > 2',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     */
    public function testScopeWhereHasTranslationWithCallback(): void
    {
        $query = Example::query()->whereHasTranslation(function (Builder $query) {
            return $query->where('description', 'like', 'test');
        });

        $this->assertEquals(
            'select * from "examples" where exists (select * from "example_translations" where ' .
            '"examples"."id" = "example_translations"."example_id" and "locale" = ? and "description" like ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['en', 'test'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     */
    public function testScopeHasTranslation(): void
    {
        $query = Example::query()->HasTranslation('description', 'test', 'fr', 'like');

        $this->assertEquals(
            'select * from "examples" where exists (select * from "example_translations" where ' .
            '"examples"."id" = "example_translations"."example_id" and "locale" = ? and "description" like ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr', 'test'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     */
    public function testScopeWhereTranslation(): void
    {
        $query = Example::query()->WhereTranslation('name', 'like', 'name1', 'fr');

        $this->assertEquals(
            'select * from "examples" where exists (select * from "example_translations" where ' .
            '"examples"."id" = "example_translations"."example_id" and "locale" = ? and "name" like ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr', 'name1'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     */
    public function testScopeWhereTranslationMixedValue(): void
    {
        $query = Example::query()->WhereTranslation('name', '>=', '2', 'fr');

        $this->assertEquals(
            'select * from "examples" where exists (select * from "example_translations" where ' .
            '"examples"."id" = "example_translations"."example_id" and "locale" = ? and "name" >= ?)',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr', '2'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     */
    public function testScopeJoinTranslation(): void
    {
        $query = Example::query()->JoinTranslation();

        $this->assertEquals(
            'select * from "examples" left join "example_translations" on ' .
            '"example_translations"."example_id" = "examples"."id" where "example_translations"."locale" = ?',
            $query->toSql()
        );

        $this->assertEquals(
            ['en'],
            $query->getBindings()
        );
    }

    /**
     * @return void
     */
    public function testScopeJoinTranslationWithLocaleParameter(): void
    {
        $query = Example::query()->JoinTranslation('fr');

        $this->assertEquals(
            'select * from "examples" left join "example_translations" on ' .
            '"example_translations"."example_id" = "examples"."id" where "example_translations"."locale" = ?',
            $query->toSql()
        );

        $this->assertEquals(
            ['fr'],
            $query->getBindings()
        );
    }
}
