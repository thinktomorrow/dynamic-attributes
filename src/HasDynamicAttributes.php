<?php

declare(strict_types=1);

namespace Thinktomorrow\DynamicAttributes;

trait HasDynamicAttributes
{
    private DynamicDocument $dynamicDocument;

    public function initializeHasDynamicAttributes()
    {
        $this->dynamicDocument = new DynamicDocument();
    }

    public function dynamic(string $key, string $index = null)
    {
        return $this->dynamicDocument->get($index ? "$key.$index" : $key);
    }

    public function setDynamic(string $key, $value, string $index = null): void
    {
        $this->dynamicDocument->set($index ? "$key.$index" : $key, $value);

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
     * @return array
     */
    protected function dynamicKeys(): array
    {
        return property_exists($this, 'dynamicKeys') ? $this->dynamicKeys : [];
    }

    protected function dynamicLocales(): array
    {
        return property_exists($this, 'dynamicLocales') ? $this->dynamicLocales : [];
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

        $locale = app()->getLocale();

        if ($this->dynamicDocument->has("$key.{$locale}")) {
            return $this->dynamic("$key.{$locale}");
        }

        if ($this->dynamicDocument->has($key)) {
            $value = $this->dynamic($key);

            if (is_array($value) && in_array($locale, $this->dynamicLocales())) {
                return null;
            }

            return $value;
        }

        return parent::getAttribute($key);
    }

    /* Override Eloquent method as part of the custom cast */
    public function setAttribute($key, $value): void
    {
        if ($this->isDynamic($key)) {
            $this->setDynamic($key, $value);
        } else {
            if ($key === $this->dynamicDocumentKey()) {
                $this->fillDynamicDocument($value);
                $value = $this->dynamicDocument->toJson();
            }

            parent::setAttribute($key, $value);
        }
    }

    private function fillDynamicDocument($value): void
    {
        $this->dynamicDocument = (new DynamicDocumentCast())->merge($this->dynamicDocument, $value);
    }
}
