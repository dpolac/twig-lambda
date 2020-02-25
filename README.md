# Twig Lambda

----------------------------------------------------------------

| Version | Twig Version | Php Version | 
|---- |----|----|
| ^3.0 | ^2.10 | ^7.2 |
| ^2.0 | ^2.10 | ^7.0 |
| ^1.0 | ^1.0 &#124;&#124; 2.9.* | ^5.6 &#124;&#124; ^7.0 |

<a name="examples"></a>
## Quick examples

Listing names of all authors ordered by age:
```twig
{% for author in articles|map(v => v.author)|unique_by('===')|sort_by(v => v.age) %}
    * {{ author.name }}, {{ author.age }}
{% endfor %}
```

Counting elements starting from specified letter:
```twig
{% for key, count in ['foo', 'bar', 'foobar']|count_by(v => v|first|capitalize) %}
    * {{ count }} elements start from {{ key }}.
{% endfor %}
```

----------------------------------------------------------------

<a name="install"></a>
## Installation

**Install via Composer:**
```bash
composer require leonaero/twig-lambda
```

**Add the extension to Twig:**
```php
$twig->addExtension(new \LeonAero\TwigLambda\LambdaExtension());
```

**... or if you use Symfony**, add the following to your `services.yml` config file:

```yaml
services:
    # ...
    leonaero.twig_lambda.extension:
        class: LeonAero\TwigLambda\LambdaExtension
        tags: [ { name: twig.extension } ]
```

----------------------------------------------------------------

## Usage
Below is a list of available filters. All works with arrays and any Traversable object and preserve it keys.

----------------------------------------------------------------

<a name="unique_by"></a>
### |unique_by
**Signature:** `array|unique_by(lambda|'==='|'==')`

Returns array of unique elements. Uniqueness is checked with
passed lambda. PHP operators == or === will be used if
string '==' or '===' is passed instead of lambda.

Lambda should have two arguments - items to check. Keys of
elements are also passed as third and fourth argument.

```twig
    {% for i in [1, 2, 2, 3, 1]|unique_by((i1, i2) => i1 == i2) %}
        {{ i }} {# prints '1 2 3' #}
    {% endfor %}
```
equivalent
```twig
    {% for i in [1, 2, 2, 3, 1]|unique_by('==') %}
        {{ i }} {# prints '1 2 3' #}
    {% endfor %}
```


----------------------------------------------------------------

<a name="group_by"></a>
### |group_by
**Signature:** `array|group_by(lambda)`

Sorts an array into groups by the result of lambda.

```twig
{% for key, group in ['foo', 'bar', 'foobar', 'barbar']|group_by(v => v|first|capitalize) %}
    = {{ key }}
    {% for i in group %}
        * {{ i }}
    {% endfor %}
{% endfor %}
```
will produce
```
    = F
        * foo
        * foobar
    = B
        * bar
        * barbar
```

----------------------------------------------------------------

<a name="count_by"></a>
### |count_by
**Signature:** `array|count_by(lambda)`

Sorts an array into groups and returns a count for the number of
objects in each group.

If lambda returns true, false or null, it will be converted to
string 'true', 'false' or 'null'. Float will be converted to
integer.

```twig
{% for key, count in ['foo', 'bar', 'foobar']|count_by(v => v|first|capitalize) %}
    * {{ count }} elements start from {{ key }}.
{% endfor %}
```
will produce
```
    * 2 elements start from F
    * 1 elements start from B
```

----------------------------------------------------------------

<a name="is_any"></a>
### |is_any
**Signature:** `array|is_any(lambda)`

Returns true if lambda returns true for any element from an array.

**Returns false if array is empty.**

```twig
{{ [1, 2, 3]|is_any(v => v is even) ? "There is even element in the array." }}
{# prints 'There is even element in the array.' #}
```

----------------------------------------------------------------

<a name="is_every"></a>
### |is_every
**Signature:** `array|is_every(lambda)`

Returns true if lambda returns true for every element from an array.

**Returns true if array is empty.**

```twig
{{ [1, 2, 3]|is_every(v => v > 0) ? "All elements in the array are positive." }}
{# prints 'All elements in the array are positive.' #}
```
