<?php
/**
 * Copyright © 2015 Expertrec . All rights reserved.
 */
namespace Expertrec\ExpertrecSiteSearch\Helper;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;
    protected $httpRequest;
    protected $storeManagerData;
    protected $pfac;
    protected $queue_col_fac;
    protected $resourceConnection;
    protected $_currency;
    protected $groupCollectionFactory;
    protected $stockItem;
    protected $_ProductAttributeRepository;
    protected $constvalues;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Framework\App\Request\Http $httpRequest,
                                \Magento\Store\Model\StoreManagerInterface $storeManagerData,
                                \Expertrec\ExpertrecSiteSearch\Model\ResourceModel\Queue\CollectionFactory $queue_col_fac,
                                \Expertrec\ExpertrecSiteSearch\Model\QueueFactory $queue_fac,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $pfac,
                                \Magento\Framework\App\ResourceConnection $resourceConnection,
                                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
                                \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
                                \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
                                \Expertrec\ExpertrecSiteSearch\Helper\ConstValues $constvalues,
                                \Magento\Directory\Model\Currency $currency
    ) {
        $this->pfac=$pfac;
        $this->httpRequest = $httpRequest;
        $this->storeManagerData = $storeManagerData;
        $this->queue_col_fac = $queue_col_fac;
        $this->queue_fac = $queue_fac;
        $this->resourceConnection = $resourceConnection;
        $this->_currency = $currency;
        $this->stockItem = $stockItem;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->_ProductAttributeRepository = $productAttributeRepository;
        $this->constvalues = $constvalues;
		parent::__construct($context);
	}

    public function sendToServer($data) {

	    $store_id_val = $this->getFirstExpertrecStoreId();
        $ExpertrecId = $this->getConfigValue('clientid', $store_id_val);
        $ExpertrecKey = $this->getConfigValue('clientsecret', $store_id_val);
        $request_url = $this->getDataEndpoint() . $ExpertrecId . '/batch';

        $this->send_post_request($request_url, $data, $ExpertrecKey);

        return "Success";
    }

    public function getServiceUrl() {
	    return ConstValues::expertrec_base_url;
    }

    public function getFrontendUrl() {
        return ConstValues::expertrec_frontend_url;
    }

    public function send_post_request($request_url, $post_data, $secret_header_value) {
        $client = new \Zend_Http_Client();
        try {
            $client->setUri($request_url);
            $client->setConfig([
                'maxredirects'  => 3,
                'timeout'       => 30,
            ]);
            if ($secret_header_value){
                $client->setHeaders('X-Expertrec-API-Key',$secret_header_value);
            }
            $client->setHeaders('Content-type','application/json');
            $client->setRawData(json_encode($post_data));
            $method = \Zend_Http_Client::POST;
            try {
                $response = $client->request($method);
                $responseBody = $response->getBody();
                $return_data = json_decode($responseBody);
                return $return_data;
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
                return json_decode('{"status":"error","error":"Error while making post request"}');
            }
        } catch (\Zend_Http_Client_Exception $e) {
            $this->_logger->error($e->getMessage());
            return json_decode('{"status":"error","error":"Zend_Http_Client_Exception"}');
        }

    }

    public function fetchOrgData($user_name, $admin_email) {
        $request_url = $this->getServiceUrl() . '/user/ECOM/create_search_index';
        
        $data = [
            "user_name"=> $user_name,
            "email"=> $admin_email
        ];
        
        $response_data = $this->constvalues->fetch_org_data_from_server($request_url, $data, false, $this);
        return $response_data;
    }

    public function getFullObject($pid, $categoryFactory, $currency_symbol, $objectManager) {
	    try{
            $not_required_fields = array("tax_class_id", "attribute_set_id", "category_ids");

            $product_type = $pid->getTypeId();
            if ('configurable' == $product_type){
                return false;
            }

            $categoryids = $pid->getCategoryIds();
            $array_map = [];

            foreach ($categoryids as $key => $x) {
                $array_map[$key] = $categoryFactory->load($x)->getName();
            }
            $catNames = $array_map;
            $qty = (int) $this->stockItem->getStockQty($pid->getId(), $pid->getStore()->getWebsiteId());
            $parent_id = $this->getParentProductId($pid->getId());

            $array_obj = array("id" => $pid->getId(),
                "name" => $pid->getName(),
                "updated_at" => $pid->getUpdatedAt(),
                "price"=>$pid->getPrice(),
                "discount_price"=>$pid->getFinalPrice(),
                "category_names"=>$catNames,
                "currency"=>$currency_symbol,
                "quantity"=>$qty,
                "parent_id"=>$parent_id,
                "product_url"=>$pid->getProductUrl()
            );
            $attributeSetId = $pid->getAttributeSetId();
            $groupCollection = $this->groupCollectionFactory->create()
                ->setAttributeSetFilter($attributeSetId)
                ->setSortOrder()
                ->load();
            foreach ($groupCollection as $group) {
                $attributes = $pid->getAttributes($group->getId(), true);
                foreach ($attributes as $key => $attribute) {
                    if($attribute->getIsVisibleOnFront() && $attribute->getFrontend()->getValue($pid) !="" && $attribute->getFrontend()->getValue($pid) !="Non" && $attribute->getFrontend()->getValue($pid) !="No"){
                        $array_obj["prod_atr_".$attribute->getFrontend()->getLabel()] = $attribute->getFrontend()->getValue($pid);
                    }
                }
            }

            $attributes = $pid->getAttributes();
            foreach($attributes as $a) {
                $key = $a->getName();
                $value = $pid->getData($a->getName());
                if($value && !in_array("$key", $not_required_fields)) {
//                $array_obj[$key] = $value;
                    $array_obj[$key] = $a->getFrontend()->getValue($pid);
                }
            }
            $currentStore = $this->storeManagerData->getStore();
            $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
            $array_obj["image"] = $mediaUrl.$pid->getData('image');
            $array_obj["thumbnail"] = $mediaUrl.$pid->getData('thumbnail');
            $array_obj["small_image"] = $mediaUrl.$pid->getData('small_image');


            if ($parent_id != $pid->getId()){
                $parent_product = $objectManager->get('Magento\Catalog\Model\Product')->load($parent_id);
                $parent_attributes = $parent_product->getAttributes();

                $not_required_parent_keys = array("tax_class_id", "attribute_set_id", "category_ids", "price",
                    "attribute_set_id", "required_options", "has_options", "tax_class_id", "media_gallery",
                    "image", "thumbnail", "small_image");

                $overwrite_fields = array("name","url_key");

                foreach($parent_attributes as $pa) {
                    $key = $pa->getName();
                    $value = $parent_product->getData($pa->getName());
                    if ($value && !in_array($key, $not_required_parent_keys)){
                        if(in_array($key, $overwrite_fields) ) {
                            $array_obj[$key] = $pa->getFrontend()->getValue($parent_product);
                        }elseif (!array_key_exists($key, $array_obj)){
                            $array_obj[$key] = $pa->getFrontend()->getValue($parent_product);
                        }
                    }
                }
                $array_obj["product_url"] = $parent_product->getProductUrl();
            }
            return $array_obj;
        }catch (\Exception $e){
            $this->_logger->error($e->getMessage());
            $this->_logger->error("Expertrec: not able to save data for productid " . $pid->getId());
            return false;
        }

    }

    public function getParentProductId($childProductId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configurable = $objectManager->get("\Magento\ConfigurableProduct\Model\Product\Type\Configurable");
        $parentConfigObject = $configurable->getParentIdsByChild($childProductId);
        if($parentConfigObject) {
            return $parentConfigObject[0];
        }
        return $childProductId;
    }

    public function sendDeltaSync($data) {
	    $requests = [];
	    $updates = [];
	    $endSyncId = false;
	    $storeManagerDataList = $this->storeManagerData->getStores();
	    $store_org_mapping = array();
        foreach ($storeManagerDataList as $key => $value) {
            $store_id_val = $key;
            $org_id = $this->getConfigValue('clientid', $storeId = $store_id_val);
            if ($org_id){
                $store_org_mapping[$store_id_val] = $org_id;
                break;
            }
        }
	    foreach($data as $pid_action) {

	        $id = $pid_action->getId();
            $action = $pid_action->getAction();
            $this->_logger->info("Expertrec got action ". $action . " and pid " . $id);
            if ($action == "delete") {
                $requests[] = array("action" => "deleteObject", "body" => array("id"=>$id));
            } else if ($action == "startSync") {
                $requests[] = array("action" => "start_sync", "body" => array("id"=>$id));
            } else if ($action == "endSync") {
                $endSyncId = $id;
            } else {
                $updates[] = $id;
            }
        }
	    if (count($updates) > 0) {
            $collection = $this->pfac->create();
            $collection->addAttributeToSelect('*');
            $collection->addFieldToFilter('entity_id',['in' => $updates]);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->get('Magento\Catalog\Model\Category');
            $currency_symbol = $this->_currency->getCurrencySymbol();

            foreach($collection as $product) {
                $full_product_data = $this->getFullObject($product, $categoryFactory,
                    $currency_symbol, $objectManager );
                if ($full_product_data){
                    $requests[] = array("action" => "addObject", "body" => $full_product_data);
                }
            }
        }
	    if ($endSyncId){
            $requests[] = array("action" => "end_sync", "body" => array("id"=>$endSyncId));
        }

        $post_body = array("requests" => $requests);
	    $this->sendToServer($post_body);

    }

    public function getDataEndpoint(){
        return ConstValues::expertrec_data_url;
    }
    public function sendFullSync() {
        $this->_logger->info("Expertrec: full sync started");

	    $this->deleteAllEntries();
        $this->addStartSync();
	    $this->addAllProducts();
	    $this->addEndSync();
    }

    const XML_PATH_HELLOWORLD = 'expertrecsection/expertrecgroup/';

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HELLOWORLD .$field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_HELLOWORLD .'general/'. $code, $storeId);
    }

    public function getFirstExpertrecStoreId(){
        $storeManagerDataList = $this->storeManagerData->getStores();
        $store_id_val = 1;
        foreach ($storeManagerDataList as $key => $value) {
            $store_id_val = $key;
            if ($this->getConfigValue('clientid', $store_id_val)){
                return $store_id_val;
            }
        }
        return $store_id_val;
    }


public function addDataToTable($productId, $action)
 {
        $model = $this->queue_fac->create();
        $model->setId($productId);
        $model->addData([ "action" => $action]);
     try {
         $success = $model->save();
     } catch (\Exception $e) {
         $this->_logger->error("Expertrec: not able to save product id " . $productId . "->" . $action . " to expertrec queue");
     }
     if (!$success) {
            $this->_logger->error("Expertrec: not able to save product id " . $productId . "->" . $action . " to expertrec queue");
            return "Not able to save";
        }
     return "Saved successfully";
 }

    private function addStartSync()
    {
        $this->addDataToTable(-1, 'startSync');
    }
    public function deleteAllEntries()
    {
        $queue_col = $this->queue_col_fac->create();
        $queue_col->load();
        $queue_col->walk('delete');
    }

    public function addAllProducts()
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName("expertrec_queue");
        $collection = $this->pfac->create();
        $collection->setPageSize(10000);
        $pageCount = $collection->getLastPageNumber();
        // TODO: add check for total 99999999 number of products
        // -1           startsync
        // 0-99999998   products (total 99999999)
        // 99999999     endsync
        $this->_logger->Info("Expertec: total page number " . $pageCount );
        // https://magento.stackexchange.com/questions/177465/why-is-setpagesize-and-setcurpage-functions-not-working-with-the-collecti
        for($x = 1; $x <= $pageCount; $x++) {
            $this->_logger->Info("Expertec: inserting page number " . $x );
            $collection->setCurPage($x);
            $collection->load();
            $out = [];
            foreach($collection as $p) {
                $out[] = [ "product_id" => $p->getId(), "action" => "update" ];
            }

            $this->_logger->Info("Expertec: full insert data " , $out );
            $connection->insertMultiple($table, $out);
        }
        return "added all products to db";
    }
    public function addEndSync()
    {
        // we will only support max 100Million products
        $this->addDataToTable(99999999, 'endSync');
    }

    public function deltaSync()
    {
        $queue_col = $this->queue_col_fac->create();
        $queue_col->setPageSize(100);
        $queue_col->setOrder('product_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->_logger->info("Expertrec: queue count " . $queue_col->count());
        $this->_logger->info("Expertrec: queue size " . $queue_col->getSize());
        $result = $queue_col->getData();
        if (count($result)>0) {
            $this->_logger->info("Expertrec: data " . json_encode($result));
            $this->sendDeltaSync($queue_col);
            $this->deleteTheseEntries($queue_col);
            return "successfully sent all edits";
        } else {
            return "Nothing to send";
        }
    }

    public function deleteTheseEntries($queue_col)
    {
        $queue_col->walk('delete');
    }

}
