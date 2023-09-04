# Generate PPTX with SlidePack (NodeJS sample code)

This script uses `input.zip` as input, renders a presentation with SlidePack, and outputs to `output.pptx`.

## Prerequisites

* NodeJS >= 16
* Sign up for free and create your API token at https://slidepack.io/app

## Running the sample code

```bash
export SLIDEPACK_API_TOKEN="xxxxxxx"
npm install
node main.js
# => generates output.pptx
```

## More information

For more information about SlidePack, refer to:

* [The SlidePack Website](https://slidepack.io)
* [Documentation](https://docs.slidepack.io/)
    * [API reference](https://docs.slidepack.io/en/api-endpoints)
    * [Input file format reference](https://docs.slidepack.io/en/input-json)
    * [Rendering examples](https://docs.slidepack.io/en/examples)
