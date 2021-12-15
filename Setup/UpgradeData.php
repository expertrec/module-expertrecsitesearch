<?php
namespace Expertrec\ExpertrecSiteSearch\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Expertrec\ExpertrecSiteSearch\Helper\Data;

class UpgradeData implements UpgradeDataInterface{
    protected $helperData;
    private $logger;
    public function __construct(\Psr\Log\LoggerInterface $logger, Data $helperData){
        $this->helperData = $helperData;
        $this->logger = $logger;
    }
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $entityType = $objectManager->get('Magento\Eav\Model\Config')->getEntityType('catalog_product');
            $this->logger->Info("Expertrec: in Upgrade Data: catalog_product entity found, calling sendFullSync");
            $this->helperData->sendFullSync();
        }
        catch(\Exception $e){
            $this->helperData->log_to_endpoint('{
                    "location":"UpgradeData.php",
                    "data":"'. $e->getMessage() .' : catalog_product entity not found, will call sendFullSync() later",
                    "trace":"' . $e->getTraceAsString() . ' "
                }');
            $this->logger->Info("Expertrec: in Upgrade Data: catalog_product entity not found");
            $this->logger->Info($e);
        }
        $setup->endSetup();
    }
}