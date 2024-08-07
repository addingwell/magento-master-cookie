<?php

namespace Addingwell\MasterCookie\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SetMasterCookieFlag implements ObserverInterface
{
    protected $sessionManager;
    protected $logger;

    public function __construct(
        SessionManager $sessionManager,
    ) {
        $this->sessionManager = $sessionManager;
    }

    public function execute(Observer $observer)
    {
        $cookieName = '_aw_master_id';

        // Check if the cookie already exists
        if (!isset($_COOKIE[$cookieName])) {
            $this->sessionManager->setShouldSetMasterCookie(true);
        }
    }
}
