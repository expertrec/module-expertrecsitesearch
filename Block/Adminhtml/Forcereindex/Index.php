<?php


namespace Expertrec\ExpertrecSiteSearch\Block\Adminhtml\Forcereindex;

class Index extends \Magento\Backend\Block\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAjaxUrl(){
        return $this->getUrl("expertrecsitesearch/reindex/fullsyncdata"); // Controller Url
    }
}
