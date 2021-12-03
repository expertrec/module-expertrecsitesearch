<?php
namespace Expertrec\ExpertrecSiteSearch\Cron;
use Expertrec\ExpertrecSiteSearch\Helper\Data;
use \Expertrec\ExpertrecSiteSearch\Model\ResourceModel\Queue\CollectionFactory;
class DeltaSync {
    protected $logger;
    protected $helper;
    public function __construct(\Psr\Log\LoggerInterface $logger,
                                \Expertrec\ExpertrecSiteSearch\Helper\Data $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }
 
    public function execute() {
    	try{
            $this->logger->info("Expertrec: cron got called");

            $this->helper->deltaSync();

            return $this;
	    }
	    catch(\Exception $e){
            $this->logger->info("Expertrec: exception in cron deltasync ". $e->getMessage());
            $this->logger->info("Expertrec: exception in cron deltasync ". $e->getTraceAsString());
	    }
	    return $this;
    }

}
