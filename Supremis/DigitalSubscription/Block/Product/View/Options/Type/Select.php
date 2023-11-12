<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Block\Product\View\Options\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Block\Product\View\Options\Type\Select as BaseSelect;
use Magento\Catalog\Block\Product\View\Options\Type\Select\CheckableFactory;
use Magento\Catalog\Block\Product\View\Options\Type\Select\MultipleFactory;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Html\Select as HtmlSelect;
use Magento\Framework\View\Element\Template\Context;

class Select extends BaseSelect
{
    public function __construct(
        Context          $context,
        Data             $pricingHelper,
        CatalogHelper    $catalogData,
        array            $data = [],
        CheckableFactory $checkableFactory = null,
        MultipleFactory  $multipleFactory = null
    ) {
        parent::__construct($context, $pricingHelper, $catalogData, $data, $checkableFactory, $multipleFactory);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getValuesHtml(): string
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
        $this->setSkipJsReloadPrice(1);

        try {
            if ($_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN
                || $_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE
                || $_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX
                || $_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_RADIO
            ) {
                $require = $_option->getIsRequire() ? ' required' : '';
                $extraParams = '';
                $select = $this->getLayout()->createBlock(HtmlSelect::class)->setData(
                    [
                        'id' => 'select_' . $_option->getId(),
                        'class' => $require . ' product-custom-option admin__control-select'
                    ]
                );

                if ($_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN) {
                    $select->setName('options[' . $_option->getId() . ']')->addOption('', __('-- Please Select --'));
                } else {
                    $select->setName('options[' . $_option->getId() . '][]');
                    $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
                }

                foreach ($_option->getValues() as $_value) {
                    $priceStr = $this->_formatPrice(
                        [
                            'is_percent' => $_value->getPriceType() == 'percent',
                            'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                        ],
                        false
                    );

                    $defaultAttribute = [];

                    if ($_value->getData('is_default')) {
                        $defaultAttribute = ['selected' => 'selected'];
                    }

                    $select->addOption(
                        $_value->getOptionTypeId(),
                        $_value->getTitle() . ' ' . strip_tags($priceStr) . ' ',
                        [
                            'price' => $this->pricingHelper->currencyByStore(
                                $_value->getPrice(true),
                                $store,
                                false
                            ), $defaultAttribute
                        ]
                    );
                }

                if ($_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {
                    $extraParams = ' multiple="multiple"';
                }

                if (!$this->getSkipJsReloadPrice()) {
                    $extraParams .= ' onchange="opConfig.reloadPrice()"';
                }

                $extraParams .= ' data-selector="' . $select->getName() . '"';
                $select->setExtraParams($extraParams);

                if ($configValue) {
                    $select->setValue($configValue);
                }

                return $select->getHtml();
            }
        } catch (LocalizedException $exception) {
            return '';
        }

        return '';
    }
}
