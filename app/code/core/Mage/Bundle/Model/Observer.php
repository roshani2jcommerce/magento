<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Bundle
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bundle Products Observer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Model_Observer
{
    /**
     * Setting Bundle Items Data to product for father processing
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function prepareProductSave($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $product = $observer->getEvent()->getProduct();

        if ($items = $request->getPost('bundle_options')) {
            $product->setBundleOptionsData($items);
        }

        if ($selections = $request->getPost('bundle_selections')) {
            $product->setBundleSelectionsData($selections);
        }

        return $this;
    }

    /**
     * Append bundles in upsell list for current product
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function appendUpsellProducts($observer)
    {
        $product = $observer->getEvent()->getProduct();

        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            return $this;
        }

        $collection = $observer->getEvent()->getCollection();
        $limit = $observer->getEvent()->getLimit();

        $bundles = Mage::getModel('catalog/product')->getResourceCollection()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addAttributeToSort('position', 'asc')
            ->addStoreFilter()
            ->addMinimalPrice()

            ->joinTable('bundle/option', 'parent_id=entity_id', array('option_id' => 'option_id'))
            ->joinTable('bundle/selection', 'option_id=option_id', array('product_id' => 'product_id'), '{{table}}.product_id='.$product->getId());

        $ids = Mage::getSingleton('checkout/cart')->getProductIds();
        $ids = array_merge($ids, $collection->getAllIds());

        if (count($ids)) {
            $bundles->addIdFilter($ids, true);
        }

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($bundles);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($bundles);

        $bundles->getSelect()->group('entity_id');

        if (isset($limit['bundle'])) {
            $bundles->setPageSize($limit['bundle']);
        }
        $bundles->load();

        foreach ($bundles->getItems() as $item) {
            $collection->addItem($item);
        }

        return $this;
    }

    /**
     * Append selection attributes to selection's order item
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function appendBundleSelectionData($observer) {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();

        if ($attributes = $quoteItem->getProduct()->getCustomOption('bundle_selection_attributes')) {
            $productOptions = $orderItem->getProductOptions();
            $productOptions['bundle_selection_attributes'] = $attributes->getValue();
            $orderItem->setProductOptions($productOptions);
        }
    }

}