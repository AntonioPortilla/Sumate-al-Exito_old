<?php

/**
 * Class for handling API actions as well as security and licensing
 * @author Luka Peharda <luka.peharda@gmail.com>
 */
class OptimizePress_Sl_Api
{
	const OP_SL_BASE_URL                = 'aHR0cDovL29wdGltaXplaHViLmNvbS9zbC9wdWJsaWMvYXBpLw==';
    const HEADER_INSTALLATION_URL_PARAM = 'Op-Installation-Url';
    const HEADER_API_KEY_PARAM          = 'Op-Api-Key';
    const OPTION_API_KEY_PARAM          = 'sl_api_key';
    const OPTION_API_KEY_STATUS         = 'sl_api_key_status';
    const OPTION_ELIGIBILITY_STATUS     = 'sl_eligibility_status';

    /**
     * @var OptimizePress_Sl_Api
     */
    protected static $instance;

    /**
     * Singleton pattern hidden constructor, initializes object
     */
    private function __construct()
    {}

    /**
     * Singleton pattern instance getter
     * @return OptimizePress_Sl_Api
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Returns formated network_site_url()
     * @return string
     */
    public function getInstallationUrl()
    {
        return get_bloginfo('url');
    }

    /**
     * Fetching API key from options table
     * @return [type] [description]
     */
    public function getApiKey()
    {
        return op_get_option(self::OPTION_API_KEY_PARAM);
    }

    /**
     * Saving API key to WP options table
     * @param string $key
     * @return void
     */
    public function setApiKey($key)
    {
        op_update_option(self::OPTION_API_KEY_PARAM, $key);
    }

    /**
     * Registers API key on OP SL
     * @param  string $key
     * @return bool|WP_Error      true on success, WP_Error on failure
     */
    public function register($key)
    {
        $args = array(
            'headers' => array(
                self::HEADER_INSTALLATION_URL_PARAM => $this->getInstallationUrl()
            )
        );
        $response = wp_remote_get(base64_decode(self::OP_SL_BASE_URL) . 'register/' . $key, $args);

        if (is_wp_error($response)) {
            /*
             * Request faild
             */
            error_log('OP SL error: ' . $response->get_error_message());
            return new WP_Error($response->get_error_code(), $response->get_error_message());
        } else if ((int)$response['response']['code'] !== 200) {
            /*
             * API key issues
             */
            $data = json_decode($response['body']);
            error_log('OP SL error: ' . $data->error);
            return new WP_Error('failed', $data->error);
        } else {
            /*
             * Success
             */
            $this->setApiKey($key);
            return true;
        }
    }

    /**
     * Sends data to SL for parsing
     * @param  string $type
     * @param  array $data
     * @return string
     */
    public function parse($type, $data)
    {
        $args = array(
            'headers' => array(
                self::HEADER_INSTALLATION_URL_PARAM => $this->getInstallationUrl(),
                self::HEADER_API_KEY_PARAM => $this->getApiKey()
            ),
            'body' => array(
                'data' => $data
            )
        );

        $response = wp_remote_post(base64_decode(self::OP_SL_BASE_URL) . 'parse/' . $type, $args);

        if (is_wp_error($response)) {
            /*
             * Request failed
             */
            $data = '';
        } else if ((int)$response['response']['code'] != 200) {
            /*
             * API key issues
             */
            if (is_admin()) {
                $data = '##' . sprintf(__('There seems to be a problem with your OptimizePress API Key.<br />Please recheck it is entered correctly and if you still have problems <a href="https://optimizepress.zendesk.com/hc/en-us/articles/201687173" target="_blank">see this guide for help</a> or  <a href="%s" target="_blank">contact support</a>.', OP_SN), OP_SUPPORT_LINK);
            } else {
                $data = '';
            }
        } else {
            /*
             * Success
             */
            $json = json_decode($response['body'], true);
            $data = $json['data'];
        }

        return $data;
    }

    /**
     * Ping SL service with API key
     * @param string $type
     * @param string $version
     * @return bool|WP_Error
     */
    public function ping($type = null, $version = null)
    {
        $args = array(
            'headers' => array(
                self::HEADER_INSTALLATION_URL_PARAM => $this->getInstallationUrl(),
                self::HEADER_API_KEY_PARAM => $this->getApiKey()
            ),
            'body' => array(
                'type'      => $type !== null ? $type : OP_TYPE,
                'version'   => $version !== null ? $version : OP_VERSION,
                'php'       => phpversion(),
            )
        );

        $response = wp_remote_post(base64_decode(self::OP_SL_BASE_URL) . 'ping', $args);

        if (is_wp_error($response)) {
            /*
             * Request failed
             */
            error_log('OP SL error: ' . $response->get_error_message());
            return new WP_Error($response->get_error_code(), $response->get_error_message());
        } else if ((int)$response['response']['code'] !== 200) {
            /*
             * API key issues
             */
            $data = json_decode($response['body']);
            error_log('OP SL error: ' . $data->error);
            return new WP_Error('invalid', $data->error);
        } else {
            /*
             * Success
             */
            return true;
        }
    }

    /**
     * Checks if customer is eligible for updates
     * @since  2.2.2
     * @return bool|WP_Error
     */
    public function eligible()
    {
        $args = array(
            'headers' => array(
                self::HEADER_INSTALLATION_URL_PARAM => $this->getInstallationUrl(),
                self::HEADER_API_KEY_PARAM => $this->getApiKey()
            ),
        );

        $response = wp_remote_post(base64_decode(self::OP_SL_BASE_URL) . 'eligible', $args);

        if (is_wp_error($response)) {
            /*
             * Request failed
             */
            error_log('OP SL error: ' . $response->get_error_message());
            return new WP_Error($response->get_error_code(), $response->get_error_message());
        } else if ((int)$response['response']['code'] !== 200) {
            /*
             * API key issues
             */
            $data = json_decode($response['body']);
            error_log('OP SL error: ' . $data->error);
            return new WP_Error('invalid', $data->error);
        } else {
            /*
             * Success
             */
            return true;
        }
    }

    /**
     * Check for latest theme/plugin/opm version
     * @param  string $type
     * @return mixed
     */
    public function update($type = 'theme')
    {
        $args = array(
            'headers' => array(
                self::HEADER_INSTALLATION_URL_PARAM => $this->getInstallationUrl(),
                self::HEADER_API_KEY_PARAM => $this->getApiKey()
            ),
        );

        $response = wp_remote_get(base64_decode(self::OP_SL_BASE_URL) . 'update/' . $type, $args);

        if (is_wp_error($response)) {
            /*
             * Request failed
             */
            error_log('OP SL error: ' . $response->get_error_message());
            return new WP_Error($response->get_error_code(), $response->get_error_message());
        } else if ((int)$response['response']['code'] !== 200) {
            /*
             * API key issues
             */
            $data = json_decode($response['body']);
            error_log('OP SL error: ' . $data->error);
            return new WP_Error('invalid', $data->error);
        } else {
            /*
             * Success
             */
            $data = json_decode($response['body']);
            return $data;
        }
    }
}

/**
 * Wrapper for saving API key to DB
 * @param  string $key
 * @return void
 */
function op_sl_save_key($key)
{
    OptimizePress_Sl_Api::getInstance()->setApiKey($key);

    /*
     * We are also clearing API key status transient
     */
    delete_transient(OptimizePress_Sl_Api::OPTION_API_KEY_STATUS);
}

/**
 * Wrapper for fetching API key
 * @return string
 */
function op_sl_get_key()
{
    return OptimizePress_Sl_Api::getInstance()->getApiKey();
}

/**
 * Wrapper for ping method
 * @param string $type
 * @param string $version
 * @return bool|WP_Error
 */
function op_sl_ping($type = null, $version = null)
{
    $status = OptimizePress_Sl_Api::getInstance()->ping($type, $version);

    if (is_wp_error($status) && $status->get_error_code() === 'invalid') {

    }

    return $status;
}

/**
 * Wrapper for eligible method
 * @param string $type
 * @param string $version
 * @return bool|WP_Error
 */
function op_sl_eligible()
{
    $status = OptimizePress_Sl_Api::getInstance()->eligible();

    return $status;
}

/**
 * Wrapper for fetching installation URL
 * @return string
 */
function op_sl_get_url()
{
    return OptimizePress_Sl_Api::getInstance()->getInstallationUrl();
}

/**
 * Wrapper for registration method
 * @param  string $key
 * @return bool|WP_Error
 */
function op_sl_register($key)
{
    return OptimizePress_Sl_Api::getInstance()->register($key);
}

/**
 * Wrapper for parsing method
 * @param  string $type
 * @param  array $data
 * @return string
 */
function op_sl_parse($type, $data)
{
    return OptimizePress_Sl_Api::getInstance()->parse($type, $data);
}

/**
 * Wrapper for update method
 * @param  string $type
 * @return string
 */
function op_sl_update($type)
{
    return OptimizePress_Sl_Api::getInstance()->update($type);
}