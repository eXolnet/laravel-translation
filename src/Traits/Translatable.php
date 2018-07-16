<?php namespace Exolnet\Translation\Traits;

use App;
use Closure;
use Dimsav\Translatable\Translatable as DimsavTranslatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use stdClass;

trait Translatable
{
    use DimsavTranslatable {
        DimsavTranslatable::save as translatableSave;
    }

    /**
     * @param array         $rules
     * @param array         $customMessages
     * @param array         $options
     * @param \Closure|null $beforeSave
     * @param \Closure|null $afterSave
     * @return bool
     */
    public function save(
        array $rules = [],
        array $customMessages = [],
        array $options = [],
        Closure $beforeSave = null,
        Closure $afterSave = null
    ) {
        if (count($this->translatedAttributes) > 0 && ! $this->translatableSave($options)) {
            return false;
        }

        return parent::save($rules, $customMessages, $options, $beforeSave, $afterSave);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Closure $callback
     * @param string|null $locale
     * @param string $operator
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasTranslation(
        Builder $query,
        Closure $callback,
        $locale = null,
        $operator = '>=',
        $count = 1
    ) {
        $locale = $locale ?: App::getLocale();

        return $query->whereHas('translations', function (Builder $query) use ($locale, $callback) {
            $query->where('locale', '=', $locale);
            call_user_func($callback, $query);

            return $query;
        }, $operator, $count);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param mixed $value
     * @param string|null $locale
     * @param string $op
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasTranslation(Builder $query, $key, $value, $locale = null, $op = '=')
    {
        return $this->scopeWhereHasTranslation($query, function (Builder $query) use ($key, $value, $op) {
            return $query->where($key, $op, $value);
        }, $locale);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param string $op
     * @param mixed $value
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereTranslation(Builder $query, $key, $op, $value, $locale = null)
    {
        return $this->scopeHasTranslation($query, $key, $value, $locale, $op);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinTranslation(Builder $query, $locale = null)
    {
        $translationTable = $this->getTranslationsTable();
        $localeKey        = $this->getLocaleKey();

        if ($locale === null) {
            $locale = App::getLocale();
        }

        return $query
            ->leftJoin(
                $translationTable,
                $translationTable.'.'.$this->getRelationKey(),
                '=',
                $this->getTable().'.'.$this->getKeyName()
            )
            ->where($translationTable.'.'.$localeKey, $locale);
    }

    /**
     * @param string|null $locale
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function translation($locale = null)
    {
        if (! $locale) {
            $locale = App::getLocale();
        }

        return $this->hasOne($this->getTranslationModelName(), $this->getRelationKey())
            ->where($this->getLocaleKey(), '=', $locale);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany($this->getTranslationModelName(), $this->getRelationKey());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $key
     * @return mixed
     */
    private function getTranslationByLocaleKey($key)
    {
        if (isset($this->relations['translation']) && ! isset($this->relations['translations'])) {
            $translation = $this->translation;

            if ($translation->getAttribute($this->getLocaleKey()) === $key) {
                return $translation;
            }
        }

        foreach ($this->translations as $translation) {
            if ($translation->getAttribute($this->getLocaleKey()) === $key) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @param array $values
     * @return $this
     */
    public function setAttributeTranslations($key, array $values)
    {
        foreach ($values as $locale => $value) {
            $this->translateOrNew($locale)->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param array $translations
     * @return $this
     */
    public function fillTranslations(array $translations)
    {
        foreach ($translations as $locale => $data) {
            $this->getTranslationOrNew($locale)->fill($data);
        }

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function fillWithTranslations(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isTranslationAttribute($key)) {
                $this->setAttributeTranslations($key, $value);
            } else {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function translationAttributesToArray()
    {
        $attributes = [];
        $hiddenAttributes = $this->getHidden();

        foreach ($this->translatedAttributes as $field) {
            if (in_array($field, $hiddenAttributes)) {
                continue;
            }

            if ($translations = $this->getTranslation()) {
                $attributes[$field] = $translations->$field;
            }
        }

        return $attributes;
    }

    /**
     * return array
     */
    public function attributesTranslationToArray()
    {
        $attributesTranslation = [];
        $attributes = $this->translatedAttributes;


        foreach ($attributes as $attribute) {
            foreach ($this->translations as $translation) {
                $locale = $translation->locale;
                $attributesTranslation[$attribute][$locale] = $translation->{$attribute};
            }
        }

        return $attributesTranslation;
    }

    /**
     * @return array
     */
    public function translationsToArray()
    {
        $translations = [];

        foreach ($this->translations as $translation) {
            $locale = $translation->locale;

            $translations[$locale] = $translation->toArray();
        }

        return $translations;
    }

    /**
     * @return \stdClass
     */
    public function translationsAsObject()
    {
        $translations = new stdClass;

        foreach ($this->translations as $translation) {
            $locale = $translation->locale;

            $translations->$locale = $translation;
        }

        return $translations;
    }

    /**
     * This function allows to build additional parameters to be used in dynamic URL.
     *
     * For example, if your route is "/products/{taxonomyType}", you might use the following
     * function to obtain the alternate parameters:
     *
     * $taxonomy->buildAlternateParameters(function(TaxonomyTranslation $translation) {
     *     return ['taxonomyType' => $translation->getSlug()];
     * });
     *
     * @param callable $callback
     * @return array
     */
    public function buildAlternateParameters(callable $callback)
    {
        $localeKey = $this->getLocaleKey();

        return $this->getTranslations()
            ->map(function (Model $translation) use ($callback, $localeKey) {
                /** @var array $parameters */
                $parameters = call_user_func($callback, $translation);

                return $parameters + [
                    'locale'  => $translation->getAttribute($localeKey),
                ];
            })
            ->keyBy('locale')
            ->toArray();
    }
}
