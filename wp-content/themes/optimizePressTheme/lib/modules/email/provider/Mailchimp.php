<?php

require_once(OP_MOD . 'email/ProviderInterface.php');
require_once(OP_MOD . 'email/LoggerInterface.php');
require_once(OP_LIB . 'vendor/mailchimp-api-class/MCAPI.class.php');

/**
 * MailChimp email integration provider
 * @author Luka Peharda <luka.peharda@gmail.com>
 */
class OptimizePress_Modules_Email_Provider_Mailchimp implements OptimizePress_Modules_Email_ProviderInterface
{
    const OPTION_NAME_API_KEY = 'mailchimp_api_key';

    /**
     * @var OP_MCAPI
     */
    protected $client = null;

    /**
     * @var boolean
     */
    protected $apiKey = false;

    /**
     * @var OptimizePress_Modules_Email_LoggerInterface
     */
    protected $logger;

    /**
     * Initializes client object and fetches API KEY
     */
    public function __construct(OptimizePress_Modules_Email_LoggerInterface $logger)
    {
        /*
         * Fetching API key from the wp_options table
         */
        $this->apiKey = op_get_option(self::OPTION_NAME_API_KEY);

        /*
         * Initializing logger
         */
        $this->logger = $logger;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::subscribe()
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new OP_MCAPI($this->apiKey, false, $this->logger);
        }

        return $this->client;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::getLists()
     */
    public function getLists()
    {
        $lists = $this->getClient()->lists();

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
            foreach ($lists['data'] as $list) {
                $data['lists'][$list['id']] = array('name' => $list['name'], 'fields' => $this->getFormFields($list['id']));
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

            $mergeVars = $this->prepareMergeVars($data['list']);

            $status = $this->getClient()->listSubscribe($data['list'], $data['email'], $mergeVars, 'html', op_post('double_optin') === 'Y' ? true : false);

            $this->logger->notice('Subscription status: ' . print_r($status, true));

            /*
             * Displays error
             */
            if (false === $status) {

                $this->logger->error('Error ' . $this->getClient()->errorCode . ': ' . $this->getClient()->errorMessage);

                if ($this->getClient()->errorCode == 214) {
                    if (isset($_POST['redirect_url'])) {
                        $action = sprintf(__('<a href="javascript:history.go(-1);">Return to previous page</a> or <a href="%s">continue</a>.', OP_SN), op_post('redirect_url'));
                    } else {
                        $action = __('<a href="javascript:history.go(-1);">Return to previous page.</a>', OP_SN);
                    }
                    op_warning_screen(
                        __('This email is already subscribed...', OP_SN),
                        __('Optin form - Warning', OP_SN),
                        $action
                    );
                }
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

        $status = $this->getClient()->listSubscribe($list, $email, array('fname' => $fname, 'lname' => $lname));

        $this->logger->notice('Registration status: ' . print_r($status, true));
        /*
         * Displays error
         */
        if (false === $status) {
            $this->logger->error('Error ' . $this->getClient()->errorCode . ': ' . $this->getClient()->errorMessage);
        }

        return true;
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
            if ($name !== 'EMAIL' && false !== $value = op_post($name)) {
                $vars[$name] = $value;
            }
        }

        if (count($vars) === 0) {
            $vars = null;
        }

        return $vars;
    }

    /**
     * @see OptimizePress_Modules_Email_ProviderInterface::isEnabled()
     */
    public function isEnabled()
    {
        return $this->apiKey === false ? false : true;
    }

    /**
     * Returns form fields for given list
     * @param  string $id
     * @return array
     */
    public function getFormFields($id)
    {
        $fields = array();

        $vars = $this->getClient()->listMergeVars($id);
        foreach ($vars as $var) {
            $fields[$var['tag']] = $var['name'];
        }

        return $fields;
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
        if ($lists['total'] > 0) {
            foreach ($lists['data'] as $list) {
                $data['lists'][$list['id']] = array('name' => $list['name']);
            }
        }

        $this->logger->info('Items: ' . print_r($data, true));

        return $data;
    }
}