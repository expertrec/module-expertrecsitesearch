<?php
namespace Expertrec\ExpertrecSiteSearch\Model\ResourceModel;

class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('expertrec_queue', 'product_id');
        $this->_isPkAutoIncrement = false;
    }
}
