<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Plugin\Checkout\Controller\Cart;

use Closure;
use Exception;
use Magento\Checkout\Controller\Cart\Add as CoreCart;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Supremis\DigitalSubscription\Model\Cart;
use Supremis\DigitalSubscription\Service\ConfigService;

class Add
{
    public function __construct(
        private ConfigService $configService,
        private Session $session,
        private Cart $cart
    ) {}

    /**
     * @param CoreCart $subject
     * @param Closure $proceed
     * @return mixed
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function aroundExecute(CoreCart $subject, Closure  $proceed)
    {
        if (!$this->configService->isEnabled()) {
            return $proceed();
        }

        $items = $this->session->getQuote()->getAllVisibleItems() ?? [];
        $productId = (int) $subject->getRequest()->getParam('product');

        if ($product = $this->cart->initProduct($productId)) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $superAttribute = $subject->getRequest()->getParam('super_attribute');
                $this->cart->restrictAddToCart($items, $superAttribute);
            } elseif ($product->getTypeId() === "downloadable") {
                $this->cart->restrictDownloadableProduct($items, $productId);
            }
        }

        return $proceed();
    }
}
