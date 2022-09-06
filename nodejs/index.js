'use strict';

let axios = require('axios');
let fs = require('fs');
let FormData = require('form-data');

let {
  SLIDEPACK_API_TOKEN
} = process.env;

let SLIDEPACK_API_ENDPOINT = "https://slidepack.io";
let input = './template.zip';
let output = './output.pptx';

(async function () {
  try {
    // Create a session
    let sessionResponse = await axios({
      method: 'post',
      url: `${SLIDEPACK_API_ENDPOINT}/sessions`,
      headers: {
        'Authorization': `Bearer ${SLIDEPACK_API_TOKEN}`
      }
    }).catch(e => {
      throw Error(`ERROR: Failed to create session: ${e.message}`, { cause: e });
    });

    let session = sessionResponse.data;

    // Upload local zip file using multipart/form-data
    let form = new FormData();
    for (let key in session.upload.params) {
      form.append(key, session.upload.params[key]);
    }
    form.append('file', fs.createReadStream(input));
    let length = await getFormDataLength(form);

    await axios({
      method: session.upload.method,
      url: session.upload.action,
      data: form,
      headers: {
        'Content-Length': length,
      },
    }).catch(e => {
      throw Error(`ERROR: Failed to upload zip file: ${e.message}`, { cause: e });
    });

    // Render PowerPoint
    let renderResponse = await axios({
      method: 'POST',
      url: `${SLIDEPACK_API_ENDPOINT}/sessions/${session.session.uuid}/render`,
      headers: {
        'Authorization': `Bearer ${SLIDEPACK_API_TOKEN}`
      }
    }).catch(e => {
      throw Error(`ERROR: Failed to render: ${e.message}`, { cause: e });
    });

    // Download rendered pptx
    let downloadResponse = await axios({
      method: 'get',
      url: renderResponse.data.download_url,
      responseType: 'stream',
    });
    downloadResponse.data.pipe(fs.createWriteStream(output));

    console.log(`Rendered to ${output}`);
  } catch (e) {
    console.error(e.message);
  }
})();

async function getFormDataLength(formData) {
  return new Promise((resolve, reject) => {
    formData.getLength((err, length) => {
      err ? reject(err) : resolve(length);
    });
  });
}
