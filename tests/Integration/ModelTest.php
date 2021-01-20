<?php

namespace Exolnet\Translation\Tests\Integration;

use Exolnet\Translation\Tests\Mocks\Models\Example;

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
    public function testScopeWhereHasTranslation(): void
    {
        $query = Example::query()->whereHasTranslation(function () {
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
}
