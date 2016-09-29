<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Transformer;

use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class TransformerChain implements TransformerInterface
{
    /**
     * @var TransformerInterface[]
     */
    private $transformers = [];

    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function create(TransformerEvent $event)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($event)) {
                $transformer->create($event);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(TransformerEvent $event)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($event)) {
                $transformer->update($event);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(TransformerEvent $event)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($event)) {
                $transformer->delete($event);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TransformerEvent $event)
    {
        return true;
    }
}
