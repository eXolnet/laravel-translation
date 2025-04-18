<?php

namespace Exolnet\Translation\Tests\Integration\Traits;

use Exolnet\Translation\Tests\Integration\TestCase;
use Exolnet\Translation\Tests\Mocks\Models\Example;

class TranslatableTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\Tests\Mocks\Models\Example
     */
    protected $example;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('translatable.locales', ['en', 'fr']);

        $this->example = new Example();

        $this->example->setAttributeTranslations('name', [
            'fr' => 'test_fr',
            'en' => 'test_en'
        ]);
        $this->example->setAttributeTranslations('description', [
            'fr' => 'desc_fr',
            'en' => 'desc_en'
        ]);
    }

    /**
     * @return void
     */
    public function testGetTranslations(): void
    {
        $name = $this->example->getTranslations()->pluck('name');
        $description = $this->example->getTranslations()->pluck('description');

        self::assertEquals('test_fr', $name[0]);
        self::assertEquals('test_en', $name[1]);
        self::assertEquals('desc_fr', $description[0]);
        self::assertEquals('desc_en', $description[1]);
    }

    /**
     * @return void
     */
    public function testFillTranslations(): void
    {
        $translations = [
            'es' => [
                'name' => 'test_es',
                'description' => 'desc_es'
                ]
        ];

        $this->example->fillTranslations($translations);

        $name = $this->example->getTranslations()->pluck('name');
        self::assertEquals('test_es', $name[2]);
        $description = $this->example->getTranslations()->pluck('description');
        self::assertEquals('desc_es', $description[2]);
    }

    /**
     * @return void
     */
    public function testFillWithTranslations(): void
    {
        $attributes = [
                'name' => ['es' => 'test_es'],
                'description' => ['es' => 'desc_es']
        ];

        $this->example->fillWithTranslations($attributes);
        $name = $this->example->getTranslations()->pluck('name');
        self::assertEquals('test_es', $name[2]);
        $description = $this->example->getTranslations()->pluck('description');
        self::assertEquals('desc_es', $description[2]);
    }

    /**
     * @return void
     */
    public function testFillWithTranslationsWithNotTranslatableAttribute(): void
    {
        $attributes = [
            'title'    => ['en' => 'test_title'],
            'username' => ['en' => 'test_username'],
            'name'     => ['en' => 'test_en']
        ];

        $this->example->fillWithTranslations($attributes);

        $translatableAttributes = $this->example->translationAttributesToArray();
        self::assertFalse(in_array('test_title', $translatableAttributes));
        self::assertFalse(in_array('test_username', $translatableAttributes));
        self::assertTrue(in_array('test_en', $translatableAttributes));

        $exampleAttributes = $this->example->getAttributes();
        self::assertEquals('test_title', $exampleAttributes['title']['en']);
        self::assertEquals('test_username', $exampleAttributes['username']['en']);
    }

    /**
     * @return void
     */
    public function testTranslationAttributesToArrayReturnFallbackLocale(): void
    {
        $attributes = $this->example->translationAttributesToArray();

        self::assertEquals('test_en', $attributes['name']);
        self::assertEquals('desc_en', $attributes['description']);
    }

    /**
     * @return void
     */
    public function testTranslationAttributesToArrayAfterChangedLocale(): void
    {
        $this->app->setLocale('fr');
        $attributes = $this->example->translationAttributesToArray();

        self::assertEquals('test_fr', $attributes['name']);
        self::assertEquals('desc_fr', $attributes['description']);
    }

    /**
     * @return void
     */
    public function testTranslationAttributesToArrayWithHiddenAttributes(): void
    {
        $this->example->setHidden(['name']);
        $attributes = $this->example->translationAttributesToArray();

        self::assertFalse(in_array('test_en', $attributes));
        self::assertTrue(in_array('desc_en', $attributes));
    }

    /**
     * @return void
     */
    public function testAttributesTranslationToArray(): void
    {
        $attributes = $this->example->attributesTranslationToArray();

        self::assertEquals('test_fr', $attributes['name']['fr']);
        self::assertEquals('test_en', $attributes['name']['en']);
        self::assertEquals('desc_fr', $attributes['description']['fr']);
        self::assertEquals('desc_en', $attributes['description']['en']);
    }

    /**
     * @return void
     */
    public function testTranslationsToArray(): void
    {
        $array = $this->example->translationsToArray();

        self::assertEquals('test_fr', $array['fr']['name']);
        self::assertEquals('desc_fr', $array['fr']['description']);
        self::assertEquals('fr', $array['fr']['locale']);
        self::assertEquals('test_en', $array['en']['name']);
        self::assertEquals('desc_en', $array['en']['description']);
        self::assertEquals('en', $array['en']['locale']);
    }

    /**
     * @return void
     */
    public function testTranslationsAsObject(): void
    {
        $object = $this->example->translationsAsObject();

        self::assertEquals('test_fr', $object->fr->name);
        self::assertEquals('desc_fr', $object->fr->description);
        self::assertEquals('fr', $object->fr->locale);
        self::assertEquals('test_en', $object->en->name);
        self::assertEquals('desc_en', $object->en->description);
        self::assertEquals('en', $object->en->locale);
    }
}
