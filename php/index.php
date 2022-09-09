<?php
require 'vendor/autoload.php';

class SlidePackClient {
    private $endpoint;
    private $token;
    private $guzzle;

    function __construct($token, $endpoint = 'https://slidepack.io') {
        $this->token = $token;
        $this->endpoint = $endpoint;
        $this->guzzle = new GuzzleHttp\Client();
    }

    /**
     * Create a rendering session.
     */
    public function createSession() {
        try {
            $response = $this->guzzle->request('POST', "{$this->endpoint}/sessions", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->token}",
                ]
            ]);

            if ($response->getStatusCode() != 201) {
                throw new \Exception("Request failed with {$response->getStatusCode()}: {$response->getBody()->getContents()}");
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch(Throwable $e) {
            throw new \Exception("Failed to create session", 0, $e);
        }
    }

    /**
     * Upload zip file for rendering.
     * @param array $session Session data returned from createSession()
     * @param string $path Zip file path
     */
    public function uploadZip($session, $path) {
        try {
            $data = [];
            foreach ($session['upload']['params'] as $key => $value) {
                $data[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }

            $data[] = [
                'name' => 'file',
                'contents' => GuzzleHttp\Psr7\Utils::tryFopen($path, 'r'),
            ];

            $response = $this->guzzle->request('POST', $session['upload']['action'], [
                'multipart' => $data,
            ]);

            if ($response->getStatusCode() != 204) {
                throw new \Exception("Request failed with {$response->getStatusCode()}: {$response->getBody()->getContents()}");
            }

            return $response;
        } catch (Throwable $e) {
            throw new \Exception("Failed to upload zip", 0, $e);
        }
    }


    /**
     * Render the uploaded zip into PPTX.
     * @param array $session Session data returned from createSession()
     */
    public function render($session) {
        try {
            $response = $this->guzzle->request('POST', "{$this->endpoint}/sessions/{$session['session']['uuid']}/render", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$this->token}",
                ]
            ]);

            if ($response->getStatusCode() != 200) {
                throw new \Exception("Request failed with {$response->getStatusCode()}: {$response->getBody()->getContents()}");
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $e) {
            throw new \Exception("Failed to upload zip", 0, $e);
        }
    }
}

$token = getenv('SLIDEPACK_API_TOKEN');

if (!$token) {
    exit('Please set SLIDEPACK_API_TOKEN environment variable.');
}

$error = null;

try {
    if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $client = new SlidePackClient($token);
        $session = $client->createSession();
        $client->uploadZip($session, $_FILES['file']['tmp_name']);
        $result = $client->render($session);
        header("Location: {$result['download_url']}");
        exit();
    }
} catch (Throwable $e) {
    $error = $e;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SlidePack PHP example</title>
</head>

<body>
    <main>
        <?php if ($error): ?>
            <div class="error">
                <h2>Error</h2>
                <pre><?= htmlspecialchars($error) ?></pre>
            </div>
        <?php else: ?>
            <div class="card">
                <h1>SlidePack PHP Example</h1>
                <form action="/" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="foo" value="FOO" />
                    <div class="file-field">
                        <label>Choose input zip:</label>
                        <input type="file" name="file" accept=".zip" />
                    </div>
                    <button type="submit">Upload and render</button>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            background: #fbfbfb;
            color: #333;
            line-height: 1.5;
        }

        main {
            max-width: 650px;
            margin: 50px auto;
            padding: 15px;
        }

        .card {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 30px;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 30px;
            padding: 0;
        }

        input[type="file"] {
            display: block;
            margin-top: 5px;
            font-size: inherit;
        }

        button {
            margin-top: 30px;
            border: none;
            border-radius: 5px;
            font-size: inherit;
            padding: 10px 20px;
            background: #4f46e5;
            color: #fff;
        }

        button:hover {
            background: #6366f1;
        }

        .error {
            background: #eee;
            color: #333;
            border-radius: 5px;
            padding: 15px;
        }
        
        .error h2 {
            font-size: 16px;
            margin: 0 0 15px;
            padding: 0;
        }

        .error pre {
            margin: 0;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</body>

</html>
