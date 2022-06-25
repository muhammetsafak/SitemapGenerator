<?php
/**
 * Generator.php
 *
 * This file is part of SitemapGenerator.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    0.2
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace MuhammetSafak\SitemapGenerator;

use \SimpleXMLElement;
use \DOMDocument;

use function ltrim;
use function rtrim;
use function file_exists;
use function file_put_contents;
use function is_array;
use function in_array;

class Generator
{

    public const STANDARD = 1;
    public const IMAGE = 2;
    public const VIDEO = 3;
    public const NEWS = 4;

    private $supported_types = [
        self::STANDARD,
        self::IMAGE,
        self::VIDEO,
        self::NEWS
    ];

    protected $version = '1.0';
    protected $encoding = 'UTF-8';
    protected $formatOutput = false;

    /** @var array */
    protected $data = [];

    /** @var int */
    protected $type = self::STANDARD;

    protected $mainUrl = null;

    protected $alternate = [];

    /** @var SimpleXMLElement */
    protected $xml = null;

    public function __construct(int $type = self::STANDARD)
    {
        if (!in_array($type, $this->supported_types, true)) {
            throw new \InvalidArgumentException('Please specify a supported type.');
        }
        $this->type = $type;
    }

    public function __destruct()
    {
        $this->alternate = [];
        $this->type = self::STANDARD;
        $this->mainUrl = null;
        $this->clear();
    }

    public function __toString()
    {
        return $this->getContent();
    }

    public function clear(): self
    {
        $this->data = [];
        $this->xml = null;
        $this->formatOutput = false;
        return $this;
    }

    /**
     * @param bool $format
     * @return $this
     */
    public function setFormatOutput(bool $format = true): self
    {
        $this->formatOutput = $format;
        return $this;
    }

    /**
     * @param string $baseURL <p>Example: "http://example.com/"</p>
     * @return $this
     */
    public function setBaseURL(string $baseURL): self
    {
        $this->mainUrl = rtrim($baseURL, "/");
        return $this;
    }

    /**
     * @param string $hreflang <p>Example : "en", "fr", "tr"</p>
     * @param string $href <p>Example : "http://example.com/en/", "http://en.example.com/"</p>
     * @return $this
     */
    public function addAlternate(string $hreflang, string $href): self
    {
        $this->alternate[] = [
            'hreflang'  => $hreflang,
            'href'      => rtrim($href, "/")
        ];
        return $this;
    }

    /**
     * @param string $filepath
     * @param boolean $overwrite
     * @return bool
     */
    public function save(string $filepath, bool $overwrite = false): bool
    {
        if ($overwrite === FALSE && file_exists($filepath)) {
            return false;
        }
        if(@file_put_contents($filepath, $this->getContent()) === FALSE){
            throw new \RuntimeException('Failed to write file ' . $filepath . '.');
        }
        return true;
    }

    public function getContent(): string
    {
        $this->xml = new SimpleXMLElement('<urlset/>');
        $this->xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        switch ($this->type) {
            case self::STANDARD :
                $this->createStandardContent();
                break;
            case self::IMAGE :
                $this->createImageContent();
                break;
            case self::VIDEO :
                $this->createVideoContent();
                break;
            case self::NEWS :
                $this->createNewsContent();
                break;
        }
        $dom = new DOMDocument();
        $dom->loadXML($this->xml->asXML());
        $dom->preserveWhiteSpace = false;
        if($this->formatOutput === FALSE){
            $dom->formatOutput = true;
        }
        $dom->xmlVersion = $this->version;
        $dom->encoding = $this->encoding;
        return $dom->saveXML();
    }

    public function addUrl(string $path, $date, array $extensions = []): self
    {
        $data = [
            'loc'   => $path,
        ];
        if(($date = $this->dateFormat($date)) !== FALSE){
            $data['date'] = $date;
        }
        foreach ($extensions as $key => $value) {
            if($key === 'loc'){
                continue;
            }
            $data[$key] = $value;
        }
        $this->data[] = $data;
        return $this;
    }

    private function dateFormat($date, string $format = 'c')
    {
        if(empty($date)){
            return false;
        }
        if($date instanceof \DateTimeInterface){
            return $date->format($format);
        }
        return $date;
    }

    /**
     * @param SimpleXMLElement|null $url
     * @param string $path
     * @return void
     */
    private function appendAlternateUrlAttribute(?SimpleXMLElement &$url, string $path)
    {
        if ($url === null) {
            return;
        }
        if (empty($this->alternate)) {
            return;
        }
        foreach ($this->alternate as $alternate) {
            $alternateUrl = $url->addChild('xhtml:link');
            $alternateUrl->addAttribute('rel', 'alternate');
            $alternateUrl->addAttribute('hreflang', $alternate['hreflang']);
            $alternateUrl->addAttribute('href', ($alternate['href'] . $path));
        }
    }

    private function createStandardContent(): void
    {
        foreach ($this->data as $row) {
            $path = '/' . ltrim($row['loc'], "/");
            $url = $this->xml->addChild('url');
            $url->addChild('loc', ($this->mainUrl . $path));
            if(isset($row['lastmod']) || isset($row['date'])){
                $url->addChild('lastmod', $this->dateFormat(($row['lastmod'] ?? $row['date'])));
            }
            if(isset($row['changefreq'])){
                $url->addChild('changefreq', $row['changefreq']);
            }
            if(isset($row['priority'])){
                $url->addChild('priority', $row['priority']);
            }
            $this->appendAlternateUrlAttribute($url, $path);
        }
    }

    private function createVideoContent(): void
    {
        $this->xml->addAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');
        foreach ($this->data as $row) {
            $path = '/' . ltrim($row['loc'], "/");
            $url = $this->xml->addChild('url');
            $url->addChild('loc', ($this->mainUrl . $path));
            $video = $url->addChild('video:video');

            if(isset($row['thumbnail'])){
                $video->addChild('video:thumbnail_loc', $row['thumbnail']);
            }
            if(isset($row['title'])){
                $video->addChild('video:title', $row['title']);
            }
            if(isset($row['description'])){
                $video->addChild('video:description', $row['description']);
            }
            if(isset($row['content_loc'])){
                $video->addChild('video:content_loc', $row['content_loc']);
            }
            if(isset($row['player_loc'])){
                $video->addChild('video:player_loc', $row['player_loc']);
            }
            if(isset($row['duration'])){
                $video->addChild('video:duration', $row['duration']);
            }
            if(isset($row['expiration_date'])){
                if(($date = $this->dateFormat($row['expiration_date'])) !== FALSE){
                    $video->addChild('video:expiration_date', $date);
                }
            }
            if(isset($row['rating'])){
                $video->addChild('video:rating', $row['rating']);
            }
            if(isset($row['view_count'])){
                $video->addChild('video:view_count', $row['view_count']);
            }
            if(isset($row['publication_date'])){
                if(($date = $this->dateFormat($row['publication_date'])) !== FALSE){
                    $video->addChild('video:publication_date', $date);
                }
            }
            if(isset($row['family_friendly'])){
                $familyFriendly = (($row['family_friendly'] === FALSE || $row['family_friendly'] === 'no') ? 'no' : 'yes');
                $video->addChild('video:family_friendly', $familyFriendly);
            }
            if(isset($row['platform'])){
                $pf = $row['platform'];
                if(isset($pf['relationship'], $pf['value'])){
                    $platform = $video->addChild('video:platform', $pf['value']);
                    $platform->addAttribute('relationship', $pf['relationship']);
                }
            }
            if(isset($row['restriction'])){
                $rest = $row['restriction'];
                if(isset($rest['relationship'], $rest['value'])){
                    $restriction = $video->addChild('video:restriction', $rest['value']);
                    $restriction->addAttribute('relationship', $rest['relationship']);
                }
            }
            if(isset($row['price'])){
                $pr = $row['price'];
                if(isset($pr['currency'], $pr['value'])){
                    $price = $video->addChild('video:price', $pr['value']);
                    $price->addAttribute('currency', $pr['currency']);
                }
            }
            if(isset($row['requires_subscription'])) {
                $requires_subscription = ($row['requires_subscription'] === FALSE || $row['requires_subscription'] === 'no') ? 'no' : 'yes';
                $video->addChild('video:requires_subscription', $requires_subscription);
            }
            if(isset($row['uploader'])){
                $up = $row['uploader'];
                if(isset($up['info'], $up['value'])){
                    $uploader = $video->addChild('video:uploader', $up['value']);
                    $uploader->addAttribute('info', $up['info']);
                }
            }
            if(isset($row['live'])){
                $video->addChild('video:live', (($row['live'] === FALSE || $row['live'] === 'no') ? 'no' : 'yes'));
            }
            $this->appendAlternateUrlAttribute($url, $path);
        }
    }

    private function createImageContent(): void
    {
        $this->xml->addAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

        foreach ($this->data as $row) {
            $path = "/" . ltrim($row['loc'], "/");
            $url = $this->xml->addChild('url');
            $url->addChild('loc', $this->mainUrl . $path);
            if(is_array($row['image'])){
                foreach ($row['image'] as $img) {
                    $image = $url->addChild('image:image');
                    $image->addChild('image:loc', (string)$img);
                }
            }else{
                $image = $url->addChild('image:image');
                $image->addChild('image:loc', (string)$row['image']);
            }
            $this->appendAlternateUrlAttribute($url, $path);
        }
    }

    private function createNewsContent(): void
    {
        $this->xml->addAttribute('xmlns:news', 'http://www.google.com/schemas/sitemap-news/0.9');
        foreach ($this->data as $row) {
            $path = "/" . ltrim($row['loc'], "/");
            $url = $this->xml->addChild('url');
            $url->addChild('loc', $this->mainUrl . $path);
            $new = $url->addChild('news:news');
            if(isset($row['publication'])){
                $pub = $row['publication'];
                $publication = $new->addChild('news:publication');
                if(isset($pub['name'])){
                    $publication->addChild('news:name', $pub['name']);
                }
                if(isset($pub['language'])){
                    $publication->addChild('news:language', $pub['language']);
                }
            }
            if(isset($row['date']) && ($date = $this->dateFormat($row['date'])) !== FALSE){
                $new->addChild('news:publication_date', $date);
            }
            if(isset($row['title'])){
                $new->addChild('news:title', $row['title']);
            }
            $this->appendAlternateUrlAttribute($url, $path);
        }
    }

}
