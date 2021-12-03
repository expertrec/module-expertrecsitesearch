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
            $this->helper->sendFullSync();
	        return $this;
	    }
	    catch(\Exception $e){

	    }
	    return $this;
    }
}
