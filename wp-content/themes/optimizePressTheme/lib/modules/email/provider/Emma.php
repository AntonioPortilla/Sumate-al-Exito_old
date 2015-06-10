<?php

require_once(OP_MOD . 'email/ProviderInterface.php');
require_once(OP_MOD . 'email/LoggerInterface.php');
require_once(OP_LIB . 'vendor/emma-api-class/emma.class.php');

/**
 * MailChimp email integration provider
 * @author Luka Peharda <luka.peharda@gmail.com>
 */
class OptimizePress_Modules_Email_Provider_Emma implements OptimizePress_Modules_Email_ProviderInterface
{
    const OPTION_NAME_ACCOUNT_ID    = 'emma_account_id';
    const OPTION_NAME_PUBLIC_KEY    = 'emma_public_key';
    const OPTION_NAME_PRIVATE_KEY   = 'emma_private_key';

    /**
     * @var OP_MCAPI
     */
    protected $client = null;

    /**
     * @var boolean
     */
    protected $accountID = false;
	/**
	 * @var boolean
	 */
	protected $publicKey = false;

	/**
	 * @var boolean
	 */
	protected $privateKey = false;

    /**
     * @var OptimizePress_Modules_Email_LoggerInterface
     */
    protected $logger;

    /**
     * Initializes client object and fetches API KEY
     */
    public function __construct(OptimizePress_Modules_Email_LoggerInterface $logger)
    {
        $this->accountID    = op_get_option(self::OPTION_NAME_ACCOUNT_ID);
        $this->publicKey    = op_get_option(self::OPTION_NAME_PUBLIC_KEY);
        $this->privateKey   = op_get_option(self::OPTION_NAME_PRIVATE_KEY);

        /*
         * Initializing logger
         */
        $this->logger       = $logger;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::subscribe()
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new OP_Emma($this->accountID, $this->publicKey, $this->privateKey, $this->logger);
        }

        return $this->client;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getLists()
     */
    public function getLists()
    {
        $lists = $this->getClient()->list_groups();
		$this->logger->info('Lists: ' . print_r($lists, true));

        return $lists;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getData()
     */
    public function getData()
    {
        $data = array(
            'lists' => array()
        );

        /*
         * List parsing
         */
        $lists = $this->getLists();
        if ($lists['total'] > 0) {
            foreach ($lists as $list) {
                $data['lists'][$list->member_group_id] = array('name' => $list->group_name, 'fields' => $this->getFormFields($list->member_group_id));
            }
        }

        $this->logger->info('Formatted lists: ' . print_r($data, true));

        return $data;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::subscribe()
     */
    public function subscribe($data)
    {
        $this->logger->info('Subscribing user: ' . print_r($data, true));

        if (isset($data['list']) && isset($data['email'])) {

            $fields = $this->prepareMergeVars($data['list']);

        	$status = $this->getClient()->import_single_member($data['email'], $fields, array($data['list']));

            $this->logger->notice('Subscription status: ' . print_r($status, true));

            if (!isset($status->added) || $status->added != 1) {
                // No extra info then the one provided by debug
            }

            return true;

        } else {
            $this->logger->alert('Mandatory information not present [list and/or email address]');
            wp_die('Mandatory information not present [list and/or email address].');
        }
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::register()
     */
    public function register($list, $email, $fname, $lname)
    {
        $this->logger->info('Registering user: ' . print_r(func_get_args(), true));

        $status = $this->getClient()->import_single_member($email, array('first_name' => $fname, 'last_name' => $lname), array($list));

        $this->logger->notice('Registration status: ' . print_r($status, true));

        if (!isset($status->added) || $status->added != 1) {
            // No extra info then the one provided by debug
        }

        return true;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::isEnabled()
     */
    public function isEnabled()
    {
        return $this->accountID === false ? false : true;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getItems()
     */
    public function getItems()
    {
        $data = array(
            'lists' => array()
        );

        /*
         * List parsing
         */
        $lists = $this->getLists();
        if (is_array($lists) && count($lists) > 0) {
            foreach ($lists as $list) {
                $data['lists'][$list->member_group_id] = array('name' => $list->group_name);
            }
        }

        $this->logger->info('Items: ' . print_r($data, true));

        return $data;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getListFields()
     */
    public function getListFields($listId)
    {
        $fields = $this->getFormFields($listId);

        $this->logger->info("Fields for list $listId: " . print_r($fields, true));

        return array('fields' => $fields);
    }

    /**
     * Returns form fields for given list
     * @param  string $id
     * @return array
     */
    public function getFormFields($id)
    {
        $fields = array('email' => 'Email');

        $vars = $this->getClient()->get_field_list(false);
        foreach ($vars as $var) {
            $fields[$var->shortcut_name] = $var->display_name;
        }

        return $fields;
    }

    /**
     * Searches for possible form fields from POST and adds them to the collection
     * @param  string $id
     * @return null|array     Null if no value/field found
     */
    protected function prepareMergeVars($id)
    {
        $vars = array();
        $allowed = array_keys($this->getFormFields($id));

        foreach ($allowed as $name) {
            if ($name !== 'email' && false !== $value = op_post($name)) {
                $vars[$name] = $value;
            }
        }

        if (count($vars) === 0) {
            $vars = null;
        }

        return $vars;
    }
}