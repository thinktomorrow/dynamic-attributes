# Changelog

## Unreleased

## 2.0.1 - 2025-03-20
- Changed: when returning a localized value for a locale that is not set as dynamic locale, null is returned. In previous releases, the raw array was returned instead. This often lead to render issues where a string instead of the raw array was expected.

- Added: You can set locales at runtime via the `setDynamicLocales()` method.
- Added: You can set fallback locales map via the `setDynamicFallbackLocales()` method. This expects an array with the locale as key and its fallback locale as value.
- Added: You can set the active dynamic locale via the `setActiveDynamicLocale()` method. By default the app locale is used.
- Removed: `dynamicLocaleFallback(string $locale)` method. This is now handled by the `getDynamicFallbackLocale(string $locale)` property.
- Changed: Following properties are added to the trait:
  - `private ?string $activeDynamicLocale = null;`
  - `protected array $dynamicLocales = [];`
  - `protected array $dynamicFallbackLocales = [];`
- Changed: Since the dynamicLocales in now an internal property, you cannot use this property on your model to set the dynamic locales. Override the `getDynamicLocales` method or use the `setDynamicLocales` method instead.
- Changed: Since the dynamicFallbackLocales in now an internal property, you cannot use this property on your model to set the dynamic fallback locales. Override the `getDynamicFallbackLocale` method or use the `setDynamicFallbackLocales` method instead.
