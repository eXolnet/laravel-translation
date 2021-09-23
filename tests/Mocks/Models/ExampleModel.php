<?php


namespace Exolnet\Translation\Tests\Mocks\Models;

use Exolnet\Translation\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model
{
    use Translatable;

    /**
     * @var array
     */
    protected $translatedAttributes = ['name', 'description'];
}
