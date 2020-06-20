<?php
declare(strict_types=1);

namespace Thinktomorrow\DynamicAttributes\Tests\Stubs;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Thinktomorrow\DynamicAttributes\HasDynamicAttributes;

class ModelStub extends Model
{
    use HasDynamicAttributes;

    protected $dynamicKeys = [
        'title',
    ];

    protected $dynamicLocales = ['nl','en'];

    protected $guarded = [];

    public static function migrateUp()
    {
        Schema::dropIfExists('model_stubs');

        Schema::create('model_stubs', function (Blueprint $table) {
            $table->increments('id');
            $table->json('values')->nullable();
            $table->timestamps();
        });
    }
}
