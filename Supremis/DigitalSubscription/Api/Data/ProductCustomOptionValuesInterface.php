<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Api\Data;

interface ProductCustomOptionValuesInterface
{
    public const IS_DEFAULT = 'is_default';

    /**
     * @return int
     */
    public function getIsDefault(): int;

    /**
     * @param int $isDefault
     * @return $this
     */
    public function setIsDefault(int $isDefault): self;
}
