<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Boolean;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Field;

class Base extends AbstractModifier
{
    private array $meta = [];

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $this->meta = $meta;
        $this->addFields();

        return $this->meta;
    }

    /**
     * @return void
     */
    protected function addFields(): void
    {
        $groupCustomOptionsName    = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName       = CustomOptions::CONTAINER_OPTION;

        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $this->getValueFieldsConfig()
        );
    }

    /**
     * @return array
     */
    private function getValueFieldsConfig(): array
    {
        $fields['is_default'] = $this->getIsDefaultFieldConfig();

        return $fields;
    }

    /**
     * @return array
     */
    private function getIsDefaultFieldConfig(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Is default'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataType'      => Boolean::NAME,
                        'dataScope'     => 'is_default',
                        'sortOrder'     => 50,
                        'valueMap'      => [
                            'true'  => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
        ];
    }
}
