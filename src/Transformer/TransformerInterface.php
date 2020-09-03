<?php

/*
 * This file is part of the MigrationBundle.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CoopTilleuls\MigrationBundle\Transformer;

use CoopTilleuls\MigrationBundle\EventListener\TransformerEvent;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface TransformerInterface
{
    /**
     * Create legacy record from local object.
     */
    public function create(TransformerEvent $event): void;

    /**
     * Update legacy record from local object.
     */
    public function update(TransformerEvent $event): void;

    /**
     * Delete legacy record from local object.
     */
    public function delete(TransformerEvent $event): void;
}
