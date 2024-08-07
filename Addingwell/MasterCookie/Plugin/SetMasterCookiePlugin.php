<?php

namespace Addingwell\MasterCookie\Plugin;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class SetMasterCookiePlugin
{
    protected $cookieManager;
    protected $cookieMetadataFactory;
    protected $httpHeader;
    protected $sessionManager;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManager $sessionManager,
        Header $httpHeader
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->httpHeader = $httpHeader;
        $this->sessionManager = $sessionManager;
    }

    public function beforeSendResponse(HttpResponse $subject)
    {

        if ($this->sessionManager->getShouldSetMasterCookie()) {
            $cookieName = '_aw_master_id';
            $domain = $this->httpHeader->getHttpHost();
            $cookieDomain = $this->getMainDomain($domain);
            $cookieLifetime = 13 * 30 * 24 * 60 * 60; // 13 months in seconds
            if (empty($this->cookieManager->getCookie($cookieName))) {
                $cookieValue = $this->generateUuid();
            } else {
                $cookieValue = $this->cookieManager->getCookie($cookieName);
            }

            // Set the cookie with the domain
            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration($cookieLifetime)
                ->setPath('/')
                ->setDomain($cookieDomain) // Set the domain here
                ->setHttpOnly(false);

            $this->cookieManager->setPublicCookie(
                $cookieName,
                $cookieValue,
                $metadata
            );
        }
    }

    private function getMainDomain($url)
    {
        $composedTlds = [
            'co.uk', 'gov.uk', 'ac.uk', 'org.uk', 'net.uk', 'sch.uk', 'nhs.uk', 'police.uk',
            'com.au', 'net.au', 'org.au', 'edu.au', 'gov.au', 'asn.au', 'id.au',
            'co.jp', 'ac.jp', 'ne.jp', 'or.jp', 'go.jp', 'ed.jp', 'ad.jp', 'gr.jp',
            'com.cn', 'net.cn', 'gov.cn', 'org.cn', 'edu.cn', 'mil.cn', 'ac.cn',
            'com.br', 'net.br', 'org.br', 'gov.br', 'edu.br', 'mil.br', 'art.br', 'coop.br',
            'co.in', 'net.in', 'org.in', 'gov.in', 'ac.in', 'res.in', 'edu.in', 'mil.in', 'nic.in',
            'gc.ca', 'gov.ca',
            'com.de', 'net.de', 'org.de',
            'gov.it', 'edu.it',
            'asso.fr', 'nom.fr', 'prd.fr', 'presse.fr', 'tm.fr', 'com.fr', 'gouv.fr',
            'com.es', 'nom.es', 'org.es', 'gob.es', 'edu.es',
            'co.za', 'net.za', 'gov.za', 'org.za', 'edu.za',
            'com.mx', 'net.mx', 'org.mx', 'edu.mx', 'gob.mx',
            'com.ru', 'net.ru', 'org.ru', 'edu.ru', 'gov.ru',
            'co.kr', 'ne.kr', 'or.kr', 're.kr', 'pe.kr', 'go.kr', 'mil.kr',
            'com.sg', 'net.sg', 'org.sg', 'edu.sg', 'gov.sg', 'per.sg',
            'com.my', 'net.my', 'org.my', 'gov.my', 'edu.my', 'mil.my',
            'com.hk', 'net.hk', 'org.hk', 'gov.hk', 'edu.hk', 'idv.hk',
            'com.ar', 'net.ar', 'org.ar', 'gov.ar', 'edu.ar', 'int.ar',
            'com.tr', 'net.tr', 'org.tr', 'gov.tr', 'edu.tr', 'mil.tr',
        ];
        // Remove protocol if present
        $domain = preg_replace('/^https?:\/\//', '', $url);
        // Remove www. if present
        $domain = preg_replace('/^www\./', '', $domain);
        // Split the domain into parts
        $parts = explode('.', $domain);
        $count = count($parts) - 1;

        for ($i = 0; $i < $count; $i++) {
            $possibleTld = implode('.', array_slice($parts, $i));

            if (in_array($possibleTld, $composedTlds)) {
                return '.'.implode('.', array_slice($parts, $i - 1));
            }
        }
        // Default to last two parts if no composed TLD matches
        return '.'.implode('.', array_slice($parts, -2));
    }

    private function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
        );
    }
}
