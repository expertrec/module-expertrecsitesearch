<?php


namespace Expertrec\ExpertrecSiteSearch\Controller\Adminhtml\Reindex;

class Fullsyncdata extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;
    protected $helper;
    protected $logger;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Expertrec\ExpertrecSiteSearch\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->logger->info("Expertrec: Reindex started from dashboard");
            $this->helper->sendFullSync();
            $this->helper->log_to_endpoint('{
                "location":"[CRON] FullSync.php",
                "data":"calling sendFullSync() from CRON"
            }');
            return $this->jsonResponse('Reindex Started');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->log_to_endpoint('{
                "location":"[CRON] FullSync.php",
                "data":"Exception in calling sendFullSync() from CRON: ' . $e->getMessage() . '",
                "trace":"' . $e->getTraceAsString() . ' "
            }');
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->helper->log_to_endpoint('{
                "location":"[CRON] FullSync.php",
                "data":"Exception in calling sendFullSync() from CRON: ' . $e->getMessage() . '",
                "trace":"' . $e->getTraceAsString() . ' "
            }');
            $this->logger->info("Exppertrec: error in reindex from dashboard: ");
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
