<?php

namespace Tmv\WhatsApi\Message\Node;

class StreamFeatures extends AbstractNode
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'stream:features';
    }

    /**
     * @return $this
     */
    public function addProfileSubscribe()
    {
        if (!$this->hasProfileSubscribe()) {
            $node = $this->getNodeFactory()->fromArray(
                array(
                    'name'       => 'w:profile:picture',
                    'attributes' => array("type" => "all")
                )
            );
            $this->addChild($node);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasProfileSubscribe()
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getName() == 'w:profile:picture') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return $this
     */
    public function removeProfileSubscribe()
    {
        foreach ($this->getChildren() as $k => $child) {
            if ($child->getName() == 'w:profile:picture') {
                unset($this->children[$k]);
            }
        }

        return $this;
    }
}
