<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Model;

use Magento\Checkout\Model\Session;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Supremis\DigitalSubscription\Enum\ConfigEnum;
use Supremis\DigitalSubscription\Service\ConfigService;

class SetAllowedCountries extends AllowedCountries
{
    /** @var ConfigService */
    private ConfigService $configService;

    /** @var Session */
    private Session $session;

    /** @var Cart */
    private Cart $cart;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ConfigService $configService
     * @param Session $session
     * @param Cart $cart
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ConfigService $configService,
        Session $session,
        Cart $cart
    ) {
        parent::__construct($scopeConfig, $storeManager);

        $this->configService = $configService;
        $this->session = $session;
        $this->cart = $cart;
    }

    /**
     * @param $scope
     * @param $scopeCode
     *
     * @return array
     */
    public function getAllowedCountries($scope = ScopeInterface::SCOPE_WEBSITE, $scopeCode = null)
    {
        if ($this->configService->isEnabled()) {
            try {
                $quoteItems = $this->session->getQuote()->getAllItems() ?? [];
            } catch (NoSuchEntityException|LocalizedException $e) {
                return ["PL"];
            }

            if ($this->cart->checkFortInternationalDelivery($quoteItems)) {
                return ConfigEnum::ALLOWED_COUNTRIES;
            }
        }

        return ["PL"];
    }
}
