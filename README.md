# Sitemap Generator

This class uses simple XML syntax. It is prepared simply to create a sitemap. Supports creating sitemaps for Images, Videos and News...

It has been prepared using the document provided by Google to implement current standards ([https://developers.google.com/search/docs/advanced/sitemaps/overview](https://developers.google.com/search/docs/advanced/sitemaps/overview)).

## Requirements

- PHP 7.2 or higher
- PHP SimpleXML Extension
- PHP DOM Extension

## Installation

```
composer require muhammetsafak/sitemap-generator
```

## Usage

_**Note :** If you want to get the XML output as a string instead of writing it directly to a file; You can use the `getContent()` method._

_**Note :** If you want the generated XML output to be formatted, you can use the `setFormatOutput()` method._

### Standard Sitemap Generator

```php
require_once "vendor/autoload.php";
use \MuhammetSafak\SitemapGenerator\Generator;

$generator = new Generator();
$generator->setBaseURL('https://example.com/');

for ($i = 1; $i <= 3; ++$i) {
    $path = "/path/page/" . $i;
    $generator->addUrl($path, new DateTime(), [
        'changefreq'    => 'weekly',
        'priority'      => '0.6'
    ]);
}

$generator->save(__DIR__ . '/sitemap.xml', true);
$generator->clear();
```

The example above produces the following output;

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://example.com/path/page/1</loc>
        <lastmod>2022-04-26T19:07:09+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>https://example.com/path/page/2</loc>
        <lastmod>2022-04-26T19:07:09+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>https://example.com/path/page/3</loc>
        <lastmod>2022-04-26T19:07:09+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
</urlset>
```

Review the following example on identifying alternatives.

```php
require_once "vendor/autoload.php";
use \MuhammetSafak\SitemapGenerator\Generator;

$generator = new Generator();
$generator->setBaseURL('https://example.com/');

$generator->addAlternate('fr', 'https://example.com/fr/');
$generator->addAlternate('de', 'https://example.com/de/')

for ($i = 1; $i <= 3; ++$i) {
    $path = "/path/page/" . $i;
    $generator->addUrl($path, new DateTime(), [
        'changefreq'    => 'weekly',
        'priority'      => '0.6'
    ]);
}

$generator->save(__DIR__ . '/sitemap.xml', true);
$generator->clear();
```

The example above produces the following output;

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://example.com/path/page/1</loc>
        <lastmod>2022-04-26T19:07:09+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
        <xhtml:link rel="alternate" hreflang="fr" href="https://example.com/fr/path/page/1"/>
        <xhtml:link rel="alternate" hreflang="de" href="https://example.com/de/path/page/1"/>
    </url>
    <url>
        <loc>https://example.com/path/page/2</loc>
        <lastmod>2022-04-26T19:07:09+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
        <xhtml:link rel="alternate" hreflang="fr" href="https://example.com/fr/path/page/2"/>
        <xhtml:link rel="alternate" hreflang="de" href="https://example.com/de/path/page/2"/>
    </url>
    <url>
        <loc>https://example.com/path/page/3</loc>
        <lastmod>2022-04-26T19:07:09+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
        <xhtml:link rel="alternate" hreflang="fr" href="https://example.com/fr/path/page/3"/>
        <xhtml:link rel="alternate" hreflang="de" href="https://example.com/de/path/page/3"/>
    </url>
</urlset>
```

### Video Sitemap Generator

```php
require_once "vendor/autoload.php";
use \MuhammetSafak\SitemapGenerator\Generator;

$generator = new Generator(Generator::NEWS);
$generator->setBaseURL('https://example.com/');


$video = [
    'thumbnail'     => 'https://example.com/thumbs/1.jpg',
    'title'         => 'Video Title 1',
    'description'   => 'Video Description Value',
    'content_loc'   => 'https://example.com/videos/1.mp4',
    'player_loc'    => 'https://example.com/videoplayer.php?video=1',
    'duration'      => 600,
    'expiration_date'   => '2021-11-05T19:20:30+08:00', // or DateTimeInterface object
    'rating'        => '4.2',
    'view_count'    => 12345,
    'publication_date'  => '2012-11-05T19:20:30+08:00', // or DateTimeInterface object
    'family_friendly'   => true, // [true|false|"yes"|"no"]
    'platform'      => [
        'relationship'  => 'allow', // ["allow"|"deny"]
        'value'         => 'web mobil tv' // "web" "mobil" "tv"
    ],
    'restriction'   => [
        'relationship'  => 'allow', // ["allow"|"deny"]
        'value'         => 'IE GB US CA'
    ],
    'price'         => [
        'currency'  => 'EUR',
        'value'     => '1.99'
    ],
    'requires_subscription' => true, // [true|false|"yes"|"no"]
    'uploader'      => [
        'info'  => 'https://example.com/user/admin',
        'value' => 'Admin'
    ],
    'live'          => false, // [true|false|"yes"|"no"]
];

$generator->addUrl('/path/video/1', new DateTime(), $video);

$generator->save(__DIR__ . '/sitemap.xml', true);
$generator->clear();
```

The example above produces the following output;

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
   <url>
     <loc>https://example.com/path/video/1</loc>
     <video:video>
       <video:thumbnail_loc>https://example.com/thumbs/1.jpg</video:thumbnail_loc>
       <video:title>Video Title 1</video:title>
       <video:description>Video Description Value</video:description>
       <video:content_loc>https://example.com/videos/1.mp4</video:content_loc>
       <video:player_loc>https://example.com/videoplayer.php?video=1</video:player_loc>
       <video:duration>600</video:duration>
       <video:expiration_date>2021-11-05T19:20:30+08:00</video:expiration_date>
       <video:rating>4.2</video:rating>
       <video:view_count>12345</video:view_count>
       <video:publication_date>2007-11-05T19:20:30+08:00</video:publication_date>
       <video:family_friendly>yes</video:family_friendly>
       <video:platform relationship="allow">web mobil tv</video:platform>
       <video:restriction relationship="allow">IE GB US CA</video:restriction>
       <video:price currency="EUR">1.99</video:price>
       <video:requires_subscription>yes</video:requires_subscription>
       <video:uploader info="https://example.com/user/admin">Admin
       </video:uploader>
       <video:live>no</video:live>
     </video:video>
   </url>
</urlset>
```

### Image Sitemap Generator

```php
require_once "vendor/autoload.php";
use \MuhammetSafak\SitemapGenerator\Generator;

$generator = new Generator(Generator::NEWS);
$generator->setBaseURL('https://example.com/');

$generator->addUrl('/path/page/1', new DateTime(), [
    'image' => 'https://example.com/files/image1.jpg'
]);

$generator->addUrl('/path/page/2', new DateTime(), [
    'image' => [
        'https://example.com/files/image2.jpg',
        'https://example.com/files/image3.jpg',
        'https://example.com/files/image4.jpg'
    ]
]);

$generator->save(__DIR__ . '/sitemap.xml', true);
$generator->clear();
```

The example above produces the following output;

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>https://example.com/path/page/1</loc>
        <image:image>
            <image:loc>https://example.com/files/image1.jpg</image:loc>
        </image:image>
    </url>
    <url>
        <loc>https://example.com/path/page/2</loc>
        <image:image>
            <image:loc>https://example.com/files/image2.jpg</image:loc>
        </image:image>
        <image:image>
            <image:loc>https://example.com/files/image3.jpg</image:loc>
        </image:image>
        <image:image>
            <image:loc>https://example.com/files/image4.jpg</image:loc>
        </image:image>
    </url>
</urlset>
```

### News Sitemap Generator

```php
require_once "vendor/autoload.php";
use \MuhammetSafak\SitemapGenerator\Generator;

$generator = new Generator(Generator::NEWS);
$generator->setBaseURL('https://example.com/');

for ($i = 1; $i <= 3; ++$i) {
    $path = "/path/news/" . $i;
    $generator->addUrl($path, new DateTime(), [
        'publication'   => [
            'name'      => 'The Example Times',
            'language'  => 'en'
        ],
        'title'         => 'Headline Of Breaking News #' . $i,
    ]);
}

$generator->save(__DIR__ . '/sitemap.xml', true);
$generator->clear();
```

The example above produces the following output;

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    <url>
        <loc>https://example.com/path/news/1</loc>
        <news:news>
            <news:publication>
                <news:name>The Example Times</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>2022-06-22</news:publication_date>
            <news:title>Headline Of Breaking News #1</news:title>
        </news:news>
    </url>
    <url>
        <loc>https://example.com/path/news/2</loc>
        <news:news>
            <news:publication>
                <news:name>The Example Times</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>2022-06-22</news:publication_date>
            <news:title>Headline Of Breaking News #2</news:title>
        </news:news>
    </url>
    <url>
        <loc>https://example.com/path/news/3</loc>
        <news:news>
            <news:publication>
                <news:name>The Example Times</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>2022-06-22</news:publication_date>
            <news:title>Headline Of Breaking News #3</news:title>
        </news:news>
    </url>
</urlset>
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
