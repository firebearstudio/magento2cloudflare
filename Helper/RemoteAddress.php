<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Firebear\CloudFlare\Helper;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress as basRemoteAddres;
/**
 * Library for working with client ip address
 */
class RemoteAddress extends basRemoteAddres
{


    /**
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param array $alternativeHeaders
     */
    public function __construct(\Magento\Framework\App\RequestInterface $httpRequest, array $alternativeHeaders = [])
    {
       parent::__construct($httpRequest,$alternativeHeaders);
    }

    /**
     * Retrieve Client Remote Address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     */
    public function getRemoteAddress($ipToLong = false)
    {
        if ($this->remoteAddress === null) {
            if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $this->remoteAddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
            }
            else    {
                $this->remoteAddress = parent::getRemoteAddress(false);
            }
        }  
        return $ipToLong ? ip2long($this->remoteAddress) : $this->remoteAddress;
    }

}
