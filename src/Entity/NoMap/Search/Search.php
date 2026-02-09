<?php

// src/Entity/NoMap/Search/Search.php

namespace App\Entity\NoMap\Search;

class Search implements \Stringable
{
    /**
     * @var string
     */
    public $q;

    /**
     * @var int
     */
    public $search_id;

    /**
     * @var int
     */
    public $search_product;

    /**
     * @var array
     */
    public $search_brands = [];

    /**
     * @var array
     */
    public $search_categories = [];

    /**
     * @var array
     */
    public $search_occasions = [];

    /**
     * @var array
     */
    public $search_types = [];

    /**
     * @var array
     */
    public $search_colors = [];

    /**
     * @var array
     */
    public $search_fabrics = [];

    /**
     * @var array
     */
    public $search_textures = [];

    /**
     * @var array
     */
    public $search_tags = [];

    /**
     * @var array
     */
    public $search_suppliers = [];

    /**
     * @var string
     */
    public $search_country;

    /**
     * @var bool
     */
    public $_exact;

    /**
     * @var bool
     */
    public $_id;

    /**
     * @var DateTime
     */
    public $search_start_date;

    /**
     * @var DateTime
     */
    public $search_end_date;

    public function search(): ?string
    {
        return $this->q;
    }

    public function getId(): ?int
    {
        return $this->search_id;
    }

    public function product(): ?int
    {
        return $this->search_product;
    }

    public function brands(): ?array
    {
        return $this->search_brands;
    }

    public function categories(): ?array
    {
        return $this->search_categories;
    }

    public function occasions(): ?array
    {
        return $this->search_occasions;
    }

    public function types(): ?array
    {
        return $this->search_types;
    }

    public function colors(): ?array
    {
        return $this->search_colors;
    }

    public function fabrics(): ?array
    {
        return $this->search_fabrics;
    }

    public function textures(): ?array
    {
        return $this->search_textures;
    }

    public function tags(): ?array
    {
        return $this->search_tags;
    }

    public function suppliers(): ?array
    {
        return $this->search_suppliers;
    }

    public function country(): ?string
    {
        return $this->search_country;
    }

    public function isExact(): ?bool
    {
        return $this->_exact;
    }

    public function searchID(): ?bool
    {
        return $this->_id;
    }

    public function startDate()
    {
        return $this->search_start_date;
    }

    public function endDate()
    {
        return $this->search_end_date;
    }

    public function __toString(): string
    {
        try {
            return (string) $this->q;
        } catch (Exception) {
            return '';
        }
    }
}
