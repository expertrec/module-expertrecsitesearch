<?php
namespace Expertrec\ExpertrecSiteSearch\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
	public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		$setup->getConnection()->dropTable($setup->getTable('expertrec_queue'));
		$setup->endSetup();
	}
}