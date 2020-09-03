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

namespace CoopTilleuls\MigrationBundle\Annotation;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Transformer
{
    public $transformer;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        if (!isset($data['value']) || !$data['value']) {
            throw new \InvalidArgumentException(sprintf('Parameter of annotation "%s" cannot be empty.', static::class));
        }

        if (!\is_string($data['value'])) {
            throw new \InvalidArgumentException(sprintf('Parameter of annotation "%s" must be a string.', static::class));
        }

        $this->transformer = $data['value'];
    }
}
