<?php

namespace Expertrec\ExpertrecSiteSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductDeleteBefore implements ObserverInterface {

    protected $logger;
    protected $queue_fac;

    public function __construct(\Psr\Log\LoggerInterface $logger,
                                \Expertrec\ExpertrecSiteSearch\Model\QueueFactory $queue_fac)
    {
        $this->logger = $logger;
        $this->queue_fac = $queue_fac;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->logger->info("Expertrec: logger called at delete before");
        $event = $observer->getEvent();
        $this->logger->info("got event ". $event->getName());
        $productId = $event->getProduct()->getId();
        $this->logger->info("Expertrec: Deleting product id " . $productId);
        $model = $this->queue_fac->create();
        $model->setId($productId);
        $model->addData([ "action" => "delete"]);
        $success = $model->save();
        if (!$success) {
            $this->logger->error("Expertrec: not able to save product id to expertrec queue when deleted");
        }
    }
}
