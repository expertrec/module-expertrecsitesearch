<?php
namespace Expertrec\ExpertrecSiteSearch\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use \Expertrec\ExpertrecSiteSearch\Api\Data\QueueInterface;

class Queue extends AbstractModel implements QueueInterface, IdentityInterface
{
    const CACHE_TAG = 'expertrec_queue';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Expertrec\ExpertrecSiteSearch\Model\ResourceModel\Queue');
    }

    public function getId(){
        return $this->getData(self::PRODUCT_ID);
    }

    public function getAction(){
        return $this->getData(self::ACTION);
    }

    public function getIdentities(){
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setAction($action){
        return $this->setData(self::ACTION, $action);
    }

    public function setId($id){
        return $this->setData(self::PRODUCT_ID, $id);
    }
}
