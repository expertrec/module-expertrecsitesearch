<?php

namespace Expertrec\ExpertrecSiteSearch\Helper;

class  ConstValues extends \Magento\Framework\App\Helper\AbstractHelper
{
    const expertrec_base_url   = 'https://cseb.expertrec.com/api';
    const expertrec_frontend_url = 'https://cseb.expertrec.com/api';
    const expertrec_data_url = 'http://data.expertrec.com/1/indexes/';

    public function fetch_org_data_from_server($request_url, $post_data, $secret_header_value, $data_helper_obj){
        return $data_helper_obj->send_post_request($request_url, $post_data, $secret_header_value);
    }

}
