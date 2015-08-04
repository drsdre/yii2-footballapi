<?php
/**
 * football-api.com API Yii2 Client Component
 *
 * @see http://football-api.com/documentation/
 * @author Andre Schuurman <andre.schuurman@gmail.com>
 * @copyright 2014 Andre Schuurman
 * @license MIT License
 */

namespace FootballAPI;

use yii\base\Component;
use yii\base\InvalidConfigException;

class Client extends Component {
	/**
	 * @var string url API endpoint
	 */
	public $service_url = "http://football-api.com/api/";

	/**
	 * @var string API key as shown on http://football-api.com/account/
	 */
	public $api_key;

	/**
	 * @var string output type default value: JSON. Options include: XML, JSON, PHP, LINE, CONSOLE, VAR.
	 */
	public $output_type = 'JSON';

	/**
	 * @var string optional the IP address of interface for originating requests
	 */
	public $request_ip;

	/**
	 * @var string optional a cache component/name to cache content of requests between time outs
	 */
	public $cache;

	/**
	 * @var integer optional time in seconds to cache results
	 */
	public $cache_time;

	/**
	 * @var boolean optional generate a content hash to facilitate easier change detection
	 */
	public $generate_hash = false;

	/**
	 * Initialize component
	 *
	 * @throws Exception
	 */
	public function init() {
		if ( empty( $this->service_url ) ) {
			throw new Exception( "service_url cannot be empty. Please configure." );
		}
		if ( empty( $this->api_key ) ) {
			throw new Exception( "api_key cannot be empty. Please configure." );
		}
		if ($this->cache) {
			// If class was specified as name, try to instantiate on application
			if (is_string($this->cache)) {
				$this->cache = \yii::$app->{$this->cache};
			}
		}
	}

	/**
	 * Set the IP address of specific interface to be used for API calls
	 *
	 * @param $ip
	 *
	 * @throws Exception
	 */
	public function setRequestIp( $ip ) {
		if ( empty( $ip ) ) {
			throw new Exception( "IP parameter cannot be empty.", E_USER_WARNING );
		}
		$this->request_ip = $ip;
	}

	/**
	 * Execute API call
	 * @param string $name API call function name
	 * @param array $params API call parameters
	 *
	 * @return mixed|\SimpleXMLElement
	 * @throws Exception
	 */
	public function __call( $name, $params ) {

		$url = $this->buildUrl( $name, $params );

		// If caching is available try to return results from cache
		if ( $this->cache && $data = $this->cacheGet( $url ) ) {
			return $data;
		}

		// Retrieve the data from API
		$data = $this->request( $url );

		// Convert and check if data is valid XML
		if ( $this->output_type == 'XML') {
			if ( false === ( $data = simplexml_load_string( $data ) ) ) {
				throw new Exception( "Invalid XML" );
			}

			// Check if time-out is given for call
			// TODO: check error message
			if ( strstr( $data[0], "To avoid misuse of the service" ) ) {
				throw new Exception( $data[0] );
			}

			// If requested generate a content hash and source url
			if ($this->generate_hash) {
				$data->addChild( 'contentHash', md5($data->asXML()) );
				$data->addChild( 'sourceUrl', htmlspecialchars( $url ) );
			}
		}

		// If caching is available put results in cache
		if ( $this->cache && $this->cache_time > 0 ) {
			if ( $this->output_type == 'XML') {
				// Add cache information
				$data->addChild( 'cached', date( 'Y-m-d h:m:s' ) );
			}
			$this->cacheSet( $url, $data, $this->cache_time );
		}

		return $data;
	}

	/**
	 * Build URL for API call
	 *
	 * @param $method
	 * @param $params
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function buildUrl( $method, $params ) {
		$url = $this->service_url . "?Action=" . $method . "&APIKey=" . $this->api_key. "&OutputType=" . $this->output_type;
		for ( $i = 0; $i < count( $params ); $i ++ ) {
			if ( is_array( $params[ $i ] ) ) {
				foreach ( $params[ $i ] as $key => $value ) {
					$url .= "&" . strtolower( $key ) . "=" . rawurlencode( $value );
				}
			} else {
				throw new Exception( "Arguments must be an array" );
			}
		}

		return $url;
	}

	/**
	 * Execute API request using curl
	 *
	 * @param $url
	 *
	 * @return mixed
	 * @throws Exception
	 */
	protected function request( $url ) {
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, self::TIMEOUT_CURL );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT_CURL );
		if ( !empty($this->request_ip) ) {
			curl_setopt( $curl, CURLOPT_INTERFACE, $this->request_ip );
		}
		$data      = curl_exec( $curl );
		$cerror    = curl_error( $curl );
		$cerrno    = curl_errno( $curl );
		$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );

		if ( $cerrno != 0 ) {
			throw new Exception( "Curl error: $cerror ($cerrno)\nURL: $url", E_USER_WARNING );
		}

		if ( $http_code <> 200 ) {
			throw new Exception( "Wrong HTTP status code: $http_code - $data\nURL: $url" );
		}
		return $data;
	}

	/**
	 * Cache result
	 *
	 * @param $key
	 * @param $result
	 * @param $timeout
	 */
	protected function cacheSet($key, $result, $timeout)
	{

		$this->cache->set($key, $result, $timeout);
	}

	/**
	 * Retrieve from cache
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function cacheGet($key)
	{
		if ($result = $this->cache->get($key)) {
			return $result;
		}
	}
}