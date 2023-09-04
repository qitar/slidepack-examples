# Generate PPTX with SlidePack (PHP sample code)

## Prerequisites

* NodeJS >= 16
* Sign up for free and create your API token at https://slidepack.io/app

## Running the sample code

```bash
export SLIDEPACK_API_TOKEN="xxxxxxx"
composer install
php -S localhost:8000
```

This will run a local web server. Go to http://localhost:8000 and upload an input zip file to have it be rendered with SlidePack.
There is an `input.zip` file in this directory that you can use.

## More information

For more information about SlidePack, refer to:

* [The SlidePack Website](https://slidepack.io)
* [Documentation](https://docs.slidepack.io/)
    * [API reference](https://docs.slidepack.io/en/api-endpoints)
    * [Input file format reference](https://docs.slidepack.io/en/input-json)
    * [Rendering examples](https://docs.slidepack.io/en/examples)
