<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Adminhtml catalog super product configurable tab
 */
class Config extends Widget implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/super/config.phtml';

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /** @var \Magento\Catalog\Helper\Image */
    protected $image;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param Configurable $configurableType
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Catalog\Helper\Image $image
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        Configurable $configurableType,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Catalog\Helper\Image $image,
        array $data = []
    ) {
        $this->_configurableType = $configurableType;
        $this->_coreRegistry = $coreRegistry;
        $this->_catalogData = $catalogData;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_localeCurrency = $localeCurrency;
        $this->image = $image;
        parent::__construct($context, $data);
    }

    /**
     * Initialize block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setProductId($this->getRequest()->getParam('id'));

        $this->setId('config_super_product');
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Retrieve Tab class (for loading)
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Check block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return (bool)$this->getProduct()->getCompositeReadonly();
    }

    /**
     * Check whether attributes of configurable products can be editable
     *
     * @return bool
     */
    public function isAttributesConfigurationReadonly()
    {
        return (bool)$this->getProduct()->getAttributesConfigurationReadonly();
    }

    /**
     * Check whether prices of configurable products can be editable
     *
     * @return bool
     */
    public function isAttributesPricesReadonly()
    {
        return $this->getProduct()->getAttributesConfigurationReadonly() ||
            $this->_catalogData->isPriceGlobal() && $this->isReadonly();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = (array)$this->_configurableType->getConfigurableAttributesAsArray($this->getProduct());
            $this->setData('attributes', $attributes);
        }
        return $this->getData('attributes');
    }

    /**
     * Retrieve Links in JSON format
     *
     * @return string
     */
    public function getLinksJson()
    {
        $products = $this->_configurableType->getUsedProducts($this->getProduct());
        if (!$products) {
            return '{}';
        }
        $data = [];
        foreach ($products as $product) {
            $data[$product->getId()] = $this->getConfigurableSettings($product);
        }
        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Retrieve configurable settings
     *
     * @param Product $product
     * @return array
     */
    public function getConfigurableSettings($product)
    {
        $data = [];
        $attributes = $this->_configurableType->getUsedProductAttributes($this->getProduct());
        foreach ($attributes as $attribute) {
            $data[] = [
                'attribute_id' => $attribute->getId(),
                'label' => $product->getAttributeText($attribute->getAttributeCode()),
                'value_index' => $product->getData($attribute->getAttributeCode()),
            ];
        }

        return $data;
    }

    /**
     * Retrieve Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Associated Products');
    }

    /**
     * Retrieve Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Associated Products');
    }

    /**
     * Can show tab flag
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check is a hidden tab
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get list of used attributes
     *
     * @return array
     */
    public function getSelectedAttributes()
    {
        return $this->getProduct()->getTypeId() == Configurable::TYPE_CODE ? array_filter(
            $this->_configurableType->getUsedProductAttributes($this->getProduct())
        ) : [];
    }

    /**
     * Get parent tab code
     *
     * @return string
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * Get base application currency
     *
     * @return \Zend_Currency
     */
    public function getBaseCurrency()
    {
        return $this->_localeCurrency->getCurrency(
            $this->_scopeConfig->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default')
        );
    }

    /**
     * @return string
     */
    public function getNoImageUrl()
    {
        return $this->image->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * @return bool
     */
    public function isConfigurableProduct()
    {
        return $this->getProduct()->getTypeId() === Configurable::TYPE_CODE || $this->getRequest()->has('attributes');
    }
}
