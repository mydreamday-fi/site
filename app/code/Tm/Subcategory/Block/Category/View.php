<?php
namespace Tm\Subcategory\Block\Category;

use Magento\Framework\View\Element\Template;

class View extends \Magento\Catalog\Block\Category\View
{
    protected $_coreRegistry = null;
    protected $_helper;
    public function __construct(
        Template\Context $context, 
        \Magento\Catalog\Model\Layer\Resolver $layerResolver, 
        \Magento\Framework\Registry $registry, 
        \Magento\Catalog\Helper\Image $image,    
        \Magento\Catalog\Helper\Category $categoryHelper, 
        \Magento\Catalog\Model\CategoryFactory  $categoryFactory,
		\Tm\Subcategory\Helper\Data $helper,
        array $data = array()) 
    {   
        parent::__construct($context, $layerResolver, $registry, $categoryHelper,$data);
        $this->_categoryFactory = $categoryFactory;
        $this->image = $image;
		$this->_helper = $helper;
     }

    public function getCategoryList()
    {
      $_category  = $this->getCurrentCategory();
	  if($_category)
	  {
		  $collection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect('*')
              ->addAttributeToFilter('is_active', 1)
              ->setOrder('position', 'ASC')
              ->addIdFilter($_category->getChildren());
		return $collection;
	  }
	  else {
		return;
	  }
      
      
    }
    public function getCategoryThumbImage($category) {

	return $this->_helper->getImageUrl($category);
	
	/**$mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
		//return $mediaDirectory.'catalog/category'.$imageName;
        return  $mediaDirectory.'catalog/category/'.$imageName;
       **/
    }
    
   
    public function getPlaceholderImage(){
        return $this->image->getPlaceholder('image');
    }
}
