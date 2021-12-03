<?php

namespace Expertrec\ExpertrecSiteSearch\Model\ResourceModel\Queue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Expertrec\ExpertrecSiteSearch\Model\Queue', 'Expertrec\ExpertrecSiteSearch\Model\ResourceModel\Queue');
//        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
