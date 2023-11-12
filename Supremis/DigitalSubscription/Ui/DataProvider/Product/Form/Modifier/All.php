<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class All extends AbstractModifier implements ModifierInterface
{
    /**
     * @param PoolInterface $pool
     * @param array $meta
     */
    public function __construct(
        private PoolInterface $pool,
        private array $meta = []
    ) {}

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        try {
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $data = $modifier->modifyData($data);
            }
        } catch (LocalizedException $exception) {
            return [];
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $this->meta = $meta;

        try {
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $this->meta = $modifier->modifyMeta($this->meta);
            }
        } catch (LocalizedException $exception) {
            return [];
        }

        return $this->meta;
    }
}
