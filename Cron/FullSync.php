<?php
namespace Expertrec\ExpertrecSiteSearch\Cron;
class FullSync {
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
            $this->logger->info("Expertrec: CRON got called: sendFullSync");
            $this->helperData->log_to_endpoint('{
                    "location":"[CRON] FullSync.php",
                    "data":"calling sendFullSync() from CRON"
                }');
            $this->helper->sendFullSync();
	        return $this;
	    }
	    catch(\Exception $e){
            $this->helperData->log_to_endpoint('{
                    "location":"[CRON] FullSync.php",
                    "data":"Exception in calling sendFullSync() from CRON: ' . $e->getMessage() . '",
                    "trace":"' . $e->getTraceAsString() . ' "
                }');
            $this->logger->info("Expertrec: error in calling sendFyllSync from CRON");
            $this->logger->info($e);
	    }
	    return $this;
    }
}
