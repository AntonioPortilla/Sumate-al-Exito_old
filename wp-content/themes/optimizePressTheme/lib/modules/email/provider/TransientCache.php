<?php

require_once(OP_MOD . 'email/ProviderInterface.php');

/**
 * WordPress Transient cache decorator for email services provider
 * @author Luka Peharda <luka.peharda@gmail.com>
 */
class OptimizePress_Modules_Email_Provider_TransientCache implements OptimizePress_Modules_Email_ProviderInterface
{
    const CACHE_EXPIRY_TIME = 900;

    /**
     * @var OptimizePress_Modules_Email_ProviderInterface
     */
    protected $provider = null;

    /**
     * @var string
     */
    protected $cachePrefix = null;

    /**
     * Initializes $provider and caches its output
     * @param OptimizePress_Modules_Email_ProviderInterface $provider
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
        $this->cachePrefix = get_class($provider);
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getData()
     */
    public function getData()
    {
        $cacheKey = md5($this->cachePrefix . '_' . __FUNCTION__);

        if (false === $data = get_transient($cacheKey)) {
            $data = $this->provider->getData();
            uasort($data['lists'], array($this, 'sort'));
            set_transient($cacheKey, $data, self::CACHE_EXPIRY_TIME);
        }

        return $data;
    }

    /**
     * Sorts items alphabeticaly
     *
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @since 2.1.4
     * @param  array $a
     * @param  array $b
     * @return integer
     */
    protected function sort($a, $b)
    {
        if (strtolower($a['name']) > strtolower($b['name'])) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getItems()
     */
    public function getItems()
    {
        $cacheKey = md5($this->cachePrefix . '_' . __FUNCTION__);

        if (false === $data = get_transient($cacheKey)) {
            $data = $this->provider->getItems();
            uasort($data['lists'], array($this, 'sort'));
            set_transient($cacheKey, $data, self::CACHE_EXPIRY_TIME);
        }

        return $data;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getListFields()
     */
    public function getListFields($listId)
    {
        $cacheKey = md5($this->cachePrefix . '_' . __FUNCTION__ . '_' . $listId);

        if (false === $data = get_transient($cacheKey)) {
            $data = $this->provider->getListFields($listId);
            set_transient($cacheKey, $data, self::CACHE_EXPIRY_TIME);
        }

        return $data;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getLists()
     */
    public function getLists()
    {
        $cacheKey = md5($this->cachePrefix . '_' . __FUNCTION__);

        if (false === $data = get_transient($cacheKey)) {
            $data = $this->provider->getLists();
            uasort($data['lists'], array($this, 'sort'));
            set_transient($cacheKey, $data, self::CACHE_EXPIRY_TIME);
        }

        return $data;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::isEnabled()
     */
    public function isEnabled()
    {
        return $this->provider->isEnabled();
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::subscribe()
     */
    public function subscribe($data)
    {
        return $this->provider->subscribe($data);
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::subscribe()
     */
    public function getClient()
    {
        return $this->provider->getClient();
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::register()
     */
    public function register($list, $email, $fname, $lname)
    {
        return $this->provider->register($list, $email, $fname, $lname);
    }

    public function getFollowUpSequences()
    {
        $cacheKey = md5($this->cachePrefix . '_' . __FUNCTION__);

        if (false === $data = get_transient($cacheKey)) {
            $data = $this->provider->getFollowUpSequences();
            uasort($data['lists'], array($this, 'sort'));
            set_transient($cacheKey, $data, self::CACHE_EXPIRY_TIME);
        }

        return $data;
    }

    public function __call($method, $args)
    {
        if (method_exists($this->provider, $method)) {
            return call_user_func_array(array($this->provider, $method), $args);
        }
    }
}