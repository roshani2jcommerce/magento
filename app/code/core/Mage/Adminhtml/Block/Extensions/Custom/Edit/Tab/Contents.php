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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Extensions_Custom_Edit_Tab_Contents
    extends Mage_Adminhtml_Block_Extensions_Custom_Edit_Tab_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('extensions/custom/contents.phtml');
    }

    public function getMageRoles()
    {
        $arr = array();
        foreach (Mage::getModel('adminhtml/extension')->getRoles() as $k=>$role) {
            if (isset($role['name'])) {
                $arr[$k] = $role['name'];
            }
        }
        return $arr;
    }

    public function getPearRoles()
    {
        return array(
            'php'=>'PHP Files',
            'data'=>'Data files',
            'doc'=>'Documentation',
            'script'=>'Scripts',
            'test'=>'Tests'
        );
    }
}