# Dynamic attributes

This small package adds document behaviour to your eloquent model. It allows the model to interact
with json values as if they were top-level attributes.

## Installation

You can install the package via composer:

```bash
composer require thinktomorrow/dynamic-attributes
```

Next, add the `HasDynamicAttributes` trait to your eloquent model. 

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
Do you notice the `dynamicKeys` property? Well, in order to know which values should be treated as dynamic ones you'll need to inform the model about it. 
Add the attribute keys to the `dynamicKeys` array. Now you're set to go.

## Storage
Some notes on storing the values. On the model, no custom eloquent cast is needed, because the trait itself will handle the conversion to and from the stored value. 
The dynamic attributes are preferrably stored as a json database column but a string typed column should also be fine. The json column will just make your life easier when you need to query the database.
By default, the database column name is assumed to be `values`. You are free to change this. 

## Usage

``` php
    $model = new ExampleModel(['firstname' => 'Ben']);

    $model->firstname; // returns 'Ben'
```

- nested values
- localization
- api: dynamic, setDynamic

## Configuration

#### Changing the database column name
...

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
