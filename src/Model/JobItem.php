<?php

namespace uuf6429\SOJobMap\Model;

use JsonSerializable;
use function is_array;

class JobItem implements JsonSerializable
{
    /**
     * @var string
     */
    protected $guid;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string[]
     */
    protected $categories;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string
     */
    protected $pubDate;

    /**
     * @var array|null
     */
    protected $coords;

    /**
     * @var null|string
     */
    protected $salary;

    /**
     * @var bool
     */
    protected $remote;

    /**
     * @var null|bool
     */
    protected $visaSponsor;

    /**
     * @var null|bool
     */
    protected $paidRelocation;

    /**
     * @var null|string
     */
    protected $company;

    /**
     * @param string $guid
     * @return $this
     */
    public function setGuid(string $guid): self
    {
        $this->guid = $guid;
        return $this;
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @param string $author
     * @return $this
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @param string[] $categories
     * @return $this
     */
    public function setCategories(array $categories): self
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $location
     * @return $this
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @param string $pubDate
     * @return $this
     */
    public function setPubDate(string $pubDate): self
    {
        $this->pubDate = $pubDate;
        return $this;
    }

    /**
     * @param array|string|float|null $lat
     * @param string|float|null $lng
     * @return $this
     */
    public function setCoords($lat = null, $lng = null): self
    {
        if (is_array($lat)) {
            [$lat, $lng] = $lat;
        }

        $this->coords = ($lat !== null && $lng !== null)
            ? ['lat' => (float)$lat, 'lng' => (float)$lng]
            : null;
        return $this;
    }

    /**
     * @param null|string $salary
     * @return $this
     */
    public function setSalary(?string $salary): self
    {
        $this->salary = $salary;
        return $this;
    }

    /**
     * @param bool $remote
     * @return $this
     */
    public function setRemote(bool $remote): self
    {
        $this->remote = $remote;
        return $this;
    }

    /**
     * @param null|bool $visaSponsor
     * @return $this
     */
    public function setVisaSponsor(?bool $visaSponsor): self
    {
        $this->visaSponsor = $visaSponsor;
        return $this;
    }

    /**
     * @param null|bool $paidRelocation
     * @return $this
     */
    public function setPaidRelocation(?bool $paidRelocation): self
    {
        $this->paidRelocation = $paidRelocation;
        return $this;
    }

    /**
     * @param null|string $company
     * @return $this
     */
    public function setCompany(?string $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
