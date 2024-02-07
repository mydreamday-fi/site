<?php
namespace Tm\Subcategory\Model\Category\Attribute\Backend;
 
class Upload extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
	protected $_uploaderFactory;
	protected $_filesystem;
	protected $_fileUploaderFactory;
	protected $_logger;
	private $imageUploader;
 
	public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
    	\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
	) {
        $this->_filesystem                = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger                      = $logger;
	}
 
    private function getImageUploader()
	{
    	if ($this->imageUploader === NULL) {
            $this->imageUploader = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Tm\Subcategory\ImageUpload'
        	);
    	}
    	return $this->imageUploader;
	}
 
	public function beforeSave($object)
	{
        $attrCode = $this->getAttribute()->getAttributeCode();
		if (!$object->hasData($attrCode)) {
            $object->setData($attrCode, NULL);
    	} else {
            $values = $object->getData($attrCode);
        	if (is_array($values)) {
                if (!empty($values['delete'])) {
                    $object->setData($attrCode, NULL);
                } else {
                    if (isset($values[0]['name']) && isset($values[0]['tmp_name'])) {
                        $object->setData($attrCode, $values[0]['name']);
                    } else {
                        // don't update
                    }
                }
      	  }
    	}
 
    	return $this;
	}
 
	public function afterSave($object)
	{
		
    	$image = $object->getData($this->getAttribute()->getName(), NULL);
 
    	if ($image !== NULL) {
        	try {
                $this->getImageUploader()->moveFileFromTmp($image);
        	} catch (\Exception $e) {
                $this->_logger->critical($e);
        	}
    	}
    	return $this;
	}
}