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
 * @package    Mage_Oscommerce
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * osCommerce order view block
 * 
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oscommerce_Block_Order_List extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('oscommerce/order/list.phtml');
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $websiteId  = $websiteId = Mage::app()->getStore()->getWebsiteId();
        $osCommerce = Mage::getModel('oscommerce/oscommerce');
        $oscOrders = $osCommerce->loadOrders($customerId, $websiteId);
        $this->setOsCommerceOrders($oscOrders);
    }

    protected function _prepareLayout()
    {
        $orderInfo = $this->getOrder();
        $order = $orderInfo['order'];
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $order['orders_id']));
        }
    }

    public function getViewOscommerceUrl($order)
    {
        return $this->getUrl('oscommerce/order/view', array('order_id'=>$order['osc_magento_id']));
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_oscommerce_order');
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/*/history');
    }
}
