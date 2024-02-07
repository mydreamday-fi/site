<?php

namespace David\Custom\Plugin\Breeze\Helper;

class Data{
    public function __construct(\Magento\Framework\App\Request\Http $request){
        $this->_request = $request;
    }

    public function aroundIsEnabled($subject, $proceed)
    {
        if($this->isCategoryPage()){
            return false;
        }
        return $proceed();
    }

    public function aroundIsTurboEnabled($subject, $proceed)
    {
        if($this->isCategoryPage()){
            return false;
        }
        return $proceed();
    }

    public function isCategoryPage(){
		$fullActionName = $this->_request->getFullActionName();
		return $fullActionName == 'catalog_category_view' || 
			   $fullActionName == 'catalogsearch_result_index' || 
			   $fullActionName == 'blog_index_index' || 
			   $fullActionName == 'blog_category_view' || 
			   $fullActionName == 'blog_tag_view';
	}
}
