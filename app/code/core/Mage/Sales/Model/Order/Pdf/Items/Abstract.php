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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Pdf Items renderer Abstract
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Order_Pdf_Items_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Order model
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Source model (invoice, shipment, creditmemo)
     *
     * @var Mage_Core_Model_Abstract
     */
    protected $_source;

    /**
     * Item object
     *
     * @var Varien_Object
     */
    protected $_item;

    /**
     * Pdf object
     *
     * @var Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected $_pdf;

    /**
     * Pdf current page
     *
     * @var Zend_Pdf_Page
     */
    protected $_pdfPage;

    /**
     * Set order model
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Set Source model
     *
     * @param Mage_Core_Model_Abstract $source
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setSource(Mage_Core_Model_Abstract $source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * Set item object
     *
     * @param Varien_Object $item
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setItem(Varien_Object $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Set Pdf model
     *
     * @param Mage_Sales_Model_Order_Pdf_Abstract $pdf
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setPdf(Mage_Sales_Model_Order_Pdf_Abstract $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Set current page
     *
     * @param Zend_Pdf_Page $page
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setPage(Zend_Pdf_Page $page)
    {
        $this->_pdfPage = $page;
        return $this;
    }

    /**
     * Retrieve order object
     *
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            Mage::throwException(Mage::helper('sales')->__('Don\'t specified order object'));
        }
        return $this->_order;
    }

    /**
     * Retrieve source object
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Abstract
     */
    public function getSource()
    {
        if (is_null($this->_source)) {
            Mage::throwException(Mage::helper('sales')->__('Don\'t specified source object'));
        }
        return $this->_source;
    }

    /**
     * Retrieve item object
     *
     * @throws Mage_Core_Exception
     * @return Varien_Object
     */
    public function getItem()
    {
        if (is_null($this->_item)) {
            Mage::throwException(Mage::helper('sales')->__('Don\'t specified item object'));
        }
        return $this->_item;
    }

    /**
     * Retrieve Pdf model
     *
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    public function getPdf()
    {
        if (is_null($this->_pdf)) {
            Mage::throwException(Mage::helper('sales')->__('Don\'t specified PDF object'));
        }
        return $this->_pdf;
    }

    /**
     * Retrieve Pdf page object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function getPage()
    {
        if (is_null($this->_pdfPage)) {
            Mage::throwException(Mage::helper('sales')->__('Don\'t specified PDF page object'));
        }
        return $this->_pdfPage;
    }

    /**
     * Draw item line
     *
     */
    abstract public function draw();

    protected function _formatOptionValue($value)
    {
        $order = $this->getOrder();

        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return  $resultValue;
        } else {
            return $value;
        }
    }

    protected function _parseDescription()
    {
        $description = $this->getItem()->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return array($description);
    }
}