<?php

namespace Tmv\WhatsApi\Message\Node;

interface NodeInterface
{

    /**
     * @param array $data
     * @param NodeFactory $factory
     * @return NodeInterface
     */
    public static function fromArray(array $data, NodeFactory $factory);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name);

    /**
     * @return string
     */
    public function getData();

    /**
     * @return array|NodeInterface[]
     */
    public function getChildren();

    /**
     * @param string $name
     * @return bool
     */
    public function hasChild($name);

    /**
     * @param string $name
     * @return NodeInterface
     */
    public function getChild($name);

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name);

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute($name, $value);

    /**
     * @return string
     */
    public function toString();

    /**
     * @return array
     */
    public function toArray();
}
