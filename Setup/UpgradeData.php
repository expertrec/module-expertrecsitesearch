<?php
namespace Expertrec\ExpertrecSiteSearch\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Expertrec\ExpertrecSiteSearch\Helper\Data;

class UpgradeData implements UpgradeDataInterface{
    protected $helperData;
    public function __construct(Data $helperData){
        $this->helperData = $helperData;
    }
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $entityType = $objectManager->get('Magento\Eav\Model\Config')->getEntityType('catalog_product');
            $this->logger->Info("Expertrec: in Upgrade Data: catalog_product entity found, calling sendFullSync");
            $this->helperData->sendFullSync();
        }
        catch(\Magento\Framework\Exception\LocalizedException $e){
            $this->logger->Info("Expertrec: in Upgrade Data: catalog_product entity not found");
        }
        $setup->endSetup();
    }
}