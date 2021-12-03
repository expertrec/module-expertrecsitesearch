<?php

namespace  Expertrec\ExpertrecSiteSearch\Block;
use Expertrec\ExpertrecSiteSearch\Helper\Data;
use \Magento\Framework\View\Element\Template;


class Jsinit extends Template
{
    private $dataHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $dataHelper,
        array $data = []
    )
    {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }
    public function getJSLink()
    {
        $client_id = $this->dataHelper->getConfigValue('clientid');
        $base_frontend_url = $this->dataHelper->getFrontendUrl();
        $js_url = $base_frontend_url. "/js/ci_common.js?id=".$client_id;
        return $js_url;
    }
    public function hasExpertrec(){

        if ($this->dataHelper->getConfigValue('clientid',$this->_storeManager->getStore()->getId())){
            return true;
        }
        return false;
    }

}

