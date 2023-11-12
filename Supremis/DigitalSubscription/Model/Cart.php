<?php

declare(strict_types=1);

namespace Supremis\DigitalSubscription\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Cart
{
    public function __construct(
        private StoreManagerInterface $storeManager,
        private ProductRepositoryInterface $productRepository,
        private ManagerInterface $manager,
        private ResourceConnection $connection,
        private SerializerInterface $serializer
    ) {}

    /**
     * @param array $superAttribute
     * @return string
     */
    public function getSubscriptionType(array $superAttribute): string
    {
        try {
            $connection = $this->connection->getConnection();
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            return "";
        }

        foreach ($superAttribute as $attribute) {
            $query = "select e.value FROM eav_attribute_option_value e where e.option_id="
                . $attribute . " and store_id = " . $storeId;

            $result = $connection->fetchAll($query);

            if ($result) {
                $result = $this->serializer->serialize($result);
                $result = substr($result, 1, -1);
                $result = $this->serializer->unserialize($result);

                if (str_contains($result['value'], "Krajowa") || str_contains($result['value'], "Zagraniczna")) {
                    return $result['value'];
                }
            }
        }

        return "";
    }

    /**
     * @param int $productId
     *
     * @return ProductInterface|bool
     * @throws NoSuchEntityException
     */
    public function initProduct(int $productId): ProductInterface|bool
    {
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();

            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param array $items
     * @param int $productId
     * @return void
     * @throws Exception
     */
    public function restrictDownloadableProduct(array $items, int $productId): void
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('testsetest');

        foreach ($items as $item) {
            if ($item->getProduct()->getTypeId() === "downloadable" && $item->getProduct()->getId() === (string) $productId) {
                $this->manager->addErrorMessage(__("Cannot add the same downloadable product to the cart"));
                throw new Exception("Cannot add the same downloadable product to the cart");
            }
        }
    }

    /**
     * @param array $items
     * @param array $superAttribute
     * @return void
     * @throws Exception
     */
    public function restrictAddToCart(array $items, array $superAttribute): void
    {
        $currentType = $this->getSubscriptionType($superAttribute);
        $types = [$currentType];

        foreach ($items as $item) {
            $buyRequest = $this->serializer->unserialize($item->getOptionByCode('info_buyRequest')->getValue());

            if (isset($buyRequest['super_attribute'])) {
                $type = $this->getSubscriptionType($buyRequest['super_attribute']) ?? "";

                if (str_contains($type, "Krajowa")) {
                    $types[] = "Krajowa";
                }

                if (str_contains($type, "Zagraniczna")) {
                    $types[] = "Zagraniczna";
                }

                $this->throwMessage($types, $currentType);
            }
        }
    }

    /**
     * @param array $quoteItems
     * @return bool
     */
    public function checkFortInternationalDelivery(array $quoteItems): bool
    {
        foreach ($quoteItems as $item) {
            $buyRequest = $this->serializer->unserialize($item->getOptionByCode('info_buyRequest')->getValue());

            if (isset($buyRequest['super_attribute'])) {
                $type = $this->getSubscriptionType($buyRequest['super_attribute']) ?? "";

                if (str_contains($type, "Zagraniczna")) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $types
     * @param string $currentType
     * @return void
     * @throws Exception
     */
    public function throwMessage(array $types, string $currentType): void
    {
        if (in_array("Krajowa", $types) && in_array("Zagraniczna", $types)) {
            if (str_contains($currentType, "Krajowa")) {
                $this->manager->addErrorMessage(__("Cannot add domestic subscription while having international one in the cart"));
                throw new Exception("Cannot add domestic subscription while having international one in the cart");
            } elseif (str_contains($currentType, "Zagraniczna"))
                $this->manager->addErrorMessage(__("Cannot add international subscription while having international one in the cart"));
                throw new Exception("Cannot add international subscription while having international one in the cart");
        }
    }
}
