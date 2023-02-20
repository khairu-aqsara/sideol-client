![Build And Testing](https://github.com/khairu-aqsara/sideol-client/actions/workflows/php.yml/badge.svg) [![codecov](https://codecov.io/gh/khairu-aqsara/sideol-client/branch/main/graph/badge.svg?token=3CQWVHH3T6)](https://codecov.io/gh/khairu-aqsara/sideol-client) [![Known Vulnerabilities](https://snyk.io/test/github/khairu-aqsara/sideol-client/badge.svg)](https://snyk.io/test/github/khairu-aqsara/sideol-client)

<p align="center">
    <h3 align="center">Sideol PHP Client</h3>
    <p align="center">A PHP client for interacting with Sideol Engine</p>
</p>

---
This package is a PHP client for Sideol,  API to interact with powerful tools like Chromium and LibreOffice for converting numerous document formats (HTML, Markdown, Word, Excel, etc.) into PDF files, and more!

## Quick Examples

You may convert a target URL to PDF and save it to a given directory:

```php
use Picsi\Sideolclient\Sideol;

// Converts a target URL to PDF and saves it to a given directory.
$filename = Sideol::save(
    Sideol::chromium($apiUrl)->url('https://my.url'), 
    $pathToSavingDirectory
);
```

You may also convert Office documents and merge them:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

// Converts Office documents to PDF and merges them.
$response = Sideol::send(
    Sideol::libreOffice($apiUrl)
        ->merge()
        ->convert(
            Stream::path($pathToDocx),
            Stream::path($pathToXlsx)
        )
);
```

## Requirement
This packages requires [Sideol](https://khairu-aqsara.net/sidoel), a Docker-powered stateless API for PDF files:

* [Docker](https://khairu-aqsara.net/sidoel/docs/get-started/docker)
* [Docker Compose](https://khairu-aqsara.net/sidoel/docs/get-started/docker-compose)
* [Kubernetes](https://khairu-aqsara.net/sidoel/docs/get-started/kubernetes)
* [Cloud Run](https://khairu-aqsara.net/sidoel/docs/get-started/cloud-run)

## Installation
This package can not be installed with Composer, it's private repository. Once you download the repo you could install it using composer with setting below.

```
{
    "repositories": [
        {
            "type" : "path",
            "url" : "./sidoelclient"
        }   
    ],
    "require": {
        "picsi/sideolclient" : "*"
    },
}
```

I use *PSR-7* HTTP message interfaces (i.e., `RequestInterface` and `ResponseInterface`) and the *PSR-18* HTTP client
interface (i.e., `ClientInterface`).
For the latter, you may need an adapter in order to use your favorite client library. Check the available adapters:

* https://docs.php-http.org/en/latest/clients.html

If you're not sure which adapter you should use, consider using the `php-http/guzzle7-adapter`:
```
composer require php-http/guzzle7-adapter
```

## Usage

* [Send a request to the API](#send-a-request-to-the-api)
* [Chromium](#chromium)
* [LibreOffice](#libreOffice)
* [PDF Engines](#pdf-engines)
* [Webhook](#webhook)

### Send a request to the API

After having created the HTTP request (see below), you have two options:

1. Get the response from the API and handle it according to your need.
2. Save the resulting file to a given directory.

> In the following examples, we assume the Sideol Engine API is available at http://localhost:3000.

#### Get a response

You may use any HTTP client that is able to handle a *PSR-7* `RequestInterface` to call the API:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium('http://localhost:3000')
    ->url('https://my.url');
    
$response = $client->sendRequest($request);
```
If you have a *PSR-18* compatible HTTP client (see [Installation](#installation)), you may also use `Sideol::send`:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium('http://localhost:3000')
    ->url('https://my.url');

try {
    $response = Sideol::send($request);
    return $response;
} catch (SideolApiError $e) {
    // $e->getResponse();
}
```
This helper will parse the response and if it is not **2xx**, it will throw an exception. That's especially useful if 
you wish to return the response directly to the browser.

You may also explicitly set the HTTP client:

```php
use  Picsi\Sideolclient\Sideol;

$response = Sideol::send($request, $client);
```

#### Save the resulting file

If you have a *PSR-18* compatible HTTP client (see [Installation](#installation)), you may use `Sideol::save`:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium('http://localhost:3000')
    ->url('https://my.url');
    
$filename = Sideol::save($request, '/path/to/saving/directory');
```

It returns the filename of the resulting file. By default, Sideol creates a *UUID* filename (i.e., 
`36a1558b-02a5-4a45-a950-2f8f8fd7f644`) with either a `.zip` or a `.pdf` file extension.

You may also explicitly set the HTTP client:

```php
use Picsi\Sideolclient\Sideol;

$response = Sideol::save($request, $pathToSavingDirectory, $client);
```

#### Filename

You may override the output filename with:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium('http://localhost:3000')
    ->outputFilename('my_file')
    ->url('https://my.url');
```

Sideol will automatically add the correct file extension.

#### Trace or request ID

By default, Sideol creates a *UUID* trace that identifies a request in its logs. You may override its value thanks to:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium('http://localhost:3000')
    ->trace('debug')
    ->url('https://my.url');
```

It will set the header `Sideol-Trace` with your value. You may also override the default header name:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium('http://localhost:3000')
    ->trace('debug', 'Request-Id')
    ->url('https://my.url');
```

Please note that it should be the same value as defined by the `--api-trace-header` Sideol's property.

The response from Sideol will also contain the trace header. In case of error, both the `Sideol::send` and  `Sideol::save` methods throw a `SideolApiError` exception that provides the following method for retrieving the trace:

```php
use Picsi\Sideolclient\Exceptions\SideolApiError;
use Picsi\Sideolclient\Sideol;

try {
    $response = Sideol::send(
        Sideol::chromium('http://localhost:3000')
            ->url('https://my.url')
    );
} catch (SideolApiError $e) {
    $trace = $e->getSideolTrace();
    // Or if you override the header name:
    $trace = $e->getSideolTrace('Request-Id');
}
```

### Chromium

The [Chromium module](https://khairu-aqsara.net/sideol/docs/modules/chromium) interacts with the Chromium browser to convert HTML documents to PDF.

#### Convert a target URL to PDF
See https://khairu-aqsara.net/sideol/docs/modules/chromium#url.

Converting a target URL to PDF is as simple as:
```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->url('https://my.url');
```

You may inject `<link>` and `<script>` HTML elements thanks to the `$extraLinkTags` and `$extraScriptTags` arguments:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Modules\ChromiumExtraLinkTag;
use Picsi\Sideolclient\Modules\ChromiumExtraScriptTag;

$request = Sideol::chromium($apiUrl)->url('https://my.url',
        [
            new ChromiumExtraLinkTag('https://my.css'),
        ],
        [
            new ChromiumExtraScriptTag('https://my.js'),
        ],
    );
```

Please note that Sideol will add the `<link>` and `<script>` elements based on the order of the arguments.

#### Convert an HTML document to PDF

See https://khairu-aqsara.net/sideol/docs/modules/chromium#html.

You may convert an HTML document with:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::chromium($apiUrl)
    ->html(Stream::path('/path/to/file.html'));
```

Or with an HTML string:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::chromium($apiUrl)
    ->html(Stream::string('my.html', $someHtml));
```

Please note that it automatically sets the filename to `index.html`, as required by Sideol, whatever the value you're using with the `Stream` class.

You may also send additional files, like images, fonts, stylesheets, and so on. The only requirement is that their paths in the HTML DOM are on the root level.

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::chromium($apiUrl)
    ->assets(
        Stream::path('/path/to/my.css'),
        Stream::path('/path/to/my.js')
    )
    ->html(Stream::path('/path/to/file.html'));
```

#### Convert one or more markdown files to PDF

See https://khairu-aqsara.net/sideol/docs/modules/chromium#markdown.

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::chromium($apiUrl)
    ->markdown(
        Stream::path('/path/to/my_wrapper.html'),
        Stream::path('/path/to/file.md')
    );
```

The first argument is a `Stream` with HTML content, for instance:

```html
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>My PDF</title>
  </head>
  <body>
    {{ toHTML "file.md" }}
  </body>
</html>
```

Here, there is a Go template function `toHTML`. Sideol will use it to convert a markdown file's content to HTML.

Like the HTML conversion, you may also send additional files, like images, fonts, stylesheets, and so on. The only requirement is that their paths in the HTML DOM are on the root level.

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::chromium($apiUrl)
    ->assets(
        Stream::path('/path/to/my.css'),
        Stream::path('/path/to/my.js')
    )
    ->markdown(
        Stream::path('/path/to/file.html'),
        Stream::path('/path/to/my.md'),
        Stream::path('/path/to/my2.md')
    );
```

#### Paper size

You may override the default paper size with:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->paperSize($width, $height)
    ->url('https://my.url');
```

Examples of paper size (width x height, in inches):

* `Letter` - 8.5 x 11 (default)
* `Legal` - 8.5 x 14
* `Tabloid` - 11 x 17
* `Ledger` - 17 x 11
* `A0` - 33.1 x 46.8
* `A1` - 23.4 x 33.1
* `A2` - 16.54 x 23.4
* `A3` - 11.7 x 16.54
* `A4` - 8.27 x 11.7
* `A5` - 5.83 x 8.27
* `A6` - 4.13 x 5.83

#### Margins

You may override the default margins (i.e., `0.39`, in inches):

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->margins($top, $bottom, $left, $right)
    ->url('https://my.url');
```

#### Prefer CSS page size

You may force page size as defined by CSS:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->preferCssPageSize()
    ->url('https://my.url');
```

#### Print the background graphics

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->printBackground()
    ->url('https://my.url');
```

You may also hide the default white background and allow generating PDFs with transparency with:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->printBackground()
    ->omitBackground()
    ->url('https://my.url');
```

The rules regarding the `printBackground` and `omitBackground` form fields are the following:

* If `printBackground` is set to *false*, no background is printed.
* If `printBackground` is set to *true*:
    * If the HTML document has a background, that background is used.
    * If not:
        * If `omitBackground` is set to *true*, the default background is transparent.
        * If not, the default white background is used.

#### Landscape orientation

You may override the default portrait orientation with:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->landscape()
    ->url('https://my.url');
```

#### Scale

You may override the default scale of the page rendering (i.e., `1.0`) with:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->scale(2.0)
    ->url('https://my.url');
```

#### Page ranges

You may set the page ranges to print, e.g., `1-5, 8, 11-13`. Empty means all pages.

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->nativePageRanges('1-2')
    ->url('https://my.url');
```

#### Header and footer

You may add a header and/or a footer to each page of the PDF:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::chromium($apiUrl)
    ->header(Stream::path('/path/to/my_header.html'))
    ->footer(Stream::path('/path/to/my_footer.html'))
    ->margins(1, 1, 0.39, 0.39)
    ->url('https://my.url');
```

Please note that it automatically sets the filenames to `header.html` and `footer.html`, as required by Sideol,  whatever the value you're using with the `Stream` class. Each of them has to be a complete HTML document:

```html
<html>
<head>
    <style>
    body {
        font-size: 8rem;
        margin: 4rem auto;
    }
    </style>
</head>
<body>
<p><span class="pageNumber"></span> of <span class="totalPages"></span></p>
</body>
</html>
```

The following classes allow you to inject printing values:

* `date` - formatted print date.
* `title` - document title.
* `url` - document location.
* `pageNumber` - current page number.
* `totalPages` - total pages in the document.

⚠️ Make sure that:

1. Margins top and bottom are large enough (i.e., `->margins(1, 1, 0.39, 0.39)`)
2. The font size is big enough.

⚠️ There are some limitations:

* No JavaScript.
* The CSS properties are independent of the ones from the HTML document.
* The footer CSS properties override the ones from the header;
* Only fonts installed in the Docker image are loaded - see the [Fonts chapter](https://khairu-aqsara.net/sideol/docs/customize/fonts).
* Images only work using a base64 encoded source - i.e., `data:image/png;base64, iVBORw0K....`
* `background-color` and color `CSS` properties require an additional `-webkit-print-color-adjust: exact` CSS property in order to work.
* Assets are not loaded (i.e., CSS files, scripts, fonts, etc.).
* Background form fields do not apply.

#### Wait delay

When the page relies on JavaScript for rendering, and you don't have access to the page's code, you may want to wait a certain amount of time (i.e., `1s`, `2ms`, etc.) to make sure Chromium has fully rendered the page you're trying to generate.

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->waitDelay('3s')
    ->url('https://my.url');
```

#### Wait for expression

You may also wait until a given JavaScript expression returns true:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->waitForExpression("window.status === 'ready'")
    ->url('https://my.url');
```

#### User agent

You may override the default `User-Agent` header used by Sideol:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->userAgent("Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1")
    ->url('https://my.url');
```

#### Extra HTTP headers

You may add HTTP headers that Chromium will send when loading the HTML document:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->extraHttpHeaders([
        'My-Header-1' => 'My value',
        'My-Header-2' => 'My value'
    ])
    ->url('https://my.url');
```

#### Fail on console exceptions

You may force Sideol to return a `409 Conflict` response if there are exceptions in the Chromium console:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->failOnConsoleExceptions()
    ->url('https://my.url');
```

#### Emulate media type

Some websites have dedicated CSS rules for print. Using `screen` allows you to force the "standard" CSS rules:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->emulateScreenMediaType()
    ->url('https://my.url');
```

You may also force the `print` media type with:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->emulatePrintMediaType()
    ->url('https://my.url');
```

#### PDF Format

See https://khairu-aqsara.net/sidoel/docs/modules/pdf-engines#engines.

You may set the PDF format of the resulting PDF with:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->pdfFormat('PDF/A-1a')
    ->url('https://my.url');
```
### LibreOffice

The [LibreOffice module](https://khairu-aqsara.net/sideol/docs/modules/libreoffice) interacts with [LibreOffice](https://www.libreoffice.org/) 
to convert documents to PDF, thanks to [unoconv](https://github.com/unoconv/unoconv).

#### Convert documents to PDF

See https://khairu-aqsara.net/sideol/docs/modules/libreoffice#route.
Converting a document to PDF is as simple as:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::libreOffice($apiUrl)
    ->convert(Stream::path('/path/to/my.docx'));
```

If you send many documents, Sideol will return a ZIP archive with the PDFs:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::libreOffice($apiUrl)
    ->outputFilename('archive')
    ->convert(
        Stream::path('/path/to/my.docx'),
        Stream::path('/path/to/my.xlsx')
    );

// $filename = archive.zip
$filename = Sideol::save($request, $pathToSavingDirectory);
```
You may also merge them into one unique PDF:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::libreOffice($apiUrl)
    ->merge()
    ->outputFilename('merged')
    ->convert(
        Stream::path('/path/to/my.docx'),
        Stream::path('/path/to/my.xlsx')
    );

// $filename = merged.pdf
$filename = Sideol::save($request, $pathToSavingDirectory);
```
Please note that the merging order is determined by the order of the arguments.

#### Landscape orientation

You may override the default portrait orientation with:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::libreOffice($apiUrl)
    ->landscape()
    ->convert(Stream::path('/path/to/my.docx'));
```

#### Page ranges

You may set the page ranges to print, e.g., `1-4`. Empty means all pages.

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::libreOffice($apiUrl)
    ->nativePageRanges('1-2')
    ->convert(Stream::path('/path/to/my.docx'));
```

⚠️ The page ranges are applied to all files independently.

#### PDF format

See https://khairu-aqsara.net/sideol/docs/modules/pdf-engines#engines.

You may set the PDF format of the resulting PDF(s) with:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::libreOffice($apiUrl)
    ->pdfFormat('PDF/A-1a')
    ->convert(Stream::path('/path/to/my.docx'));
```

You may also explicitly tell Sideol to use [unoconv](https://github.com/unoconv/unoconv) to convert the resulting PDF(s) to a PDF format:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::libreOffice($apiUrl)
    ->nativePdfFormat('PDF/A-1a')
    ->convert(Stream::path('/path/to/my.docx'));
```

⚠️ You cannot set both property, otherwise Sideol will return `400 Bad Request` response.

### PDF Engines

The [PDF Engines module](https://khairu-aqsara.net/sideol/docs/modules/pdf-engines) gathers all engines that can manipulate PDF files.

#### Merge PDFs

See https://khairu-aqsara.net/sideol/docs/modules/pdf-engines#merge.

Merging PDFs is as simple as:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::pdfEngines($apiUrl)
    ->merge(
        Stream::path('/path/to/my.pdf'),
        Stream::path('/path/to/my2.pdf')
    );
```

Please note that the merging order is determined by the order of the arguments.

You may also set the PDF format of the resulting PDF(s) with:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::pdfEngines($apiUrl)
    ->pdfFormat('PDF/A-1a')
    ->merge(
        Stream::path('/path/to/my.pdf'),
        Stream::path('/path/to/my2.pdf'),
        Stream::path('/path/to/my3.pdf')
    );
```

#### Convert to a specific PDF format

See https://khairu-aqsara.net/sideol/docs/modules/pdf-engines#convert.

You may convert a PDF to a specific PDF format with:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::pdfEngines($apiUrl)
    ->convert(
        'PDF/A-1a'
        Stream::path('/path/to/my.pdf')
    );
```

If you send many PDFs, Sideol will return a ZIP archive with the PDFs:

```php
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Stream;

$request = Sideol::pdfEngines($apiUrl)
    ->outputFilename('archive')
    ->convert(
        'PDF/A-1a',
        Stream::path('/path/to/my.pdf'),
        Stream::path('/path/to/my2.pdf'),
        Stream::path('/path/to/my3.pdf')
    );

// $filename = archive.zip
$filename = Sideol::save($request, $pathToSavingDirectory);
```

### Webhook

The [Webhook module](https://khairu-aqsara.net/sideol/docs/modules/webhook) is a Sideol middleware that sends the API
responses to callbacks.

⚠️ You cannot use the `Sideol::save` method if you're using the webhook feature.
For instance:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->webhook('https://my.webhook.url', 'https://my.webhook.error.url')
    ->url('https://my.url'); 
```

You may also override the default HTTP method (`POST`) that Sideol will use to call the webhooks:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->webhook('https://my.webhook.url', 'https://my.webhook.error.url')
    ->webhookMethod('PATCH')
    ->webhookErrorMethod('PUT')
    ->url('https://my.url');
```

You may also tell Sideol to add extra HTTP headers that it will send alongside the request to the webhooks:

```php
use Picsi\Sideolclient\Sideol;

$request = Sideol::chromium($apiUrl)
    ->webhook('https://my.webhook.url', 'https://my.webhook.error.url')
    ->webhookExtraHttpHeaders([
        'My-Header-1' => 'My value',
        'My-Header-2' => 'My value'    
    ])
    ->url('https://my.url');
```
