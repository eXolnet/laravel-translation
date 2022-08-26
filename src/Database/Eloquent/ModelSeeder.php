<?php

namespace Exolnet\Translation\Database\Eloquent;

use Illuminate\Database\Seeder;

class ModelSeeder extends Seeder
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newModel()
    {
        return new $this->model();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->importModels($this->getData());
    }

    /**
     * @param array $models
     * @return array
     */
    private function importModels(array $models)
    {
        $importedModels = [];

        foreach ($models as $model) {
            $importedModels[] = $this->importModel($model);
        }

        return $importedModels;
    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function importModel(array $data)
    {
        $model = $this->newModel();

        $isTranslatedModel = method_exists($model, 'setAttributeTranslations');

        foreach ($data as $key => $value) {
            if ($isTranslatedModel && $model->isTranslationAttribute($key)) {
                $model->setAttributeTranslations($key, $value);
            } else {
                $model->setAttribute($key, $value);
            }
        }

        $model->save();

        return $model;
    }
}
