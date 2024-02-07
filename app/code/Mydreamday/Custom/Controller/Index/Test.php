<?php
namespace Mydreamday\Custom\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Test extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$fileName = 'reviews.csv';
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$sql = "Select * from customer_entity orderby entity_id DESC limit 100"; 
		$result = $connection->fetchAll($sql); 
		print_r($result);die();
		
	}
}
