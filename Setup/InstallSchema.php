<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Expertrec\ExpertrecSiteSearch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Expertrec\ExpertrecSiteSearch\Helper\Data;
use \Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{    private $logger;

    /**
     *  @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    protected $helperData;
    protected $storeManagerData;
    protected $scopeConfig;

    public function __construct(\Psr\Log\LoggerInterface $logger,
                                \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
                                \Magento\Store\Model\StoreManagerInterface $storeFetcher,
                                Data $helperData,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->logger = $logger;
        $this->configWriter = $configWriter;
        $this->helperData = $helperData;
        $this->storeManagerData = $storeFetcher;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();

        // Using ident_support way
        $user_name = $this->scopeConfig->getValue('trans_email/ident_support/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $admin_email = $this->scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        // using admin auth way
        // $user_name = $this->adminInfo->getUser()->getUsername();
        // $email = $this->adminInfo->getUser()->getEmail();

        // hardcoded
        // $user_name = "unknown";
        // $admin_email = "john@doe.com";

        $this->logger->info("Expertrec: Store Support Name: " . $user_name);
        $this->logger->info("Expertrec: Store Support Email: " . $admin_email);

        $org_data = $this->helperData->fetchOrgData($user_name, $admin_email);
        $this->logger->info("Expertrec: Install of expertrec plugin called, setting up db");
        $storeManagerDataList = $this->storeManagerData->getStores();
        foreach ($storeManagerDataList as $key => $value) {
            $store_id_val = $key;
            $this->configWriter->save('expertrecsection/expertrecgroup/clientid',  $org_data->mid, $scope = 'stores', $scopeId = $store_id_val);
            $this->configWriter->save('expertrecsection/expertrecgroup/clientsecret',  $org_data->write_key, $scope = 'stores', $scopeId = $store_id_val);
            break;
        }

        /**
         * Create table 'queue'
         */
        $tableName = $setup->getTable('expertrec_queue');

        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => false,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'action',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Action'
                )
                ->setComment('Queue');
            $setup->getConnection()->createTable($table);
        }
        
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $entityType = $objectManager->get('Magento\Eav\Model\Config')->getEntityType('catalog_product');
            $this->helperData->sendFullSync();
            $this->helperData->deltaSync();
        }
        catch(\Magento\Framework\Exception\LocalizedException $e){
            $this->logger->Info("Expertrec: catalog_product entity not found, will call sendFullSync() later");
        }
        $setup->endSetup();
    }
}
