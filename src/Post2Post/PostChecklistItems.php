<?php

namespace WebTheory\Post2Post;

use WP_Query;

class PostChecklistItems
{
    /**
     * @var WP_Query
     */
    protected $query;

    /**
     * @var string
     */
    protected $idFormat = 'wts--%s';

    /**
     * @var string
     */
    protected $itemValue = '1';

    /**
     * @var string
     */
    protected $itemNameProperty = 'ID';

    /**
     * @var string
     */
    protected $itemNameFormat = '%s';

    /**
     *
     */
    public function __construct(WP_Query $query)
    {
        $this->query = clone $query;
    }

    /**
     * Get the value of idFormat
     *
     * @return string
     */
    public function getIdFormat(): string
    {
        return $this->idFormat;
    }

    /**
     * Set the value of idFormat
     *
     * @param string $idFormat
     *
     * @return self
     */
    public function setIdFormat(string $idFormat)
    {
        $this->idFormat = $idFormat;

        return $this;
    }

    /**
     * Get the value of itemValue
     *
     * @return string
     */
    public function getItemValue(): string
    {
        return $this->itemValue;
    }

    /**
     * Set the value of itemValue
     *
     * @param string $itemValue
     *
     * @return self
     */
    public function setItemValue(string $itemValue)
    {
        $this->itemValue = $itemValue;

        return $this;
    }

    /**
     * Get the value of itemNameProperty
     *
     * @return string
     */
    public function getItemNameProperty(): string
    {
        return $this->itemNameProperty;
    }

    /**
     * Set the value of itemNameProperty
     *
     * @param string $itemNameProperty
     *
     * @return self
     */
    public function setItemNameProperty(string $itemNameProperty)
    {
        $this->itemNameProperty = $itemNameProperty;

        return $this;
    }

    /**
     * Get the value of itemNameFormat
     *
     * @return string
     */
    public function getItemNameFormat(): string
    {
        return $this->itemNameFormat;
    }

    /**
     * Set the value of itemNameFormat
     *
     * @param string $itemNameFormat
     *
     * @return self
     */
    public function setItemNameFormat(string $itemNameFormat)
    {
        $this->itemNameFormat = $itemNameFormat;

        return $this;
    }

    /**
     *
     */
    protected function getPosts()
    {
        return $this->query->get_posts();
    }

    /**
     *
     */
    public function getSelection()
    {
        $items = [];

        foreach ($this->getPosts() as $post) {
            $items[$post->post_name] = [
                'value' => $this->itemValue,
                'label' => $post->post_title,
                // 'name' => (string) $post->ID,
                // 'id' => "wts--{$post->post_name}",

                'name' => sprintf($this->itemNameFormat, $post->{$this->itemNameProperty}),
                'id' => sprintf($this->idFormat, $post->post_name)
            ];
        }

        return $items;
    }
}
