<?php
/**
 * Generator.php
 *
 * This file is part of SitemapGenerator.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    0.1
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace MuhammetSafak\SitemapGenerator;

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

    /** @var array */
    protected $data = [];

    /** @var int */
    protected $type = self::STANDARD;

    protected $mainUrl = null;

    protected $alternate = [];

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
        $this->clear();
        $this->mainUrl = null;
    }

    public function __toString()
    {
        return $this->getContent();
    }

    public function clear()
    {
        $this->data = [];
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
        $content = '<?xml version="' . $this->version . '" encoding="' . $this->encoding . '"?>';
        switch ($this->type) {
            case self::IMAGE :
                return $content . $this->createImageContent();
            case self::VIDEO :
                return $content . $this->createVideoContent();
            case self::NEWS :
                return $content . $this->createNewsContent();
            default:
                return $content . $this->createStandardContent();
        }
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

    private function createStandardContent(): string
    {
        $content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($this->data as $row) {
            $path = '/' . ltrim($row['loc'], "/");
            $content .= '<url>';
            $content .= '<loc>' . $this->mainUrl . $path . '</loc>';
            if(isset($row['lastmod'])){
                $content .= '<lastmod>' . $this->dateFormat($row['lastmod']) . '</lastmod>';
            }elseif(isset($row['date'])){
                $content .= '<lastmod>' . $this->dateFormat($row['date']) . '</lastmod>';
            }
            if(isset($row['changefreq'])){
                $content .= '<changefreq>' . $row['changefreq'] . '</changefreq>';
            }
            if(isset($row['priority'])){
                $content .= '<priority>' . $row['priority'] . '</priority>';
            }
            if(!empty($this->alternate)){
                foreach ($this->alternate as $alternate) {
                    $content .= '<xhtml:link rel="alternate" hreflang="' . $alternate['hreflang'] . '" href="' . ($alternate['href'] . $path) . '"/>';
                }
            }
            $content .= '</url>';
        }
        $content .= '</urlset>';
        return $content;
    }

    private function createVideoContent(): string
    {
        $content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';
        foreach ($this->data as $video) {
            $content .= '<url>';
            $content .= '<loc>' . $this->mainUrl . '/' . ltrim($video['loc'], "/") . '</loc>';
            $content .= '<video:video>';
            if(isset($video['thumbnail'])){
                $content .= '<video:thumbnail_loc>' . $video['thumbnail'] . '</video:thumbnail_loc>';
            }
            if(isset($video['title'])){
                $content .= '<video:title>' . $video['thumbnail'] . '</video:title>';
            }
            if(isset($video['description'])){
                $content .= '<video:description>' . $video['description'] . '</video:description>';
            }
            if(isset($video['content_loc'])){
                $content .= '<video:content_loc>' . $video['content_loc'] . '</video:content_loc>';
            }
            if(isset($video['player_loc'])){
                $content .= '<video:player_loc>' . $video['player_loc'] . '</video:player_loc>';
            }
            if(isset($video['duration'])){
                $content .= '<video:duration>' . $video['duration'] . '</video:duration>';
            }
            if(isset($video['expiration_date'])){
                if(($date = $video['expiration_date']) !== FALSE){
                    $content .= '<video:expiration_date>' . $date . '</video:expiration_date>';
                }
            }
            if(isset($video['rating'])){
                $content .= '<video:rating>' . $video['rating'] . '</video:rating>';
            }
            if(isset($video['view_count'])){
                $content .= '<video:view_count>' . $video['view_count'] . '</video:view_count>';
            }
            if(isset($video['publication_date'])){
                if(($date = $this->dateFormat($video['publication_date'])) !== FALSE){
                    $content .= '<video:publication_date>' . $date . '</video:publication_date>';
                }
            }
            if(isset($video['family_friendly'])){
                $content .= '<video:family_friendly>'
                        . (($video['family_friendly'] === FALSE || $video['family_friendly'] === 'no') ? 'no' : 'yes')
                        . '</video:family_friendly>';
            }
            if(isset($video['platform'])){
                $platform = $video['platform'];
                if(isset($platform['relationship']) && isset($platform['value'])){
                    $content .= '<video:platform relationship="' . $platform['relationship'] . '">' . $platform['value'] . '</video:platform>';
                }
            }
            if(isset($video['restriction'])){
                $restriction = $video['restriction'];
                if(isset($restriction['relationship']) && isset($restriction['value'])){
                    $content .= '<video:restriction relationship="' . $restriction['relationship'] . '">' . $restriction['value'] . '</video:restriction>';
                }
            }
            if(isset($video['price'])){
                $price = $video['price'];
                if(isset($price['currency']) && isset($price['value'])){
                    $content .= '<video:price currency="' . $price['currency'] . '">' . $price['value'] . '</video:price>';
                }
            }
            if(isset($video['requires_subscription'])){
                $content .= '<video:requires_subscription>'
                . (($video['requires_subscription'] === FALSE || $video['requires_subscription'] === 'no') ? 'no' : 'yes')
                    . '</video:requires_subscription>';
            }
            if(isset($video['uploader'])){
                $uploader = $video['uploader'];
                if(isset($uploader['info']) && isset($uploader['value'])){
                    $content .= '<video:uploader info="' . $uploader['info'] . '">' . $uploader['value'] . '</video:uploader>';
                }
            }
            if(isset($video['live'])){
                $content .= '<video:live>'
                . (($video['live'] === FALSE || $video['live'] === 'no') ? 'no' : 'yes')
                    . '</video:live>';
            }
            $content .= '</video:video>';
            $content .= '</url>';
        }
        $content .= '</urlset>';

        return $content;
    }

    private function createImageContent(): string
    {
        $content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        foreach ($this->data as $img) {
            $content .= '<url>';
            $content .= '<loc>' . $this->mainUrl . '/' . ltrim($img['loc'], "/") . '</loc>';
            if(is_array($img['image'])){
                foreach ($img['image'] as $image) {
                    $content .= '<image:image><image:loc>' . $image . '</image:loc></image:image>';
                }
            }else{
                $content .= '<image:image><image:loc>' . $img['image'] . '</image:loc></image:image>';
            }
            $content .= '</url>';
        }
        $content .= '</urlset>';

        return $content;
    }

    private function createNewsContent(): string
    {
        $content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
        foreach ($this->data as $new) {
            $content .= '<url>';
            $content .= '<loc>' . $this->mainUrl . '/' . ltrim($new['loc'], "/") . '</loc>';
            $content .= '<news:news>';
            if(isset($new['publication'])){
                $publication = $new['publication'];
                $content .= '<news:publication>';
                if(isset($publication['name'])){
                    $content .= '<news:name>' . $publication['name'] . '</news:name>';
                }
                if(isset($publication['language'])){
                    $content .= '<news:language>' . $publication['language'] . '</news:language>';
                }
                $content .= '</news:publication>';
            }
            if(isset($new['date']) && ($date = $this->dateFormat($new['date'], "Y-m-d") !== FALSE)){
                $content .= '<news:publication_date>' . $date . '</news:publication_date>';
            }
            if(isset($new['title'])){
                $content .= '<news:title>' . $new['title'] . '</news:title>';
            }
            $content .= '</news:news>'
                        .'</url>';
        }
        $content .= '</urlset>';

        return $content;
    }

}
