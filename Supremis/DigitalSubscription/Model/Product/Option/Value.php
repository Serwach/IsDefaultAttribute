<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Model\Product\Option;

use Magento\Catalog\Model\Product\Option\Value as BaseValue;
use Supremis\DigitalSubscription\Api\Data\ProductCustomOptionValuesInterface;

class Value extends BaseValue implements ProductCustomOptionValuesInterface
{
    /**
     * @return int
     */
    public function getIsDefault(): int
    {
        return $this->_getData(ProductCustomOptionValuesInterface::IS_DEFAULT);
    }

    /**
     * @param int $isDefault
     * @return ProductCustomOptionValuesInterface
     */
    public function setIsDefault(int $isDefault): ProductCustomOptionValuesInterface
    {
        return $this->setData(ProductCustomOptionValuesInterface::IS_DEFAULT, $isDefault);
    }
}
