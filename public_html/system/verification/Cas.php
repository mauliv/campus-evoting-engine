<?php 

function cas_show_config_error(){
	die("<strong>ERROR:</strong> CAS authentication is not properly configured.<br /><br />
	Please ensure that phpCAS is installed via Composer (<code>composer install</code>) or available in the include path.");
}

class Cas {
        
    public $CASUSER;
	
	public $config = array(
		'cas_server_url' => '',
		'phpcas_path' => 'vendor',
		'cas_disable_server_validation' => TRUE,
		'cas_debug' => FALSE,
		'changeSessionId' => FALSE
	);
        
	public function __construct($args = null){
		$cas_host = getenv('CAS_HOST') ?: 'sso.example.edu';
		$cas_port = getenv('CAS_PORT') ? (int)getenv('CAS_PORT') : 443;
		$cas_context = getenv('CAS_CONTEXT') ?: '/cas';
		$this->config['cas_server_url'] = "https://{$cas_host}:{$cas_port}{$cas_context}";
            
		$this->CASUSER = new stdClass();
		$this->CASUSER->userlogin = "";
		$this->CASUSER->attributes = array();
		$this->CASUSER->local_account = "";

		if (!function_exists('curl_init')){
			die('<strong>ERROR:</strong> You need to install the PHP module <strong>curl</strong> to use CAS authentication.');
		}

		$this->cas_server_url = $this->config['cas_server_url'];

		if (empty($this->cas_server_url) || filter_var($this->cas_server_url, FILTER_VALIDATE_URL) === FALSE) {
			cas_show_config_error();
		}

		// Autoload phpCAS: First check if class already loaded, then Composer autoloaders, then local path
		if (!class_exists('phpCAS')) {
			$autoloaders = array(
				__DIR__ . '/../../vendor/autoload.php',
				__DIR__ . '/../../../vendor/autoload.php',
				__DIR__ . '/CAS-1.3.3/CAS.php',
				__DIR__ . '/../CAS-1.3.3/CAS.php',
				$this->config['phpcas_path'] . '/CAS.php'
			);

			foreach ($autoloaders as $file) {
				if (file_exists($file)) {
					require_once $file;
					break;
				}
			}
		}

		if (!class_exists('phpCAS')) {
			cas_show_config_error();
		}

		if ($this->config['cas_debug']) {
			phpCAS::setDebug();
		}

		// Init CAS client
		$defaults = array('path' => '', 'port' => 443);
		$cas_url = array_merge($defaults, parse_url($this->cas_server_url));

		phpCAS::client(
			CAS_VERSION_2_0, 
			$cas_url['host'],
			$cas_url['port'], 
			$cas_url['path'],
			$this->config['changeSessionId']
		);

		// Configures SSL behavior
		if ($this->config['cas_disable_server_validation']){
			phpCAS::setNoCasServerValidation();
		} else {
			$ca_cert_file = $this->config['cas_server_ca_cert'] ?? '';
			if (empty($ca_cert_file)) {
				cas_show_config_error();
			}
			phpCAS::setCasServerCACert($ca_cert_file);
		}
	}

	/**
	 * Trigger CAS authentication if user is not yet authenticated.
	 */
    public function force_auth()
    {
    	phpCAS::forceAuthentication();
    }

    /**
     * Return authenticated user and attributes
     */
    public function user()
    {
    	if (phpCAS::isAuthenticated()) {
	    	$this->CASUSER->userlogin = phpCAS::getUser();
            $this->CASUSER->attributes = phpCAS::getAttributes();
            return (object) array(
				'userlogin' => $this->CASUSER->userlogin,
				'attributes' => $this->CASUSER->attributes
			);
    	} else {
    		die("User was not authenticated yet.");
    	}
    }

    /**
     * Logout and redirect to the main site URL or specified URL
     */
    public function logout($url = '')
    {
    	phpCAS::logoutWithRedirectService($url);
    }

    public function is_authenticated()
    {
    	return phpCAS::isAuthenticated();
    }
    
    public function authenticate() {
        return phpCAS::checkAuthentication();
    }
}
