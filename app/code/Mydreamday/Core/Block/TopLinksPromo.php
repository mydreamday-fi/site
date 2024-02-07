<?php

namespace Mydreamday\Core\Block;

class TopLinksPromo extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context)
	{
		parent::__construct($context);
	}

	public function showTopLinksPromo()
	{
		return __('Hello World');
	}
}