# Dynamic attributes

This small package adds document-like storage behaviour to your eloquent model. It allows the model to interact
with json column values as if they were top-level attributes. 

## Quick overview
Here's the quick how to on setting up the package. We'll assume you already have an eloquent model set up.
1. Add a json column to the database scheme of your eloquent model. By default, it should be named `values`.
2. Add the `HasDynamicAttributes` trait to your eloquent model.
3. Add a `dynamicKeys` property on the model and fill it with the value keys that should be stored and fetched from the json column.

## Installation

You can install the package via composer:

```bash
composer require thinktomorrow/dynamic-attributes
```

Next, add the `HasDynamicAttributes` trait to your eloquent model. 
Also add a `dynamicKeys` property. This dynamic keys list informs the model on which value keys should be stored and fetched from the json column. Here's an example setup:

```php
use Illuminate\Database\Eloquent\Model;
use Thinktomorrow\DynamicAttributes\HasDynamicAttributes;

class ExampleModel extends Model
{
    use HasDynamicAttributes;

    protected $dynamicKeys = ['firstname', 'lastname'];

    // ...
}
```

## Storing as json column
Behind the scenes the values are automatically transposed to and from the json attribute. 
On the model, no custom eloquent cast is needed, because the trait itself will handle the conversion to and from the stored value. 
The dynamic attributes are preferably stored as a json database column but a string typed column should also be fine. The json column will just make your life easier when you need to query the database.
By default, the database column name is assumed to be `values`. You are free to change this. 

## Usage

Let's take the previous code example to demonstrate the usage of the package.

### setDynamic()
The value can be set just like you're used to with any other eloquent attribute. There's also a `dynamic` method which allows you to explicitly set a dynamic attribute.
``` php
// Setting a dynamic attribute just like a regular attribute.
$model = new ExampleModel(['firstname' => 'Ben']);

// .. or by setting it after instantiation
$model->firstname = 'Ben';

// Is the same as
$model->setDynamic('firstname', 'Ben');
```

### dynamic()
The value can be retrieved like a regular attribute. You can use the `dynamic` method to retrieve the dynamic attribute value as well.
``` php
// Retrieve a dynamic attribute just like a regular attribute
$model = new ExampleModel(['firstname' => 'Ben']);
$model->firstname; // Ben

// Or via the 'dynamic' method
$model->dynamic('firstname'); // Ben

// The 'dynamic' method allows for a default value in case the attribute isn't found
$model->dynamic('xxx', 'default'); // default
```

### isDynamic()
Check if the passed key refers to a dynamic attribute key or not. 
```php
$model->isDynamic('title'); // true
$model->isDynamic('xxx'); // false
```

## Localization
Dynamic attributes are built with localization in mind. 
The only thing you'll need to do is add a `dynamicLocales` property on your model. This should contain all the locales of the model.
```php
use ...

class ExampleModel extends Model
{
    use HasDynamicAttributes;

    protected $dynamicKeys = ['title'];

    protected $dynamicLocales = ['en', 'nl'];
}
```

Localized values should be in an array where each key represents a locale and the value the corresponding translation. 
Setting a localized value is done by passing the locale as a nested key:
``` php
$model->setDynamic('title.en', 'My article title');
$model->setDynamic('title.nl', 'Mijn blogtitel');
```

Retrieving the localized value: 
``` php
// When fetching the attribute without locale indication, the current application locale will be used.  
$model->title; // My article title 

app()->setLocale('nl');
$model->title; // Mijn blogtitel

// You can fetch a specific localized value with the dynamic() method
$model->dynamic('title.en'); // My article title

// If the localized value isn't found, null is returned
$model->dynamic('title.fr'); // null
```

### Changing the database column name
By default the column name is set to `values`. You can change this by overriding the `dynamicDocumentKey` in your model and returning your custom column/attribute name.

## Some warnings on method inheritance
The trait class overrides some some eloquent methods. This is because of the integral connection with eloquent attribute logic and the way eloquent allows for behavioural change via inheritance.
The methods are: `getAttribute`, `setAttribute` and `setRawAttributes`. 
If you use another trait that also overrides one of these methods, you're gonna bump into a method collision and need to alias the trait methods. 

## Testing

``` bash
composer test
```

Or with coverage:
``` bash
composer test-coverage
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please email dev@thinktomorrow.be instead of using the issue tracker.

## Credits

- [bencavens](https://github.com/bencavens)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
