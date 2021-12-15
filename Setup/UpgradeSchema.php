<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Expertrec\ExpertrecSiteSearch\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Expertrec\ExpertrecSiteSearch\Helper\Data;
use \Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
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
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();
        /**
         * Fetch username and email
         * Set default name, then try for admin else support one
         */
        $user_name = "johndoe";
        $admin_email = "john@doe.com";
        try{
            $this->logger->info("Expertrec: Trying to get admin data from DB");
            $admin_data = $this->helperData->getAdminData();
            $user_name = $admin_data[0]['username'];
            $admin_email = $admin_data[0]['email'];
        }
        catch(\Exception $e){
            $this->logger->info("Expertrec: Couldn't get admin data, proceeding with ident support");
            $user_name = $this->scopeConfig->getValue('trans_email/ident_support/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $admin_email = $this->scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        $this->logger->info("Expertrec: Install of expertrec plugin called, writing to config");
        $this->logger->info("Expertrec: Store Support Name: " . $user_name);
        $this->logger->info("Expertrec: Store Support Email: " . $admin_email);

        /**
         * Check whether api id and key is there in table
         * if no, create a new one
         */
        $config = $setup->getTable('core_config_data');
        $currentOrg = $setup->getConnection()
                            ->query('SELECT value FROM ' . $config . ' WHERE path LIKE "expertrecsection%"')
                            ->fetchAll();
        $clientIdPath = 'expertrecsection/expertrecgroup/clientid';
        $clientSecretPath = 'expertrecsection/expertrecgroup/clientsecret';
        // check if client id and secret are already present
        if(count($currentOrg) != 2){
            $this->logger->info("Expertrec: client id and secret key not found, fetching from server");
            $org_data = $this->helperData->fetchOrgData($user_name, $admin_email);
            $storeManagerDataList = $this->storeManagerData->getStores();
            foreach ($storeManagerDataList as $key => $value) {
                $store_id_val = $key;
                $this->configWriter->save($clientIdPath,  $org_data->mid, $scope = 'stores', $scopeId = $store_id_val);
                $this->configWriter->save($clientSecretPath,  $org_data->write_key, $scope = 'stores', $scopeId = $store_id_val);
                break;
            }
        }
        /**
         * Create table 'queue' if not exists
         */
        $this->logger->info("Expertrec: Adding table to DB");
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
        $setup->endSetup();
    }
}
