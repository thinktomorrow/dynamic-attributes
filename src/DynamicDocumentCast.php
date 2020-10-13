<?php
declare(strict_types=1);

namespace Thinktomorrow\DynamicAttributes;

final class DynamicDocumentCast
{
    /**
     * Merge the raw values into the established document
     *
     * @param DynamicDocument $dynamicDocument
     * @param array|string|mixed $values
     * @return DynamicDocument
     */
    public static function merge(DynamicDocument $dynamicDocument, $values): DynamicDocument
    {
        $values = is_null($values) ? [] : (is_array($values) ? $values : json_decode($values, true));

        return $dynamicDocument->merge($values);
    }
}
