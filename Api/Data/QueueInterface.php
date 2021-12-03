<?php

namespace Expertrec\ExpertrecSiteSearch\Api\Data;

interface QueueInterface {
    const TABLE_NAME = 'expertrec_queue';
    
    const PRODUCT_ID = 'product_id';
    const ACTION = 'action';

    public function getId();
    public function getAction();

    public function setId($id);
    public function setAction($action);
}
