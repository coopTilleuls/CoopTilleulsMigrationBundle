<?php

/*
 * This file is part of the MigrationBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\MigrationBundle\Exception;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class LoaderNotFoundException extends \RuntimeException
{
    public function __construct($loader)
    {
        parent::__construct(sprintf('Cannot find loader "%s".', $loader));
    }
}
