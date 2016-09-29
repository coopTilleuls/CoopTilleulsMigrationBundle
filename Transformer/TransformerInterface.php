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
interface TransformerInterface
{
    /**
     * Create legacy record from local object.
     *
     * @param TransformerEvent $event
     */
    public function create(TransformerEvent $event);

    /**
     * Update legacy record from local object.
     *
     * @param TransformerEvent $event
     */
    public function update(TransformerEvent $event);

    /**
     * Delete legacy record from local object.
     *
     * @param TransformerEvent $event
     */
    public function delete(TransformerEvent $event);

    /**
     * Check if this transformer supports this object.
     *
     * @param TransformerEvent $event
     *
     * @return bool
     */
    public function supports(TransformerEvent $event);
}
