<?php

namespace uuf6429\SOJobMap\Service;

use ArrayAccess;
use RuntimeException;

class Config implements ArrayAccess
{
    /**
     * @var array
     */
    protected $data;

    public function __construct()
    {
        $this->data = file_exists(__DIR__ . '../../config.php')
            ? include __DIR__ . '../../config.php'
            : include __DIR__ . '../../config.dist.php';
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        throw new RuntimeException('Method ' . __FUNCTION__ . ' not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Method ' . __FUNCTION__ . ' not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Method ' . __FUNCTION__ . ' not implemented.');
    }
}
