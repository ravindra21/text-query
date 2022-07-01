# text-query
parsing string query 'foo:bar baz:boo' to [['foo', '=', 'bar'], ['baz', '=', 'boo']] that can be used for search operations

### install
add in composer.json
```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/ravindra21/text-query"
    }
]
```
install
```
composer require ravindra21/text-query
```
publish config/text_query.php
```
php artisan vendor:publish --tag=text-query
```
### export
```
use Ravindra21\TextQuery\TextQuery
```
### encode
```
TextQuery::encode(['foo' => 'bar', 'baz' => 'boo']);
```

### decode

```
$query = 'foo:bar baz:boo';
$defaultKey = ['profiles.name'];
$rule = [
    "name" => [
        'stritch' => false,
        'allowedPerimeter' => [':'],
        'as' => 'profiles.name'
    ],
    "date" => [
        'stritch' => true,
        'allowedPerimeter' => [':', ">", '<'],
        'as' => 'day(birthday)'
    ]
];

TextQuery::decode($query, $rule, $defaultKey);
```

### variable description (kinda)

**$defaultKey ->** when query 'random text age:20', search 'random text' by column that specifies in $defaultKey

**stritch ->** if false translate ':' to 'LIKE' and add prefix and suffix '%' in the translated query value. if true, translate ':' to '='

**allowedPerimeter ->** allowed perimeter in query text 'name:a age<2 age>3'

**as ->** convert $rule key as already declared value
