<?php
declare(strict_types=1);

namespace Thinktomorrow\DynamicAttributes\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Thinktomorrow\DynamicAttributes\HasDynamicAttributes;

class FallbackLocaleModelStub extends Model
{
    use HasDynamicAttributes;

    protected $dynamicKeys = [
        'title',
        'customs',
    ];

    protected $guarded = [];

    public static function migrateUp()
    {
        Schema::dropIfExists('model_stubs');

        Schema::create('model_stubs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content')->nullable();
            $table->json('values')->nullable();
            $table->timestamps();
        });
    }

    public function getDynamicLocales(): array
    {
        return ['nl', 'de', 'en'];
    }

    public function getDynamicFallbackLocales(): array
    {
        return count($this->dynamicFallbackLocales) ? $this->dynamicFallbackLocales : ['en' => 'nl'];
    }
}
