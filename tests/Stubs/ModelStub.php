<?php
declare(strict_types=1);

namespace Thinktomorrow\DynamicAttributes\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Thinktomorrow\DynamicAttributes\HasDynamicAttributes;

class ModelStub extends Model
{
    use HasDynamicAttributes {
        isValueEmpty as baseIsValueEmpty;
    }

    private ?\Closure $customValueEmpty = null;

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
        return ['nl','en'];
    }

    protected function isValueEmpty($value): bool
    {
        if ($this->customValueEmpty) {
            return call_user_func($this->customValueEmpty, $value);
        }

        return $this->baseIsValueEmpty($value);
    }

    public function setCustomValueEmpty($customValue): void
    {
        $this->customValueEmpty = $customValue;
    }
}
