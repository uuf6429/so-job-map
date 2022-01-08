<?php

namespace uuf6429\SOJobMap\Service;

use DOMDocument;
use ErrorException;
use Geocoder\Exception\Exception as GeocodeException;
use Geocoder\Provider\Provider as Geocoder;
use Geocoder\Query\GeocodeQuery;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;
use SimpleXMLElement;
use uuf6429\SOJobMap\Model\JobItem;

class FeedReader
{
    private const ALLOWS_REMOTE = ' (allows remote)';

    /**
     * @var Geocoder
     */
    protected $geocoder;

    protected $feedUrl = 'https://stackoverflow.com/jobs/feed';

    /**
     * @var CacheInterface
     */
    protected $cache;

    public function __construct(Geocoder $geocoder, CacheInterface $cache)
    {
        $this->geocoder = $geocoder;
        $this->cache = $cache;
    }

    /**
     * @param string $query
     *
     * @return JobItem[]
     */
    public function read(string $query = ''): array
    {
        $feedUrl = $this->feedUrl;

        if ($query) {
            $feedUrl .= '?tl=' . urlencode($query);
        }

        $feed = simplexml_load_string(file_get_contents($feedUrl));

        return array_map(
            function (SimpleXMLElement $item) {
                $guid = (string)$item->guid;
                $link = (string)$item->link;
                $categories = (array)$item->category;
                $title = (string)$item->title;
                $description = (string)$item->description;
                $pubDate = (string)$item->pubDate;
                $location = (string)$item->location;

                [$title, $isRemote, $company] = $this->parseTitle($title);

                return (new JobItem())
                    ->setGuid($guid)
                    ->setLink($link)
                    ->setCategories($categories)
                    ->setTitle($title)
                    ->setDescription($this->cleanDescription($description))
                    ->setPubDate($pubDate)
                    ->setLocation($location)
                    ->setCoords($this->getCoordinates($location))
                    ->setSalary(null)
                    ->setRemote($isRemote)
                    ->setVisaSponsor(null)
                    ->setPaidRelocation(null)
                    ->setCompany($company);
            },
            iterator_to_array($feed->channel->item, 0)
        );
    }

    /**
     * @param string $description
     * @return string
     */
    private function cleanDescription(string $description): string
    {
        // The description contains "<br>" and "<br />", but it seems that
        // "<br />" were inserted to look nicer on a feed reader.
        return str_replace('<br />', '', $description);
    }

    /**
     * @param string $title
     * @return array
     */
    private function parseTitle(string $title): array
    {
        // remove "allows remote"
        $parsed = str_replace(self::ALLOWS_REMOTE, '', $title);
        // remove location
        $parsed = preg_replace('/ \\([^)]*\\)$/', '', $parsed);
        // parse company
        $parsed = explode(' at ', $parsed);

        return [
            $parsed[0],                                         // title (or what remains of it)
            strpos($title, self::ALLOWS_REMOTE) !== false,      // if remote is allowed
            $parsed[1] ?? null                                  // company (if any)
        ];
    }

    /**
     * @param string $title
     * @return string
     */
    private function cleanTitle(string $title): string
    {
        // remove known tags
        $title = str_replace(self::ALLOWS_REMOTE, '', $title);

        // remove variable tag (location, usually)
        return preg_replace('/ \\([^)]*\\)$/', '', $title);
    }

    /**
     * @param string $title
     * @return bool
     */
    private function isRemote(string $title): bool
    {
        return strpos($title, self::ALLOWS_REMOTE) !== false;
    }

    /**
     * @param string $address
     * @return array
     */
    private function getCoordinates(string $address): array
    {
        $address = strtr(
            trim($address),
            [
                'Österreich' => 'Austria',
                'Deutschland' => 'Deutschland',
            ]
        );

        if (!$address) {
            return [null, null];
        }

        try {
            $result = $this->geocoder->geocodeQuery(GeocodeQuery::create($address));

            $coords = $result->first()->getCoordinates();

            return [
                $coords ? $coords->getLatitude() : null,
                $coords ? $coords->getLongitude() : null
            ];
        } catch (GeocodeException $error) {
            error_log($error);

            return [null, null];
        }
    }

    /**
     * @param string $link
     * @return string
     *
     * @todo Not used, until cache/api-rate mess is fixed
     */
    private function getSalary(string $link): string
    {
        libxml_use_internal_errors(true);

        try {
            $key = 'salary-' . sha1($link);

            if (($salary = $this->cache->get($key)) !== null) {
                return $salary;
            }

            $doc = new DOMDocument();
            @$doc->loadHTMLFile($link);
            if (!($page = simplexml_import_dom($doc))) {
                $error = libxml_get_last_error();
                throw new ErrorException($error->message, $error->code, $error->level, $error->file, $error->line);
            }

            $salary = trim((string)$page->xpath('//div[@id="content"]//header//span[@class="-salary pr16"]')[0]);

            $this->cache->set($key, $salary);

            return $salary;
        } catch (ErrorException|CacheException $exception) {
            error_log($exception);
            return '';
        } finally {
            libxml_use_internal_errors(false);
        }
    }
}
