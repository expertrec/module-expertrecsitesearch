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
        // no need to specifically turn off auto-increment, it can cause error in data generation
        // $this->_isPkAutoIncrement = false;
    }
}
