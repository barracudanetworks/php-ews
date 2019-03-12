<?php
/**
 * Contains OAuthSoapClient_Exchange.
 */

/**
 * Handles Soap communication with the Exchange server using OAuth
 * authentication
 *
 * @package php-ews\Auth
 */
class OAuthSoapClient_Exchange extends OAuthSoapClient
{
    /**
     * Access token for authentication on the exchnage server
     *
     * @var string
     */
    protected $access_token;

    /**
     * Soap output path
     *
     * @var string
     */
    protected $file_output;

    /**
     * If writing soap response to a file shoudl occur
     *
     * @var bool
     */
    protected $write_to_file;

	/**
	 * Used for routing users for impersonation purposes.
	 *
	 * @var string
	 */
	protected $anchor_mailbox;

    /**
     * Constructor
     *
     * @param string $wsdl        Location of wsdl.
     * @param array $options      SOAP call options
     * @param bool $write_to_file Whether to write curl output to disk.
     * @throws \EWS_Exception
     */
    public function __construct($wsdl, $options, $file_output, $write_to_file = false)
    {
        // Verify that an access token was entered
        if (empty($options['access_token'])) {
            throw new EWS_Exception('An access token is required.');
        }

        // Set the access token properties.
        $this->access_token = $options['access_token'];

        // If a version was set then add it to the headers.
        if (!empty($options['version'])) {
            $this->__default_headers[] = new SoapHeader(
                'http://schemas.microsoft.com/exchange/services/2006/types',
                'RequestServerVersion Version="' . $options['version'] . '"'
            );
        }

        // If impersonation was set then add it to the headers.
        if (!empty($options['impersonation'])) {
            $this->__default_headers[] = new SoapHeader(
                'http://schemas.microsoft.com/exchange/services/2006/types',
                'ExchangeImpersonation',
                $options['impersonation']
            );

	        if (array_key_exists('anchor_mailbox', $options)) {
		        $this->anchor_mailbox = $options['anchor_mailbox'];
	        } else {
		        $this->anchor_mailbox = $options['impersonation']->ConnectingSID->PrimarySmtpAddress;
	        }
		}

        // set the file output
        $this->file_output = $file_output;

        // set the write to file flag
        $this->write_to_file = $write_to_file;

        parent::__construct($wsdl, $options);
    }

	/**
     * Set the anchor mailbox value for X-AnchorMailBox header.
     *
	 * @param string $email
     */
    public function setAnchorMailBox($email)
	{
		$this->anchor_mailbox = $email;
    }

    /**
     * Returns the response code from the last request
     *
     * @return integer
     */
    public function getResponseCode()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    /**
     * Close the curl resource handler.
     *
     * @return void
     */
    public function closeConnection()
    {
        curl_close($this->ch);
    }
}
