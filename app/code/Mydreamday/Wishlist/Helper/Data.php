<?php
namespace Mydreamday\Wishlist\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Http\Context as AuthContext;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class Data extends AbstractHelper {
	
    private 	$customerSessionFactory;
	protected 	$wishlist;
	protected 	$productRepository;
	
	/**
	* @param \Magento\Framework\App\Helper\Context $context
	* @param \Magento\Wishlist\Model\Wishlist $wishlistHelper
	* @param \Magento\Customer\Model\Session $session
	* @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
	*/

    public function __construct(
		\Magento\Framework\App\Helper\Context $context, 
		\Magento\Wishlist\Model\Wishlist $wishlist,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		CustomerSessionFactory $customerSessionFactory,
		AuthContext $authContext
	) {
		parent::__construct($context);
		$this->wishlist = $wishlist;
		$this->customerSessionFactory = $customerSessionFactory;
		$this->productRepository = $productRepository;
		$this->authContext = $authContext;
	}

    public function getCustomerId(){
		$customerSession = $this->customerSessionFactory->create();
		if(!$customerSession->isLoggedIn()){
			return false;
		}
		return $customerSession->getCustomerId();
	}
	
	public function isProductInWishlist($productId){
		$customerId = $this->getCustomerId();
		if(!$customerId){
			return false;
		}

		$wishlistCollection = $this->wishlist->loadByCustomerId($customerId)->getItemCollection();
		$inWishlist = false;
		foreach ($wishlistCollection as $wishlist_item) {
			if($wishlist_item->getProduct()->getId() == $productId){
				$inWishlist = true;
				break;
			}
		}
		//$this->_logger->info("Product " . $productId . " in wishlist: " . ($inWishlist ? 'Yes' : 'No'));
		return $inWishlist;
	}
	public function isLoggedIn() {
		return $this->customerSessionFactory->create()->isLoggedIn();
	}
}
