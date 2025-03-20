<?php

declare(strict_types=1);

namespace Thinktomorrow\DynamicAttributes;

trait HasDynamicAttributes
{
    private DynamicDocument $dynamicDocument;
    private ?string $activeDynamicLocale = null;

    protected array $dynamicLocales = [];
    protected array $dynamicFallbackLocales = [];

    public function initializeHasDynamicAttributes()
    {
        $this->dynamicDocument = new DynamicDocument();
    }

    public function dynamic(string $key, ?string $index = null, $default = null)
    {
        return $this->dynamicDocument->get($index ? "$key.$index" : $key, $default);
    }

    public function setDynamic(string $key, $value, ?string $index = null): void
    {
        $this->dynamicDocument->set($index ? "$key.$index" : $key, $value);

        parent::setAttribute($this->dynamicDocumentKey(), $this->dynamicDocument->toJson());
    }

    public function removeDynamic(string $key, ?string $index = null): void
    {
        $this->dynamicDocument->remove($index ? "$key.$index" : $key);

        parent::setAttribute($this->dynamicDocumentKey(), $this->dynamicDocument->toJson());
    }

    public function isDynamic($key): bool
    {
        if (in_array($key, $this->dynamicKeys())) {
            return true;
        }

        if (in_array('*', $this->dynamicKeys())) {
            return ! in_array($key, array_merge([$this->dynamicDocumentKey()], $this->dynamicKeysBlacklist()));
        }

        return false;
    }

    public function rawDynamicValues(): array
    {
        return $this->dynamicDocument->all();
    }

    private function isNestedDynamic($key): bool
    {
        if (false === strpos($key, '.')) {
            return false;
        }

        // First part is considered to be the dynamic attribute key.
        $dynamicKey = substr($key, 0, strpos($key, '.'));

        return $this->isDynamic($dynamicKey);
    }

    /**
     * The attribute key which the dynamic attributes is
     * referenced by as well as the database column name.
     *
     * @return string
     */
    protected function dynamicDocumentKey(): string
    {
        return 'values';
    }

    /**
     * The attributes that should be treated as dynamic ones. This
     * is a list of keys matching the database column names.
     *
     * @return array
     */
    protected function dynamicKeys(): array
    {
        return property_exists($this, 'dynamicKeys') ? $this->dynamicKeys : [];
    }

    /**
     * When allowing by default for all attributes to be dynamic, you can use
     * the blacklist to mark certain attributes as non dynamic.
     *
     * @return array
     */
    protected function dynamicKeysBlacklist(): array
    {
        return property_exists($this, 'dynamicKeysBlacklist') ? $this->dynamicKeysBlacklist : [];
    }

    /* Override Eloquent method as part of the custom cast coming from a query result */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        // Populate the dynamic document
        foreach ($attributes as $key => $value) {
            if ($key === $this->dynamicDocumentKey()) {
                $this->fillDynamicDocument($value);
                $attributes[$key] = $this->dynamicDocument->toJson();
            }
        }

        return parent::setRawAttributes($attributes, $sync);
    }

    /*
     * Override Eloquent method as part of the custom cast
     *
     * If the dynamic attributes contain a localized value,
     * this has preference over any non-localized.
     *
     * There is no fallback strategy in place for a missing locale. If the attribute is localized
     * but it doesn't have a translation for the passed key, null is returned.
     */
    public function getAttribute($key)
    {
        if (! $this->isDynamic($key)) {
            return parent::getAttribute($key);
        }

        $locale = $this->getActiveDynamicLocale();

        if ($this->dynamicDocument->has("$key.{$locale}")) {
            return $this->getLocalizedValue($key, $locale);
        }

        if ($this->dynamicDocument->has($key)) {
            $value = $this->dynamic($key);

            if (is_array($value) && count(array_intersect($this->getDynamicLocales(), array_keys($value))) > 0) {
                return $this->getLocalizedValue($key, $locale);
            }

            return $value;
        }

        return parent::getAttribute($key);
    }

    public function setDynamicLocales(array $locales): void
    {
        $this->dynamicLocales = $locales;
    }

    protected function getDynamicLocales(): array
    {
        return $this->dynamicLocales;
    }

    public function setActiveDynamicLocale(?string $locale): void
    {
        $this->activeDynamicLocale = $locale;
    }

    protected function getActiveDynamicLocale(): string
    {
        return $this->activeDynamicLocale ?? app()->getLocale();
    }

    /**
     * Set the fallback locales for dynamic attributes.
     * Key-value pairs where the key is the locale for which the
     * fallback should be active and the value is the fallback locale.
     */
    public function setDynamicFallbackLocales(array $fallbackLocales): void
    {
        $this->dynamicFallbackLocales = $fallbackLocales;
    }

    protected function getDynamicFallbackLocales(): array
    {
        return $this->dynamicFallbackLocales;
    }

    protected function getDynamicFallbackLocale(string $locale): null|string
    {
        return $this->getDynamicFallbackLocales()[$locale] ?? null;
    }

    private function getLocalizedValue(string $key, string $locale)
    {
        $fallbackLocale = $this->getDynamicFallbackLocale($locale);

        if ($locale && $this->dynamicDocument->has("$key.{$locale}")) {
            $value = $this->dynamic("$key.{$locale}");

            // If fallback locale is given, we avoid returning null values and instead try to retrieve value via the fallback locale.
            if (! is_null($value)) {
                return $value;
            }
        }

        if($fallbackLocale) {
            return $this->getLocalizedValue($key, $fallbackLocale);
        }

        return null;


//        if ($this->dynamicDocument->has("$key.{$fallbackLocale}")) {
//            return $this->dynamic("$key.{$fallbackLocale}");
//        }
//
//        return null;
    }

    /* Override Eloquent method as part of the custom cast */
    public function setAttribute($key, $value): void
    {
        if ($this->isDynamic($key) || $this->isNestedDynamic($key)) {
            $this->setDynamic($key, $value);

            return;
        }

        if ($key === $this->dynamicDocumentKey()) {
            $this->fillDynamicDocument($value);
            $value = $this->dynamicDocument->toJson();
        }

        parent::setAttribute($key, $value);
    }

    private function fillDynamicDocument($value): void
    {
        $this->dynamicDocument = DynamicDocumentCast::merge($this->dynamicDocument, $value);
    }

    /**
     * Custom check to allow dotted syntax for dynamic attributes. By default Laravel avoids mass assignments
     * if the key contains a dot, which is expected to be a relation syntax. Here we make sure that
     * dynamic attributes can be set via the Model::create() method with the dot syntax, e.g.
     * Model::create(['title.en' => 'My title'])
     *
     * @param $key
     * @return bool
     */
    public function isFillable($key)
    {
        $isFillable = parent::isFillable($key);

        if ($this->isNestedDynamic($key)) {
            $isFillable = true;
        }

        return $isFillable;
    }
}
