<?php

namespace Tmv\WhatsApi\Message\Node;

use Tmv\WhatsApi\Exception\InvalidArgumentException;

class NodeFactory
{

    /**
     * @param  array                    $data
     * @return Node
     * @throws InvalidArgumentException
     */
    public function fromArray(array $data)
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Key 'name' is required");
        }
        switch ($data['name']) {
            case 'challenge':
                $node = Challenge::fromArray($data, $this);
                break;

            case 'success':
                $node = Success::fromArray($data, $this);
                break;

            case 'iq':
                $node = Iq::fromArray($data, $this);
                break;

            case 'stream:features':
                $node = StreamFeatures::fromArray($data, $this);
                break;

            case 'message':
                $node = Message::fromArray($data, $this);
                break;

            default:
                $node = Node::fromArray($data, $this);
                break;
        }

        return $node;
    }
}
