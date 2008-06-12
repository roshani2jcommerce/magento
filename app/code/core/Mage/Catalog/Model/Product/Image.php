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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product link model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Image extends Mage_Core_Model_Abstract
{
    protected $_width;
    protected $_height;
    protected $_keepAspectRatio;
    protected $_fillOnResize;
    protected $_fillColorOnResize;
    protected $_baseFile;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;
    protected $_angle;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeigth;

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setKeepAspectRatio($keep = true)
    {
        $this->_keepAspectRatio = (bool)$keep;
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setSize($size)
    {
        // determine width and height from string
        list($width, $height) = @explode('x', strtolower($size), 2);
        foreach (array('width', 'height') as $wh) {
            $$wh  = @(int)$$wh;
            if (empty($$wh))
                $$wh = null;
        }

        // set sizes
        $this->setWidth($width)->setHeight($height);

        return $this;
    }

    protected function _checkMemory($file = null)
    {
//        print '$this->_getMemoryLimit() = '.$this->_getMemoryLimit();
//        print '$this->_getMemoryUsage() = '.$this->_getMemoryUsage();
//        print '$this->_getNeedMemoryForBaseFile() = '.$this->_getNeedMemoryForBaseFile();

        return $this->_getMemoryLimit() > ($this->_getMemoryUsage() + $this->_getNeedMemoryForFile($file));
    }

    protected function _getMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');

        if (!isSet($memoryLimit[0])){
            $memoryLimit = "128M";
        }

        if (substr($memoryLimit, -1) == 'M') {
            return (int)$memoryLimit * 1024 * 1024;
        }
        return $memoryLimit;
    }

    protected function _getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }
        return 0;
    }

    protected function _getNeedMemoryForFile($file = null)
    {
        $file = is_null($file) ? $this->getBaseFile() : $file;
        if (!$file) {
            return 0;
        }

        if (!file_exists($file) || !is_file($file)) {
            return 0;
        }

        $imageInfo = getimagesize($file);

        if (!isset($imageInfo['channels'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['channels'] = 4;
        }
        if (!isset($imageInfo['bits'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['bits'] = 8;
        }
        return round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65);
    }

    /**
     * Convert array of 4 items (decimal r, g, b, alpha) to string of their hex values
     *
     * @param array $RGBAlphaArray
     * @return string
     */
    private function _rgbAlphaToString($RGBAlphaArray)
    {
        $result = array();
        foreach ($RGBAlphaArray as $value) {
            if (null === $value) {
                $result[] = 'null';
            }
            else {
                $result[] = sprintf('%02s', dechex($value));
            }
        }
        return implode($result);
    }

    /**
     * Set filenames for base file and new file
     *
     * $file should contain heading slash
     *
     * @param string $file
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setBaseFile($file)
    {
        // build base filename
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        if ($file && $file != 'no_selection' && !$this->_checkMemory($baseDir . '/' . $file)) {
            $file = null;
        }
        if (!$file || $file == 'no_selection') {
            if (Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder") && file_exists($baseDir . '/placeholder/' . Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder"))) {
                $file = '/placeholder/' . Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder");
            } else {
                $baseDir = Mage::getDesign()->getSkinBaseDir();
                if (file_exists($baseDir . "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg")) {
                    $file = "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
                }
            }
            $baseFile = $baseDir . $file;
        } else {
            $baseFile = $baseDir . '/' . $file;
            if (!file_exists($baseFile)) {
                if (file_exists($baseDir . "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg")) {
                    $baseFile = "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
                }
            }
        }

        if (!file_exists($baseFile)) {
            throw new Exception(Mage::helper('catalog')->__('Image file not found'));
        }
        $this->_baseFile = $baseFile;

        // build new filename (most important params)
        $path = array(
             Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath()
            ,'cache'
            ,Mage::app()->getStore()->getId()
            ,$path[] = $this->getDestinationSubdir()
        );
        if((!empty($this->_width)) || (!empty($this->_height)))
            $path[] = "{$this->_width}x{$this->_height}";
        // add misc params as a hash
        $path[] = md5(
            implode('_', array(
                 (false === $this->_fillOnResize ? 'no' : '') . 'frame'
                ,(null === $this->_fillColorOnResize ? 'ffffffnull' : $this->_rgbAlphaToString($this->_fillColorOnResize))
                ,($this->_keepAspectRatio ? '' : 'non') . 'proportional'
                ,'angle' . $this->_angle
            ))
        );
        // append prepared filename
        $this->_newFile = implode('/', $path) . $file; // the $file contains heading slash

        return $this;
    }

    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * @return Varien_Image
     */
    public function getImageProcessor()
    {
        if( !$this->_processor ) {
//            var_dump($this->_checkMemory());
//            if (!$this->_checkMemory()) {
//                $this->_baseFile = null;
//            }
            $this->_processor = new Varien_Image($this->getBaseFile());
        }
        return $this->_processor;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function resize()
    {
        if( is_null($this->getWidth()) && is_null($this->getHeight()) ) {
            return $this;
        }

        // prepare pre-resize params
        if (null !== $this->_fillOnResize) {
            $this->getImageProcessor()->setFillOnResize($this->_fillOnResize);
        }
        if (null !== $this->_fillColorOnResize) {
            $this->getImageProcessor()->setFillColorOnResize($this->_fillColorOnResize);
        }

        // resize the image
        $this->getImageProcessor()->resize($this->getWidth(), $this->getHeight(), $this->_keepAspectRatio);
        return $this;
    }

    /**
     * Passthrough to Varien_Image
     *
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setFillOnResize($flag)
    {
        $this->_fillOnResize = (bool)$flag;
        return $this;
    }

    /**
     * Passthrough to Varien_Image
     *
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setFillColorOnResize($RGBAlphaArray)
    {
        $this->_fillColorOnResize = $RGBAlphaArray;
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function rotate($angle)
    {
        $angle = intval($angle);
        $this->getImageProcessor()->rotate($angle);
        return $this;
    }

    /**
     * Set angle for rotating
     *
     * This func actually affects only the cache filename.
     *
     * @param int $angle
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setAngle($angle)
    {
        $this->_angle = $angle;
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWatermark($file, $position=null, $size=null, $width=null, $heigth=null)
    {
        $filename = false;

        if( !$file ) {
            return $this;
        }

        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        if( file_exists($baseDir . '/watermark/stores/' . Mage::app()->getStore()->getId() . $file) ) {
            $filename = $baseDir . '/watermark/stores/' . Mage::app()->getStore()->getId() . $file;
        } elseif ( file_exists($baseDir . '/watermark/websites/' . Mage::app()->getWebsite()->getId() . $file) ) {
            $filename = $baseDir . '/watermark/websites/' . Mage::app()->getWebsite()->getId() . $file;
        } elseif ( file_exists($baseDir . '/watermark/default/' . $file) ) {
            $filename = $baseDir . '/watermark/default/' . $file;
        } elseif ( file_exists($baseDir . '/watermark/' . $file) ) {
            $filename = $baseDir . '/watermark/' . $file;
        } else {
            $baseDir = Mage::getDesign()->getSkinBaseDir();
            if( file_exists($baseDir . $file) ) {
                $filename = $baseDir . $file;
            }
        }

        if( $filename ) {
            $this->getImageProcessor()
                ->setWatermarkPosition( ($position) ? $position : $this->getWatermarkPosition() )
                ->setWatermarkWidth( ($width) ? $width : $this->getWatermarkWidth() )
                ->setWatermarkHeigth( ($heigth) ? $heigth : $this->getWatermarkHeigth() )
                ->watermark($filename);
        }

        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function saveFile()
    {
        $this->getImageProcessor()->save($this->getNewFile());
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir . DS, "", $this->_newFile);
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);
    }

    public function push()
    {
        $this->getImageProcessor()->display();
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setDestinationSubdir($dir)
    {
        $this->_destinationSubdir = $dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationSubdir()
    {
        return $this->_destinationSubdir;
    }

    public function isCached()
    {
        return file_exists($this->_newFile);
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        return $this;
    }

    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWatermarkSize($size)
    {
        if( is_array($size) ) {
            $this->setWatermarkWidth($size['width'])
                ->setWatermarkHeigth($size['heigth']);
        }
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWatermarkWidth($width)
    {
        $this->_watermarkWidth = $width;
        return $this;
    }

    public function getWatermarkWidth()
    {
        return $this->_watermarkWidth;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWatermarkHeigth($heigth)
    {
        $this->_watermarkHeigth = $heigth;
        return $this;
    }

    public function getWatermarkHeigth()
    {
        return $this->_watermarkHeigth;
    }

    public function clearCache()
    {
        $directory = Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS.'cache'.DS;
        $io = new Varien_Io_File();
        $io->rmdir($directory, true);
    }
}