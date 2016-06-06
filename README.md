# WP Endpoints: View

**This package is depreciated. LEAN now uses the WordPress REST API plugins instead**

> Generic but customisable view endpoint to expose our content via WP-API. This extension will create an endpoint (at ```/wp-json/leean/v1/view``` by default) which returns all the data required by a front-end app to render a view.

The endpoint takes a slug parameter (e.g. ```/wp-json/leean/v1/view?slug=sample-page```) and returns the following data for a single post:

- Id
- Template
- Content
  - WordPress default fields: title, content
  - All ACF fields associated with the page, grouped by their ACF field groups.
- Meta Data: title, description, open graph (note this hasn't been implemented yet).

Note: currently it just works for single posts (including pages and cpt's). We need to consider the best solution for other types of views, such as archive pages.

## Getting Started

The easiest way to install this package is by using composer from your terminal:

```bash
composer require moxie-lean/wp-endpoints-view --save
```

Or by adding the following lines on your `composer.json` file

```json
"require": {
  "moxie-lean/wp-endpoints-view": "dev-master"
}
```

This will download the files from the [packagist site](https://packagist.org/packages/moxie-lean/wp-endpoints-view) 
and set you up with the latest version located on master branch of the repository. 

After that you can include the `autoload.php` file in order to
be able to autoload the class during the object creation.

```php
include '/vendor/autoload.php';
```

Finally you need to initialise the endpoint by adding this to your code:

```php
\Lean\Endpoints\View::init();
```

## Usage

The extension has a number of filters which can be used to customised the output. In addition it does some useful extra manipulation of ACF data to make it more useful to a front-end app.

### Filters

Common parameters passed by many filers are:

- $endpoint : the name of the endpoint. Always '/view' for this extension.
- $post_id : the id of the post.
- $field : the ACF [field object](http://www.advancedcustomfields.com/resources/get_field_object/).

#### ln_endpoints_api_namespace
Customise the API namespace ('leean' in ```/wp-json/leean/v1/view```)

```php
add_filter( 'ln_endpoints_api_namespace', function( $namespace, $endpoint ) {
    return 'my-app';
}, 10, 2 );
```

#### ln_endpoints_api_version
Customise the API version ('v1' in ```/wp-json/leean/v1/view```)

```php
add_filter( 'ln_endpoints_api_version', function( $version, $endpoint ) {
    return 'v2';
}, 10, 2 );
```

#### ln_endpoints_query_args
Customise the query args before the post is queried using WP_Query.
$request is the WP_REST_Request object received by endpoint handler.

```php
add_filter( 'ln_endpoints_{endpoint}_query_args', function( $query_args, $request ) {
    $query_args['post_type'] = 'page';
    return $query_args;
}, 10, 3 );
```

#### ln_endpoints_data
Customise the results just before they are sent.

```php
add_filter( 'ln_endpoints_data_{endpoint}', function( $data, $post_id ) {
    $data['content']['title'] = '***' . $data['content']['title'] . '***';
    return $data;
}, 10, 3 );
```

On the previous two filters `{endpoint}` is the name of your endpoint in
this case is `post`, so for example `ln_endpoints_data_post` is the name
of the filter you should use to filter the data.

#### ln_endpoints_acf
Customise the value of an ACF field.

```php
add_filter( 'ln_endpoints_acf', function( $value, $endpoint, $post_id, $field ) {
    if ( 'image' === $field['type'] ) {
        return 'https://upload.wikimedia.org/wikipedia/commons/1/1b/Nice-night-view-with-blurred-cars_1200x900.jpg';
    }
    return $value;
}, 10, 4 );
```

#### ln_endpoints_acf_image_size
Set the image size to use. Only activated for image fields whose return format is set to 'id'.
Note that $sub_field is only used for images within repeaters.

```php
add_filter( 'ln_endpoints_acf_image_size', function( $size, $endpoint, $post_id, $field, $sub_field ) {
    if ( 'logo' === $field['name'] ) {
        return 'very_small';
    }
    return $size;
}, 10, 4 );
```

#### ln_endpoints_acf_repeater_as_array
Whether to return a repeater as an array. Only activated for repeater fields with exactly one value.

```php
add_filter( 'ln_endpoints_acf_repeater_as_array', function( $as_array, $endpoint, $post_id, $field ) {
    $post = get_post( $post_id );
    return 'training' === $post->page_template && 'cta' === $field['name'] ? false : $as_array;
}, 10, 4 );
```


### ACF Manipulations

#### Posts
Activated when the ACF field type is 'Post Object' and the return format is 'id'. Gets all the content for the post (including its ACF fields). If the 'select multiple values' option is set, then it returns an array of post data.

#### Images
Activated when the ACF field type is 'Image' and the return format is 'id'. The image size must be set using the ```ln_endpoints_acf_image_size``` filter. Returns the image url, width, height and alt.

#### Repeaters
Activated when the ACF field type is 'Repeater' and there is exactly one item. It passes a filter, ```ln_endpoints_acf_repeater_as_array``` which returns an object instead of an array if false.
