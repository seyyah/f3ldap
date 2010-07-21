<?php

/**
	PHP Fat-Free Framework - Less Hype, More Meat.

	Fat-Free is a powerful yet lightweight PHP 5.3+ Web development
	framework designed to help build dynamic Web sites - fast. The
	latest version of the software can be downloaded at:-

	http://sourceforge.net/projects/fatfree

	See the accompanying HISTORY.TXT file for information on the changes
	in this release.

	If you use the software for business or commercial gain, permissive
	and closed-source licensing terms are available. For personal use, the
	PHP Fat-Free Framework and other files included in the distribution
	are subject to the terms of the GNU GPL v3. You may not use the
	software, documentation, and samples except in compliance with the
	license.

	Copyright (c) 2009-2010 F3 Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Core
		@version 1.3.22
**/

//! Core Pack
final class F3 {

	//@{
	//! Framework details
	const
		TEXT_AppName='PHP Fat-Free Framework',
		TEXT_Version='1.3.22';
	//@}

	//@{
	//! Locale-specific error/exception messages
	const
		TEXT_NotFound='The requested URL {@CONTEXT} was not found',
		TEXT_Route='The route {@CONTEXT} cannot be resolved',
		TEXT_Handler='The route handler {@CONTEXT} is invalid',
		TEXT_Directive='Custom directive {@CONTEXT} is not implemented',
		TEXT_Form='The form field hander {@CONTEXT} is invalid',
		TEXT_Object='{@CONTEXT} cannot be used in object context',
		TEXT_Instance='The framework cannot be started more than once',
		TEXT_Write='{@CONTEXT.0} must have write permission on {@CONTEXT.1}',
		TEXT_HTTP='HTTP status code {@CONTEXT} is invalid',
		TEXT_Class='Undefined class {@CONTEXT}',
		TEXT_Method='Undefined method {@CONTEXT}',
		TEXT_Variable='Framework variable must be specified',
		TEXT_Illegal='Illegal framework variable name',
		TEXT_Attrib='Attribute {@CONTEXT} cannot be resolved',
		TEXT_PCRELimit='PCRE backtrack/recurson limits set too low',
		TEXT_MSet='Invalid multi-variable assignment',
		TEXT_PHPExt='PHP extension {@CONTEXT} is not enabled',
		TEXT_Config='The configuration file {@CONTEXT} was not found',
		TEXT_Section='{@CONTEXT} is not a valid section',
		TEXT_Trace='Stack trace';
	//@}

	//@{
	//! HTTP/1.1 status (RFC 2616)
	const
		HTTP_100='Continue',
		HTTP_101='Switching Protocols',
		HTTP_200='OK',
		HTTP_201='Created',
		HTTP_202='Accepted',
		HTTP_203='Non-Authorative Information',
		HTTP_204='No Content',
		HTTP_205='Reset Content',
		HTTP_206='Partial Content',
		HTTP_300='Multiple Choices',
		HTTP_301='Moved Permanently',
		HTTP_302='Found',
		HTTP_303='See Other',
		HTTP_304='Not Modified',
		HTTP_305='Use Proxy',
		HTTP_306='Temporary Redirect',
		HTTP_400='Bad Request',
		HTTP_401='Unauthorized',
		HTTP_402='Payment Required',
		HTTP_403='Forbidden',
		HTTP_404='Not Found',
		HTTP_405='Method Not Allowed',
		HTTP_406='Not Acceptable',
		HTTP_407='Proxy Authentication Required',
		HTTP_408='Request Timeout',
		HTTP_409='Conflict',
		HTTP_410='Gone',
		HTTP_411='Length Required',
		HTTP_412='Precondition Failed',
		HTTP_413='Request Entity Too Large',
		HTTP_414='Request-URI Too Long',
		HTTP_415='Unsupported Media Type',
		HTTP_416='Requested Range Not Satisfiable',
		HTTP_417='Expectation Failed',
		HTTP_500='Internal Server Error',
		HTTP_501='Not Implemented',
		HTTP_502='Bad Gateway',
		HTTP_503='Service Unavailable',
		HTTP_504='Gateway Timeout',
		HTTP_505='HTTP Version Not Supported';
	//@}

	//@{
	//! HTTP headers
	const
		HTTP_Host='Host',
		HTTP_Agent='User-Agent',
		HTTP_Content='Content-Type',
		HTTP_Length='Content-Length',
		HTTP_Disposition='Content-Disposition',
		HTTP_Transfer='Content-Transfer-Encoding',
		HTTP_Expires='Expires',
		HTTP_Pragma='Pragma',
		HTTP_Cache='Cache-Control',
		HTTP_LastMod='Last-Modified',
		HTTP_IfMod='If-Modified-Since',
		HTTP_Powered='X-Powered-By',
		HTTP_AcceptEnc='Accept-Encoding',
		HTTP_Encoding='Content-Encoding',
		HTTP_Connect='Connection',
		HTTP_Location='Location',
		HTTP_WebAuth='WWW-Authenticate';
	//@}

	const
		//! Framework-mapped PHP globals
		PHP_Globals='GET|POST|COOKIE|REQUEST|SESSION|FILES|SERVER|ENV',
		//! HTTP methods for RESTful interface
		HTTP_Methods='GET|HEAD|POST|PUT|DELETE',
		//! Default extensions allowed in templates
		FUNCS_Default='standard|date|pcre',
		//! GZip compression level; Any higher just hogs CPU
		GZIP_Compress=2,
		//! Default cache timeout for Axon sync method
		SYNC_Default=60;

	//! Container for Fat-Free global variables
	public static $global;

	//! XML translation table
	private static $xmltab=array();

	/**
		Send HTTP status header; Return text equivalent of status code
			@return mixed
			@param $_code integer
			@public
	**/
	public static function httpStatus($_code) {
		if (!defined('self::HTTP_'.$_code)) {
			// Invalid status code
			self::$global['CONTEXT']=$_code;
			trigger_error(self::TEXT_HTTP);
			return FALSE;
		}
		// Get description
		$_response=constant('self::HTTP_'.$_code);
		// Send raw HTTP header
		if (PHP_SAPI!='cli' && !self::$global['QUIET'] && !headers_sent())
			header('HTTP/1.1 '.$_code.' '.$_response);
		return $_response;
	}

	/**
		Trigger an HTTP 404 error
			@public
	**/
	public static function http404() {
		self::$global['CONTEXT']=$_SERVER['REQUEST_URI'];
		self::error(
			self::resolve(self::TEXT_NotFound),404,debug_backtrace(FALSE)
		);
	}

	/**
		Send HTTP header with expiration date (seconds from current time)
			@param $_secs integer
			@public
	**/
	public static function httpCache($_secs=0) {
		if (PHP_SAPI!='cli' && !self::$global['QUIET'] && !headers_sent()) {
			if ($_secs) {
				header_remove(self::HTTP_Pragma);
				header(self::HTTP_Cache.': max-age='.$_secs);
				header(self::HTTP_Expires.': '.
					date('r',time()+$_secs));
			}
			else {
				header(self::HTTP_Pragma.': no-cache');
				header(self::HTTP_Cache.': no-cache, must-revalidate');
			}
			header(self::HTTP_Powered.': '.self::TEXT_AppName);
		}
	}

	/**
		Flatten array values and return as a comma-separated string
			@return string
			@param $_args array
			@private
	**/
	private static function listArgs($_args) {
		if (!is_array($_args))
			$_args=array($_args);
		$_str='';
		foreach ($_args as $_key=>$_val)
			if ($_key!=='GLOBALS')
				$_str.=($_str?',':'').
					(is_array($_val) && is_int(key($_val))?
						// Numeric-indexed array
						('array('.self::listArgs($_val).')'):
						(is_object($_val)?
							// Convert closure/object to string
							(get_class($_val).'()'):
							// Remove whitespaces
							preg_replace(
								array(
									'/,\s+(.+?=>)/','/\s=>\s/',
									'/\s*\(\s+/','/,*\s+\)/','/\s+/'
								),
								array(',$1','=>','(',')',' '),
									stripslashes(var_export($_val,TRUE))
							)
						)
					);
		return self::resolve($_str);
	}

	/**
		Convert Windows double-backslashes to slashes
			@return string
			@param $_str string
			@public
	**/
	public static function fixSlashes($_str) {
		return $_str?str_replace('\\','/',$_str):$_str;
	}

	/**
		Convert double quotes to equivalent XML entities (&#34;)
			@return string
			@param $_val string
			@public
	**/
	public static function fixQuotes($_val) {
		if (is_array($_val))
			return array_map('self::fixQuotes',$_val);
		return is_string($_val)?
			str_replace('"','&#34;',self::resolve($_val)):$_val;
	}

	/**
		Display default error page; Use custom page if found
			@param $_str string
			@param $_code integer
			@param $_stack array
			@public
	**/
	public static function error($_str,$_code,$_stack) {
		$_prior=self::$global['ERROR'];
		// Remove framework methods and extraneous data
		$_stack=array_filter(
			$_stack,
			function($_nexus) {
				return isset($_nexus['line']) &&
					((F3::$global['DEBUG'] || $_nexus['file']!=__FILE__) &&
						!preg_match(
							'/^(call_user_func|include|'.
								'trigger_error|{.+?})/',$_nexus['function']
						) &&
						(!isset($_nexus['class']) ||
							$_nexus['class']!='Runtime')
					);
			}
		);
		rsort($_stack);
		// Generate internal server error if code is zero
		if (!$_code)
			$_code=500;
		// Save error details
		$_error=&self::$global['ERROR'];
		$_error['code']=$_code;
		$_error['title']=self::httpStatus($_code);
		$_error['text']=self::resolve($_str);
		// Stack trace
		$_trace='';
		foreach ($_stack as $_level=>$_nexus)
			$_trace.='#'.$_level.' '.
				($_nexus['line']?
					(self::fixSlashes($_nexus['file']).':'.
						$_nexus['line'].' '):'').
				($_nexus['function']?
					($_nexus['class'].$_nexus['type'].$_nexus['function'].
					(!preg_match('/\{.+\}/',$_nexus['function']) &&
						isset($_nexus['args'])?
						('('.self::listArgs($_nexus['args']).')'):'')):'').
					"\n";
		if (PHP_SAPI!='cli' && !F3::$global['QUIET']) {
			// Write to server's error log (with complete stack trace)
			error_log($_error['text']);
			foreach (explode("\n",$_trace) as $_str)
				if ($_str)
					error_log($_str);
		}
		if ($_prior || self::$global['QUIET'])
			return;
		$_error['trace']='';
		foreach (explode('|','title|text|trace') as $_sub)
			$_error[$_sub]=htmlspecialchars(rawurldecode($_error[$_sub]));
		if (!self::$global['RELEASE'] && trim($_trace))
			$_error['trace']=nl2br($_trace);
		// Find template referenced by the global variable E<code>
		if (isset(self::$global['E'.$_error['code']])) {
			$_file=self::fixSlashes(self::$global['E'.$_error['code']]);
			if (!is_null($_file) &&
				file_exists(self::$global['GUI'].$_file)) {
					// Render custom template stored in E<code>
					echo self::serve($_file);
					return;
			}
		}
		unset(self::$global['CONTEXT']);
		// Use default HTML response page
		echo self::resolve(
			'<html>'.
				'<head>'.
					'<title>{@ERROR.code} {@ERROR.title}</title>'.
					'<style>#trace {padding:10px; background:#eee;}</style>'.
				'</head>'.
				'<body>'.
					'<h1>{@ERROR.title}</h1>'.
					'<p>{@ERROR.text}</p>'.
					'<p id="trace">{@ERROR.trace}</p>'.
				'</body>'.
			'</html>'
		);
	}

	/**
		Normalize array subscripts
			@return string
			@param $_str string
			@param $_f3var boolean
			@private
	**/
	private static function remix($_str,$_f3var=TRUE) {
		$_out='';
		return array_reduce(
			preg_split(
				'/(?:\[[\'"](?![\'"])|\[|(?<![\'"])[\'"]\]|\]|\.)/',$_str
			),
			function($_out,$_fix) use($_f3var) {
				if (isset($_fix[0])) {
					if ($_f3var || $_out)
						$_fix='[\''.$_fix.'\']';
				}
				return $_out.$_fix;
			}
		);
	}

	/**
		Generate Base36/CRC32 hash code
			@return string
			@param $_str string
			@public
	**/
	public static function hashCode($_str) {
		return str_pad(
			base_convert(sprintf('%u',crc32($_str)),10,36),7,'0',
				STR_PAD_LEFT
		);
	}

	/**
		Remove HTML tags (except those enumerated) to protect against
		XSS/code injection attacks
			@return mixed
			@param $_input string
			@param $_tags string
			@public
	**/
	public static function scrub($_input,$_tags=NULL) {
		if (is_array($_input))
			foreach ($_input as $_key=>$_val)
				$_input[$_key]=self::scrub($_val,$_tags);
		if (is_string($_tags))
			$_tags='<'.implode('><',explode('|',$_tags)).'>';
		return is_string($_input)?
			htmlspecialchars(
				self::fixQuotes(strip_tags($_input,$_tags)),
				ENT_COMPAT,self::$global['ENCODING'],FALSE
			):$_input;
	}

	/**
		Get framework variable reference
			@return mixed
			@param $_name string
			@param $_set boolean
			@private
	**/
	private static function &ref($_name,$_set=FALSE) {
		$_name=self::remix($_name);
		if (empty($_name))
			return NULL;
		// Traverse array
		preg_match_all(
			'/\[[\'"]*(.*?)[\'"]*\]/',$_name,$_matches,PREG_SET_ORDER
		);
		if ($_set)
			$_var=&self::$global;
		else
			$_var=self::$global;
		foreach ($_matches as $_match) {
			if (!$_set && (!is_array($_var) || !isset($_var[$_match[1]])))
				// No such element
				return NULL;
			if ($_set)
				$_var=&$_var[$_match[1]];
			else
				$_var=$_var[$_match[1]];
		}
		return $_var;
	}

	/**
		Return TRUE if framework variable has been assigned a value
			@return boolean
			@param $_name string
			@public
	**/
	public static function exists($_name) {
		if (!$_name) {
			trigger_error(self::TEXT_Variable);
			return array(FALSE,NULL);
		}
		$_var=&self::ref(self::resolve($_name));
		return isset($_var);
	}

	/**
		Return value of framework variable
			@return mixed
			@param $_name string
			@public
	**/
	public static function get($_name) {
		if (!$_name) {
			trigger_error(self::TEXT_Variable);
			return array(FALSE,NULL);
		}
		$_name=self::resolve($_name);
		$_hash='var.'.self::hashCode(self::remix($_name));
		$_cached=Cache::cached($_hash);
		if ($_cached)
			return unserialize(gzinflate(Cache::fetch($_hash)));
		if (preg_match('/^('.self::PHP_Globals.')\b/',$_name,$_match)) {
			// Synchronize PHP and framework globals
			if (substr($_name,0,7)=='SESSION' && !strlen(session_id()))
				session_start();
			self::$global[$_match[1]]=&$GLOBALS['_'.$_match[1]];
		}
		return self::ref($_name);
	}

	/**
		Bind value to framework variable
			@param $_name string
			@param $_val mixed
			@param $_persist boolean
			@public
	**/
	public static function set($_name,$_val,$_persist=FALSE) {
		if (!$_name) {
			trigger_error(self::TEXT_Variable);
			return;
		}
		$_name=self::resolve($_name);
		if (!preg_match('/^\w+\b(?:\[[^\]]+\]|\.\w+\b)*$/',$_name)) {
			trigger_error(self::TEXT_Illegal);
			return;
		}
		$_val=self::fixQuotes($_val);
		preg_match('/(?<=\[)[^\]]*(?=\])/',self::remix($_name),$_match);
		if (preg_match('/^\'('.self::PHP_Globals.')\'/',$_match[0])) {
			if (substr($_name,0,7)=='SESSION' && !strlen(session_id()))
				session_start();
			// Use eval; PHP doesn't allow global variable variables
			eval('$_'.self::remix($_name,FALSE).'='.
				// Convert to string if __set_state is not implemented
				(is_object($_val) && !method_exists($_val,'__set_state')?
					('\''.$_val.'\''):var_export($_val,TRUE)).';'
			);
		}
		// Assign value by reference
		$_var=&self::ref($_name,TRUE);
		$_var=$_val;
		// Cache if specified
		$_hash='var.'.self::hashCode(self::remix($_name));
		$_cached=Cache::cached($_hash);
		if ($_cached || $_persist)
			Cache::store($_hash,gzdeflate(serialize($_val)));
	}

	/**
		Multi-variable assignment using associative array
			@param $_arg string
			@public
	**/
	public static function mset($_arg) {
		if (!is_array($_arg)) {
			// Invalid argument
			trigger_error(self::TEXT_MSet);
			return;
		}
		// Bind key-value pairs
		array_map('self::set',array_keys($_arg),$_arg);
	}

	/**
		Unset framework variable
			@param $_name string
			@public
	**/
	public static function clear($_name) {
		$_name=self::resolve($_name);
		$_hash='var.'.self::hashCode(self::remix($_name));
		$_cached=Cache::cached($_hash);
		if ($_cached)
			Cache::remove($_hash);
		preg_match('/(?<=\[)[^\]]*(?=\])/',self::remix($_name),$_match);
		if (preg_match('/^\'('.self::PHP_Globals.')\'/',$_match[0])) {
			if (substr($_name,0,7)=='SESSION' && !strlen(session_id()))
				session_destroy();
			// Use eval; PHP doesn't allow global variable variables
			eval('unset($_'.self::remix($_name,FALSE).');');
		}
		eval('unset(self::$global'.self::remix($_name).');');
	}

	/**
		Determine if framework variable has been cached
			@param $_name string
			@public
	**/
	public static function cached($_name) {
		$_name=self::resolve($_name);
		$_hash='var.'.self::hashCode(self::remix($_name));
		return Cache::cached($_hash);
	}

	/**
		Reroute to specified URI
			@param $_uri string
			@public
	**/
	public static function reroute($_uri=NULL) {
		session_commit();
		if (PHP_SAPI!='cli' && !self::$global['QUIET'] && !headers_sent()) {
			// HTTP redirect
			self::httpStatus($_SERVER['REQUEST_METHOD']!='GET'?303:301);
			header(self::HTTP_Location.': '.self::resolve($_uri));
		}
		else {
			self::mock('GET '.self::resolve($_uri));
			self::run();
		}
		exit(0);
	}

	/**
		Validate route pattern and break it down into an array consisting
		of the request method and request URI
			@return mixed
			@param $_regex string
			@public
	**/
	public static function checkRoute($_regex) {
		if (preg_match(
			'/('.self::HTTP_Methods.')\s+(.*)/i',$_regex,$_route))
				return array_slice($_route,1);
		// Invalid route
		self::$global['CONTEXT']=$_regex;
		trigger_error(self::TEXT_Route);
		return FALSE;
	}

	/**
		Assign handler to route pattern
			@param $_pattern string
			@param $_funcs mixed
			@param $_ttl integer
			@public
	**/
	public static function route($_pattern,$_funcs,$_ttl=0) {
		// Check if valid route pattern
		$_route=self::checkRoute($_pattern);
		// Valid URI pattern
		if (is_string($_funcs)) {
			// String passed
			foreach (explode('|',$_funcs) as $_func) {
				// Not a lambda function
				if ($_func[0]==':') {
					// PHP include file specified
					$_file=self::fixSlashes(substr($_func,1)).'.php';
					if (!file_exists(self::$global['IMPORTS'].$_file)) {
						// Invalid route handler
						self::$global['CONTEXT']=$_file;
						trigger_error(self::TEXT_Handler);
						return;
					}
				}
				elseif (!is_callable($_func)) {
					// Invalid route handler
					self::$global['CONTEXT']=$_func;
					trigger_error(self::TEXT_Handler);
					return;
				}
			}
		}
		elseif (!is_callable($_funcs)) {
			// Invalid route handler
			self::$global['CONTEXT']=$_funcs;
			trigger_error(self::TEXT_Handler);
			return;
		}
		// Assign name to URI variable
		$_regex=preg_replace(
			'/\{?@(\w+\b)\}?/i','(?P<$1>[\w-\.!~]+\b)',
			// Wildcard character in URI
			str_replace('\*','(.*)',preg_quote($_route[1],'/'))
		);
		// Use pattern and HTTP method as array indices
		// Save handlers and cache timeout
		self::$global['ROUTES']
			['/^'.(ctype_alnum(substr($_regex,-1))?
				($_regex.'(?:\b\/*)'):$_regex).'\/*(?:\?.*)*$/i']
			[$_route[0]]=array($_funcs,$_ttl);
	}

	/**
		Provide REST interface by mapping URL to object/PHP class
			@param $_url string
			@param $_obj mixed
			@public
	**/
	public static function map($_url,$_obj) {
		foreach (explode('|',self::HTTP_Methods) as $_method) {
			if (method_exists($_obj,$_method))
				self::route(
					strtoupper($_method).' '.$_url,array($_obj,$_method)
				);
		}
	}

	/**
		Workaround for retrieving headers from non-Apache servers
			@return array
			@private
	**/
	private static function getHeaders() {
		$_hdr=array();
		foreach ($_SERVER as $_key=>$_val)
			if (substr($_key,0,5)=='HTTP_') {
				$_hdr[preg_replace_callback(
					'/\w+\b/',
					function($_word) {
						return ucfirst(strtolower($_word[0]));
					},
					str_replace('_','-',substr($_key,5))
				)]=$_val;
			}
		return $_hdr;
	}

	/**
		Retrieve from cache; or save all output generated by route
		if not previously rendered
			@return string
			@param $_proc array
			@private
	**/
	private static function urlCache(array $_proc) {
		// Get HTTP request headers
		$_req=array();
		if (PHP_SAPI!='cli' && !self::$global['QUIET']) {
			$_req=function_exists('getallheaders')?
				getallheaders():self::getHeaders();
		}
		// Content divider
		$_div=chr(0);
		// Get hash code for this Web page
		$_hash='url.'.self::hashCode(
			$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI']
		);
		$_cached=Cache::cached($_hash);
		// Regex pattern for Content-Type
		$_regex='/'.self::HTTP_Content.'.+/';
		$_time=time();
		if ($_cached && ($_time-$_cached['time'])<$_proc[1]) {
			// Activate cache timer
			self::httpCache($_cached['time']+$_proc[1]-$_time);
			if (!isset($_req[self::HTTP_IfMod]) ||
				$_cached['time']>strtotime($_req[self::HTTP_IfMod])) {
				// Retrieve from cache and decompress
				$_buffer=gzinflate(Cache::fetch($_hash));
				$_type=strstr($_buffer,$_div,TRUE);
				if (preg_match($_regex,$_type,$_match) &&
					PHP_SAPI!='cli' && !self::$global['QUIET'] &&
					!headers_sent())
						header($_match[0]);
				// Save response
				self::$global['RESPONSE']=substr(strstr($_buffer,$_div),1);
			}
			else
				// No need to serve page; client-side cache is fresh
				self::httpStatus(304);
		}
		else {
			// Cache this page
			ob_start();
			self::call($_proc[0]);
			self::$global['RESPONSE']=ob_get_contents();
			ob_end_clean();
			if (!self::$global['ERROR'] && self::$global['RESPONSE']) {
				$_type='';
				foreach (headers_list() as $_hdr)
					if (preg_match($_regex,$_hdr,$_match)) {
						// Add Content-Type header to buffer
						$_type=$_match[0];
						break;
					}
				// Compress and save to cache
				Cache::store($_hash,
					gzdeflate($_type.$_div.self::$global['RESPONSE'])
				);
				// Activate cache timer
				self::httpCache($_proc[1]);
				if (PHP_SAPI!='cli' && !self::$global['QUIET'] &&
					!headers_sent())
						header(self::HTTP_LastMod.': '.date('r',$_time));
			}
		}
	}

	/**
		Sniff headers for real IP address
			@return string
			@public
	**/
	public static function realIP() {
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			// Behind proxy
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// Use first IP address in list
			$_ip=explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
			return $_ip[0];
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
		Return TRUE if remote address is listed in spam database
			@return boolean
			@param $_addr string
			@public
	**/
	public static function spam($_addr) {
		if ($_addr!='127.0.0.1' &&
			// Not a private IPv4 range
			filter_var($_addr,
				FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE) &&
			(!isset(self::$global['EXEMPT']) ||
				!in_array($_addr,explode('|',self::$global['EXEMPT'])))) {
			// Convert to reverse IP dotted quad
			$_addr=implode('.',array_reverse(explode('.',$_addr)));
			foreach (explode('|',self::$global['DNSBL']) as $_list)
				// Check against DNS blacklist
				if (gethostbyname($_addr.'.'.$_list)!=$_addr.'.'.$_list)
					return TRUE;
		}
		return FALSE;
	}

	/**
		Process routes based on incoming URI
			@public
	**/
	public static function run() {
		// Validate user against spam blacklists
		if (PHP_SAPI!='cli' &&
			isset(self::$global['DNSBL']) && self::spam(self::realIP())) {
			if (isset($_global['SPAM']))
				// Spammer detected; Send to blackhole
				self::reroute($_global['SPAM']);
			else
				// HTTP 404 message
				self::http404();
		}
		// Process routes
		if (isset(self::$global['ROUTES'])) {
			$_found=FALSE;
			krsort(self::$global['ROUTES']);
			foreach (self::$global['ROUTES'] as $_regex=>$_route) {
				if (!preg_match($_regex,$_SERVER['REQUEST_URI'],$_args))
					continue;
				$_found=TRUE;
				// Inspect each defined route
				foreach ($_route as $_method=>$_proc) {
					if ($_SERVER['REQUEST_METHOD']!=$_method)
						continue;
					// Save named regex captures
					foreach ($_args as $_key=>$_arg)
						if (is_numeric($_key) && $_key)
							unset($_args[$_key]);
					self::$global['PARAMS']=$_args;
					// Default: Do not cache
					self::httpCache(0);
					// Save the current time
					$_time=time();
					if ($_method=='GET' && $_proc[1]) {
						$_SERVER['REQUEST_TTL']=$_proc[1];
						// Save to/retrieve from cache
						self::urlCache($_proc);
					}
					else {
						// Capture output
						ob_start();
						self::call($_proc[0]);
						self::$global['RESPONSE']=ob_get_contents();
						ob_end_clean();
					}
					$_elapsed=microtime(TRUE)-$_time;
					if ((self::$global['THROTTLE']/1e3)>$_elapsed)
						usleep(
							1e6*(self::$global['THROTTLE']/
							1e3-$_elapsed)
						);
					if (self::$global['RESPONSE'] && 
						!self::$global['QUIET'])
						// Display response
						echo self::$global['RESPONSE'];
					// Hail the conquering hero
					return;
				}
			}
		}
		// No such Web page
		self::http404();
	}

	/**
		Return XML translation table
			@return array
			@param $_latin boolean
			@public
	**/
	public static function xmlTable($_latin=FALSE) {
		if (!isset(self::$xmltab[$_latin])) {
			$_xl8=get_html_translation_table(HTML_ENTITIES,ENT_COMPAT);
			if ($_latin)
				// Latin index
				foreach ($_xl8 as $_key=>$_val)
					$_tab[$_val]='&#'.ord($_key).';';
			else
				// ANSI index
				foreach ($_xl8 as $_key=>$_val)
					$_tab[$_key]='&#'.ord($_key).';';
			self::$xmltab[$_latin]=$_tab;
		}
		return self::$xmltab[$_latin];
	}

	/**
		Convert plain text to XML entities
			@return string
			@param $_str string
			@param $_latin boolean
			@public
	**/
	public static function xmlEncode($_str,$_latin=FALSE) {
		return strtr($_str,self::xmlTable($_latin));
	}

	/**
		Convert XML entities to plain text
			@return string
			@param $_str string
			@param $_latin boolean
			@public
	**/
	public static function xmlDecode($_str,$_latin=FALSE) {
		return strtr($_str,array_flip(self::xmlTable($_latin)));
	}

	/**
		Evaluate template expressions in string
			@return mixed
			@param $_str string
			@public
	**/
	public static function resolve($_str) {
		// Analyze string for correct framework expression syntax
		$_str=preg_replace_callback(
			// Expression
			'/\{('.
				// Capture group
				'(?:'.
					// Variable token
					'@\w+\b(?:\[[^\]]+\]|\.\w+\b)*|'.
					// Function/method/parenthesized expression
					'\w*\h*[\(\,\)]|'.
					// Whitespaces and operators
					'[\h\?\.\+\-\*\/%!=<>&\|:]|'.
					// String and numeric constants
					'\'[^\']*\'|"[^"]*"|\d*\.?\d+(?:e\d+)*|'.
					// Null and boolean constants
					'NULL|TRUE|FALSE'.
				// End of captured string
				')+'.
			// End of expression
			')\}/i',
			// Evaluate expression; This will cause a syntax error
			// if framework is running on an old version of PHP!
			function($_expr) {
				// Find and replace variables
				return eval('return (string)'.
					preg_replace_callback(
						// Framework variable
						'/@(\w+\b(?:\[[^\]]+\]|\.\w+\b)*)/',
						function($_var) {
							$_val=F3::get($_var[1]);
							// Retrieve variable contents
							return !is_object($_val) ||
								method_exists($_val,'__set_state')?
									var_export($_val,TRUE):(string)$_val;
						},
						preg_replace_callback(
							// Function
							'/(\w+)\h*\(([^\)]*)\)/',
							function($_val) {
								return ($_val[1].trim($_val[2]))=='array'?
									// Null out empty array
									'\'\'':
									// check if prohibited function
									(F3::allowed($_val[1])?
										$_val[0]:('\''.$_val[0].'\''));
							},
							$_expr[1]
						)
					).';'
				);
			},
			// Coerce input
			(string)$_str
		);
		if (preg_last_error()!=PREG_NO_ERROR) {
			trigger_error(self::TEXT_PCRELimit);
			return FALSE;
		}
		return $_str;
	}

	/**
		Process <F3:include> directives
			@return string
			@param $_file string
			@param $_path string
			@public
	**/
	public static function embed($_file,$_path) {
		if (!$_file || !file_exists($_path.$_file))
			return '';
		$_hash='tpl.'.self::hashCode($_file);
		$_cached=Cache::cached($_hash);
		if ($_cached && filemtime($_path.$_file)<$_cached['time']) {
			$_text=gzinflate(Cache::fetch($_hash));
			// Gather template file info for profiler
			F3::$global['PROFILE']['TEMPLATES']['cache']
				[$_file]=$_cached['size'];
		}
		else {
			$_text=file_get_contents($_path.$_file);
			Cache::store($_hash,gzdeflate($_text));
			// Gather template file info for profiler
			F3::$global['PROFILE']['TEMPLATES']['loaded']
				[$_file]=filesize($_path.$_file);
		}
		$_regex='/<(?:F3:)*include\h*href\h*=\h*"([^"]+)"\h*\/>/i';
		// Search/replace <F3:include> regex pattern
		if (!preg_match($_regex,$_text))
			return $_text;
		// Call recursively if included file also has <F3:include>
		return preg_replace_callback(
			$_regex,
			function($_attr) use($_path) {
				// Load file
				return F3::embed(F3::resolve($_attr[1]),$_path);
			},
			$_text
		);
	}

	/**
		Parse all directives and render HTML/XML template
			@return mixed
			@param $_file string
			@param $_ishtml boolean
			@param $_path string
			@public
	**/
	public static function serve($_file,$_ishtml=TRUE,$_path=NULL) {
		if (is_null($_path))
			$_path=self::fixSlashes(self::$global['GUI']);
		// Remove <F3::exclude> blocks
		$_text=preg_replace(
			'/<(?:F3:)?exclude>.*?<\/(?:F3:)?exclude>/is','',
			// Link <F3:include> files
			self::embed($_file,$_path)
		);
		if (preg_match('/<.+>/s',$_text)) {
			// Initialize XML tree
			$_tree=new XMLTree('1.0',self::$global['ENCODING']);
			// Suppress errors caused by invalid HTML structures
			libxml_use_internal_errors($_ishtml);
			// Populate XML tree
			if ($_ishtml) {
				// HTML template; Remember defined tags
				$_deftags=array(
					'/<!DOCTYPE\s+html.*?>\h*\v*/is'=>FALSE,
					'/<[\/]?html.*?>\h*\v*/is'=>FALSE,
					'/<[\/]?head.*?>\h*\v*/is'=>FALSE,
					'/<[\/]?body.*?>\h*\v*/is'=>FALSE
				);
				foreach ($_deftags as $_regex=>&$_tag)
					$_tag=preg_match($_regex,$_text);
				// Destroy reference
				unset($_tag);
				$_tree->loadHTML($_text);
			}
			else
				// XML template
				$_tree->loadXML($_text,LIBXML_COMPACT|LIBXML_NOERROR);
			// Prepare for XML tree traversal
			$_tree->fragment=$_tree->createDocumentFragment();
			$_2ndp=FALSE;
			$_tree->traverse(
				function() use($_tree,&$_2ndp) {
					$_node=&$_tree->nodeptr;
					$_tag=$_node->tagName;
					$_next=$_node;
					$_parent=$_node->parentNode;
					// Node removal flag
					$_remove=FALSE;
					if ($_tag=='repeat') {
						// Process <F3:repeat> directive
						$_inner=$_tree->innerHTML($_node);
						if ($_inner) {
							foreach ($_node->attributes as $_attr) {
								preg_match(
									'/\{*@(\w+\b(\[[^\]]+\]|\.\w+\b)*)\}*/',
										$_attr->value,$_cap);
								if (!$_cap[1] ||
									isset($_cap[2]) && 
									$_attr->name!='group') {
									// Invalid attribute
									F3::$global['CONTEXT']=$_attr->value;
									trigger_error(F3::TEXT_Attrib);
									return;
								}
								if ($_attr->name=='key')
									$_kvar='/@'.$_cap[1].'\b/';
								elseif ($_attr->name=='index')
									$_ivar='/@'.$_cap[1].'\b/';
								elseif ($_attr->name=='group') {
									$_gcap='@'.$_cap[1];
									$_gvar=F3::get($_cap[1]);
								}
							}
							if (is_array($_gvar) && count($_gvar)) {
								$_block='';
								// Iterate thru group elements
								foreach (array_keys($_gvar) as $_key) {
									$_block.=preg_replace($_ivar,
										// Replace index token
										$_gcap.'[\''.$_key.'\']',
										isset($_kvar)?
											// Replace key token
											preg_replace($_kvar,
												var_export($_key,TRUE),
												$_inner):
											$_inner
									);
								}
								if (isset($_block[0])) {
									$_tree->fragment->appendXML($_block);
									// Insert fragment before current node
									$_next=$_parent->
										insertBefore(
											$_tree->fragment,$_node
										);
								}
							}
						}
						$_remove=TRUE;
					}
					elseif ($_tag=='check' && !$_2ndp)
						// Found <F3:check> directive
						$_2ndp=TRUE;
					elseif (strpos($_tag,'-')) {
						// Process custom template directive
						list($_class,$_method)=explode('-',$_tag);
						$_found=FALSE;
						if (!class_exists($_class,FALSE))
							foreach (explode('|',F3::$global['AUTOLOAD'])
								as $_auto) {
								$_file=$_auto.$_class.'.php';
								// Case-insensitive check for file presence
								$_glob=glob(dirname($_file).'/*.php');
								$_fkey=array_search(
									strtolower($_file),
									array_map('strtolower',$_glob)
								);
								if (is_int($_fkey)) {
									include $_glob[$_fkey];
									if (method_exists($_class,'onLoad'))
										call_user_func(
											array($_class,'onLoad')
										);
									$_found=TRUE;
									break;
								}
							}
						else
							$_found=TRUE;
						if ($_found) {
							// Invoke template directive handler
							call_user_func(array($_class,$_method),$_tree);
							$_remove=TRUE;
						}
					}
					if ($_remove) {
						// Find next node
						if ($_node->isSameNode($_next))
							$_next=$_node->nextSibling?
								$_node->nextSibling:$_parent;
						// Remove current node
						$_parent->removeChild($_node);
						// Replace with next node
						$_node=$_next;
					}
				}
			);
			if ($_2ndp) {
				// Second pass; Template contains <F3:check> directive
				$_tree->traverse(
					function() use($_tree) {
						$_node=&$_tree->nodeptr;
						$_parent=$_node->parentNode;
						$_tag=$_node->tagName;
						// Process <F3:check> directive
						if ($_tag=='check') {
							$_cond=var_export(
								(boolean) F3::resolve(
									rawurldecode(
										$_node->getAttribute('if')
									)
								),TRUE
							);
							$_block='';
							foreach ($_node->childNodes as $_child)
								if ($_child->nodeType!=XML_TEXT_NODE &&
									$_child->tagName==$_cond) {
									$_inner=$_tree->innerHTML($_child);
									if ($_inner)
										// Replacement
										$_block.=$_inner;
								}
							if (isset($_block[0])) {
								$_tree->fragment->appendXML($_block);
								$_parent->
									insertBefore($_tree->fragment,$_node);
							}
							// Remove current node
							$_parent->removeChild($_node);
							// Re-process parent node
							$_node=$_parent;
						}
					}
				);
			}
			$_text=self::resolve(
				rawurldecode(
					$_ishtml?$_tree->saveHTML():$_tree->saveXML()
				)
			);
			if ($_ishtml) {
				// Fix empty HTML tags
				$_text=preg_replace(
					'/<((?:area|base|br|col|frame|hr|img|input|'.
						'isindex|link|meta|param).*?)\/?>/is','<$1/>',
					$_text
				);
				// Remove tags inserted by libxml
				foreach ($_deftags as $_regex=>$_tag)
					if (!$_tag)
						$_text=preg_replace($_regex,'',$_text);
			}
			else
				$_text=self::xmlEncode($_text,TRUE);
			unset($_tree);
		}
		else
			// Plain text
			$_text=self::resolve($_text);
		// Remove control characters except whitespaces
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/','',$_text);
	}

	/**
		Allow PHP and user-defined functions to be used in templates
			@param $_str string
			@public
	**/
	public static function allow($_str='') {
		// Create lookup table of functions allowed in templates
		$_legal=array();
		// Get list of all defined functions
		$_dfuncs=get_defined_functions();
		foreach (explode('|',$_str) as $_ext) {
			$_funcs=array();
			if (extension_loaded($_ext))
				$_funcs=get_extension_funcs($_ext);
			elseif ($_ext=='user')
				$_funcs=$_dfuncs['user'];
			$_legal=array_merge($_legal,$_funcs);
		}
		// Remove prohibited functions
		$_illegal='/^('.
			'apache_|call|chdir|env|escape|exec|extract|fclose|fflush|'.
			'fget|file_put|flock|fopen|fprint|fput|fread|fseek|fscanf|'.
			'fseek|fsockopen|fstat|ftell|ftp_|ftrunc|get|header|http_|'.
			'import|ini_|ldap_|link|log_|magic|mail|mcrypt_|mkdir|ob_|'.
			'php|popen|posix_|proc|rename|rmdir|rpc|set_|sleep|stream|'.
			'sys|thru|unreg'.
		')/i';
		$_legal=array_merge(
			array_filter(
				$_legal,
				function($_func) use($_illegal) {
					return !preg_match($_illegal,$_func);
				}
			),
			// PHP language constructs that may be used in expressions
			array('array','isset')
		);
		self::$global['FUNCS']=array_map('strtolower',$_legal);
	}

	/**
		Return TRUE if function can be used in templates
			@return boolean
			@param $_func string
			@public
	**/
	public static function allowed($_func) {
		if (!isset(self::$global['FUNCS']))
			F3::allow(self::FUNCS_Default);
		return in_array($_func,self::$global['FUNCS']);
	}

	/**
		Call form field handler
			@param $_fields string
			@param $_funcs mixed
			@param $_tags string
			@param $_filter integer
			@param $_options mixed
			@public
	**/
	public static function input(
		$_fields,
		$_funcs,
		$_tags=NULL,
		$_filter=FILTER_UNSAFE_RAW,
		$_options=array()) {
			$_global=&self::$global;
			foreach (explode('|',$_fields) as $_field) {
				// Sanitize relevant globals
				$_php=$_SERVER['REQUEST_METHOD'].'|REQUEST|FILES';
				foreach (explode('|',$_php) as $_var)
					if (isset($_global[$_var][$_field]))
						$_global[$_var][$_field]=filter_var(
							self::scrub($_global[$_var][$_field],$_tags),
							$_filter,$_options
						);
				$_input=&$_global
					[isset($_global['FILES'][$_field])?'FILES':'REQUEST']
					[$_field];
				if (is_string($_funcs)) {
					// String passed
					foreach (explode('|',$_funcs) as $_func) {
						if (!is_callable($_func)) {
							// Invalid handler
							$_global['CONTEXT']=$_include;
							trigger_error(self::TEXT_Form);
						}
						else
							// Call lambda function
							call_user_func($_func,$_input,$_field);
					}
				}
				else {
					// Closure
					if (!is_callable($_funcs)) {
						// Invalid handler
						$_global['CONTEXT']=$_funcs;
						trigger_error(self::TEXT_Form);
					}
					else
						// Call lambda function
						call_user_func($_funcs,$_input,$_field);
				}
			}
	}

	/**
		Mock environment for command-line use and/or unit testing
			@param $_regex string
			@param $_params array
			@public
	**/
	public static function mock($_regex,array $_params=NULL) {
		// Override PHP globals
		list($_method,$_uri)=self::checkRoute($_regex);
		$_query=explode('&',parse_url($_uri,PHP_URL_QUERY));
		foreach ($_query as $_pair)
			if (strpos($_pair,'=')) {
				list($_var,$_val)=explode('=',$_pair);
				self::set($_method.'.'.$_var,$_val);
				self::set('REQUEST.'.$_var,$_val);
			}
		if (is_array($_params))
			foreach ($_params as $_var=>$_val) {
				self::set($_method.'.'.$_var,$_val);
				self::set('REQUEST.'.$_var,$_val);
			}
		self::set('SERVER.REQUEST_METHOD',$_method);
		self::set('SERVER.REQUEST_URI',$_uri);
	}

	/**
		Perform test and append result to TEST global variable
			@return string
			@param $_cond boolean
			@param $_pass string
			@param $_fail string
			@public
	**/
	public static function expect($_cond,$_pass=NULL,$_fail=NULL) {
		if (is_string($_cond))
			$_cond=self::resolve($_cond);
		$_text=$_cond?$_pass:$_fail;
		self::$global['TEST'][]=array(
			'result'=>(int)(boolean)$_cond,
			'text'=>is_string($_text)?
				self::resolve($_text):var_export($_text,TRUE)
		);
		return $_text;
	}

	/**
		Convenience method for sandboxing function/script
			@param $_funcs mixed
			@public
	**/
	public static function call($_funcs) {
		Runtime::call($_funcs);
	}

	/**
		Return array of runtime performance analysis data
			@return array
			@public
	**/
	public static function &profile() {
		$_profile=self::$global['PROFILE'];
		// Compute elapsed time
		$_profile['TIME']['start']=&self::$global['TIME'];
		$_profile['TIME']['elapsed']=microtime(TRUE)-self::$global['TIME'];
		// Reset PHP's stat cache
		foreach (get_included_files() as $_file)
			// Gather includes
			$_profile['FILES']['includes']
				[basename($_file)]=filesize($_file);
		// Compute memory consumption
		$_profile['MEMORY']['current']=memory_get_usage();
		$_profile['MEMORY']['peak']=memory_get_peak_usage();
		return $_profile;
	}

	/**
		Configure framework according to .ini file settings and cache
		auto-generated PHP code to speed up execution
			@param $_file string
			@public
	**/
	public static function config($_file) {
		// Generate hash code for config file
		$_hash='php.'.self::hashCode($_file);
		$_cached=Cache::cached($_hash);
		if ($_cached && filemtime($_file)<$_cached['time'])
			// Retrieve from cache
			$_save=gzinflate(Cache::fetch($_hash));
		else {
			if (!file_exists($_file)) {
				// .ini file not found
				self::$global['CONTEXT']=$_file;
				trigger_error(self::TEXT_Config);
				return;
			}
			// Map sections to framework methods
			$_map=array('global'=>'set','routes'=>'route','maps'=>'map');
			// Read the .ini file
			preg_match_all(
				'/\s*(?:\[(.+?)\]|(?:;.+?)*|(?:([^=]+)=(.+?)))(?:\v|$)/s',
					file_get_contents($_file),$_matches,PREG_SET_ORDER
			);
			$_cfg=array();
			$_ptr=&$_cfg;
			foreach ($_matches as $_match) {
				if ($_match[1]) {
					// Section header
					if (!isset($_map[$_match[1]])) {
						// Unknown section
						self::$global['CONTEXT']=$_section;
						trigger_error(self::TEXT_Section);
						return;
					}
					$_ptr=&$_cfg[$_match[1]];
				}
				elseif ($_match[2]) {
					$_csv=array_map(
						function($_val) {
							// Typecast if necessary
							return is_numeric($_val) ||
								preg_match('/^(TRUE|FALSE)\b/i',$_val)?
									eval('return '.$_val.';'):$_val;
						},
						str_getcsv($_match[3])
					);
					// Convert comma-separated values to array
					$_match[3]=count($_csv)>1?$_csv:$_csv[0];
					if (preg_match('/(.+?)\[(.*?)\]/',$_match[2],$_sub)) {
						if ($_sub[2])
							// Associative array
							$_ptr[$_sub[1]][$_sub[2]]=$_match[3];
						else
							// Numeric-indexed array
							$_ptr[$_sub[1]][]=$_match[3];
					}
					else
						// Key-value pair
						$_ptr[$_match[2]]=$_match[3];
				}
			}
			ob_start();
			foreach ($_cfg as $_section=>$_pair) {
				$_func=$_map[$_section];
				foreach ($_pair as $_key=>$_val)
					// Generate PHP snippet
					echo 'F3::'.$_func.'('.
						var_export($_key,TRUE).','.
						($_func=='set' || !is_array($_val)?
							var_export($_val,TRUE):self::listArgs($_val)).
					');'."\n";
			}
			$_save=ob_get_contents();
			ob_end_clean();
			// Compress and save to cache
			Cache::store($_hash,gzdeflate($_save));
		}
		// Execute cached PHP code
		eval($_save);
		if (self::$global['ERROR'])
			// Remove from cache
			Cache::remove($_hash);
	}

	/**
		Convert engineering-notated string to bytes
			@return integer
			@param $_str string
			@public
	**/
	public static function bytes($_str) {
		$_greek='KMGT';
		$_exp=strpbrk($_str,$_greek);
		return pow(1024,strpos($_greek,$_exp)+1)*(int)$_str;
	}

	/**
		Kickstart the framework
			@public
	**/
	public static function start() {
		// Get PHP settings
		$_ini=ini_get_all(NULL,FALSE);
		$_level=E_ALL^E_NOTICE;
		ini_set('error_reporting',$_level);
		// Intercept errors and send output to browser
		set_error_handler(
			function($_errno,$_errstr) {
				// Bypass if error suppression (@) is enabled
				if (error_reporting())
					F3::error($_errstr,500,debug_backtrace(FALSE));
			},
			$_level
		);
		// Do the same for PHP exceptions
		set_exception_handler(
			function($_xcpt) {
				if (!count($_xcpt->getTrace())) {
					// Translate exception trace
					$_trace=debug_backtrace(FALSE);
					$_arg=$_trace[0]['args'][0];
					$_trace=array(
						array(
							'file'=>$_arg->getFile(),
							'line'=>$_arg->getLine(),
							'function'=>'{main}',
							'args'=>array()
						)
					);
				}
				else
					$_trace=$_xcpt->getTrace();
				F3::error($_xcpt->getMessage(),$_xcpt->getCode(),$_trace);
				return;
				// PHP aborts at this point
			}
		);
		if (isset(self::$global)) {
			// Multiple framework instances not allowed
			trigger_error(self::TEXT_Instance);
			return;
		}
		// Hydrate framework variables
		$_base=self::fixSlashes(realpath('.')).'/';
		self::$global=array(
			'AUTOLOAD'=>$_base.'autoload/',
			'BASE'=>$_base,
			'DEBUG'=>FALSE,
			'ENCODING'=>'UTF-8',
			'FONTS'=>$_base,
			'GUI'=>$_base,
			'IMPORTS'=>$_base,
			'LOGS'=>$_base.'logs/',
			'MAXSIZE'=>self::bytes($_ini['post_max_size']),
			'QUIET'=>FALSE,
			'RELEASE'=>FALSE,
			'SYNC'=>self::SYNC_Default,
			'TEST'=>array(),
			'TIME'=>time(),
			'THROTTLE'=>0,
			'VERSION'=>self::TEXT_Version
		);
		if (!in_array('zlib',get_loaded_extensions())) {
			// ZLib required
			self::$global['CONTEXT']='zlib';
			trigger_error(self::TEXT_PHPExt);
			return;
		}
		// Use plain old output buffering as default
		$_handler=NULL;
		if (PHP_SAPI=='cli') {
			// Command line: Parse GET variables in URL, if any
			preg_match_all(
				'/[\?&]([^=]+)=([^&$]*)/',$_SERVER['REQUEST_URI'],
				$_matches,PREG_SET_ORDER
			);
			foreach ($_matches as $_match) {
				$_REQUEST[$_match[1]]=$_match[2];
				$_GET[$_match[1]]=$_match[2];
			}
			// Custom server name
			$_SERVER['SERVER_NAME']=strtolower($_SERVER['COMPUTERNAME']);
			// Convert URI to human-readable string
			$_SERVER['REQUEST_URI']=rawurldecode($_SERVER['REQUEST_URI']);
			self::mock('GET '.$_SERVER['argv'][1]);
		}
		// Use GZip compression if (1) browser supports GZip-encoded
		// data, (2) ZLib output compression is not set in PHP.INI, and
		// (3) Apache mod_deflate is not active
		elseif (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
			preg_match('/gzip|deflate/',$_SERVER['HTTP_ACCEPT_ENCODING']) &&
			!$_ini['zlib.output_compression'] &&
			function_exists('apache_get_modules') &&
			in_array('mod_deflate',apache_get_modules())) {
				// Use a conservative compression level
				ini_set('zlib.output_compression_level',self::GZIP_Compress);
				$_handler='ob_gzhandler';
		}
		ob_start($_handler);
		// Initialize profiler
		self::$global['PROFILE']['MEMORY']['start']=memory_get_usage();
		// Create convenience containers for PHP globals
		foreach (explode('|',self::PHP_Globals) as $_var) {
			self::$global[$_var]=&$GLOBALS['_'.$_var];
			if ($_ini['magic_quotes_gpc'] && preg_match('/^[GPCR]/',$_var))
				// Corrective action on PHP magic quotes
				array_walk_recursive(
					self::$global[$_var],
					function(&$_val) {
						$_val=stripslashes($_val);
					}
				);
		}
		// Initialize autoload stack
		spl_autoload_register('self::onLoad');
	}

	/**
		Intercept instantiation of objects in undefined classes
			@param $_class string
			@private
	**/
	private static function onLoad($_class) {
		foreach (explode('|',self::$global['AUTOLOAD']) as $_auto) {
			// Allow namespaced classes
			$_file=$_auto.self::fixSlashes($_class).'.php';
			// Case-insensitive check for file presence
			$_glob=glob(dirname($_file).'/*.php');
			$_fkey=array_search(
				strtolower($_file),array_map('strtolower',$_glob)
			);
			if (is_int($_fkey)) {
				include $_glob[$_fkey];
				if (method_exists($_class,'onLoad'))
					call_user_func(array($_class,'onLoad'));
				return;
			}
		}
		self::$global['CONTEXT']=$_class;
		trigger_error(self::TEXT_Class);
	}

	/**
		Intercept calls to undefined static methods
			@return mixed
			@param $_func string
			@param $_args array
			@public
	**/
	public static function __callStatic($_func,array $_args) {
		foreach (explode('|',self::$global['AUTOLOAD']) as $_auto) {
			// Proxy for method in autoload class if found
			foreach (glob($_auto.'*.php') as $_file) {
				$_class=strstr(basename($_file),'.php',TRUE);
				include_once $_file;
				if (method_exists($_class,'onLoad'))
					call_user_func(array($_class,'onLoad'));
				if (method_exists($_class,$_func))
					return call_user_func_array(
						array($_class,$_func),$_args
					);
			}
		}
		self::$global['CONTEXT']=__CLASS__.'::'.$_func;
		trigger_error(self::TEXT_Method);
		return FALSE;
	}

	/**
		Class constructor
			@public
	**/
	public function __construct() {
		// Prohibit use of framework as an object
		self::$global['CONTEXT']=__CLASS__;
		trigger_error(self::TEXT_Object);
	}

}

//! Framework cache engine
final class Cache {

	//@{
	//! Locale-specific error/exception messages
	const
		TEXT_Backend='Cache back-end is invalid',
		TEXT_Store='Unable to save {@CONTEXT} to cache',
		TEXT_Fetch='Unable to retrieve {@CONTEXT} from cache',
		TEXT_Clear='Unable to clear {@CONTEXT} from cache';
	//@}

	//! Level-1 cached object
	private static $l1cache;

	/**
		Initialize framework level-2 cache
			@return boolean
			@private
	**/
	private static function prep() {
		if (!isset(F3::$global['CACHE'])) {
			// Extensions usable as cache back-ends
			$_exts=array_intersect(
				explode('|','apc|xcache'),get_loaded_extensions()
			);
			foreach (array_keys($_exts,'') as $_null)
				unset($_exts[$_null]);
			$_exts=array_merge($_exts,array());
			F3::$global['CACHE']=$_exts[0]?:
				('folder='.F3::$global['BASE'].'cache/');
		}
		if (preg_match(
			'/^(?:(folder)\=(.+\/)|(apc)|(memcache)=(.+))|(xcache)/i',
			F3::$global['CACHE'],$_match)) {
			if ($_match[1]) {
				if (!file_exists($_match[2])) {
					if (!is_writable(dirname($_match[2])) &&
						function_exists('posix_getpwuid')) {
							$_uid=posix_getpwuid(posix_geteuid());
							F3::$global['CONTEXT']=array(
								$_uid['name'],realpath(dirname($_match[2]))
							);
							trigger_error(F3::TEXT_Write);
							return;
					}
					// Create the framework's cache folder
					mkdir($_match[2],0755);
				}
				// File system
				self::$l1cache=array('type'=>'folder','id'=>$_match[2]);
			}
			else {
				$_ext=strtolower($_match[3]?:($_match[4]?:$_match[6]));
				if (!extension_loaded($_ext)) {
					F3::$global['CONTEXT']=$_ext;
					trigger_error(F3::TEXT_PHPExt);
					return;
				}
				if ($_match[4]) {
					// Open persistent MemCache connection(s)
					// Multiple servers separated by semi-colon
					$_pool=explode(';',$_match[5]);
					$_mcache=NULL;
					foreach ($_pool as $_server) {
						// Hostname:port
						list($_host,$_port)=explode(':',$_server);
						if (is_null($_port))
							// Use default port
							$_port=11211;
						// Connect to each server
						if (is_null($_mcache))
							$_mcache=memcache_pconnect($_host,$_port);
						else
							memcache_add_server($_mcache,$_host,$_port);
					}
					// MemCache
					self::$l1cache=array('type'=>$_ext,'id'=>$_mcache);
				}
				else
					// APC and XCache
					self::$l1cache=array('type'=>$_ext);
			}
			self::$l1cache['current']=FALSE;
			return TRUE;
		}
		// Unknown back-end
		trigger_error(self::TEXT_Backend);
		return FALSE;
	}

	/**
		Store data in framework cache
			@return boolean
			@param $_name string
			@param $_data mixed
			@public
	**/
	public static function store($_name,$_data) {
		if (is_null(self::$l1cache) && !self::prep())
			return FALSE;
		$_key=$_SERVER['SERVER_NAME'].'.'.$_name;
		// Serialize data for storage
		$_time=time();
		// Add timestamp
		$_val=serialize(array($_time,$_data));
		// Instruct back-end to store data
		switch (self::$l1cache['type']) {
			case 'folder':
				$_ok=file_put_contents(
					self::$l1cache['id'].$_key,$_val,LOCK_EX
				);
				break;
			case 'apc':
				$_ok=apc_store($_key,$_val);
				break;
			case 'memcache':
				$_ok=memcache_set(self::$l1cache['id'],$_key,$_val);
				break;
			case 'xcache':
				$_ok=xcache_set($_key,$_val);
				break;
		}
		if (is_bool($_ok) && !$_ok) {
			F3::$global['CONTEXT']=$_name;
			trigger_error(self::TEXT_Store);
			return FALSE;
		}
		self::$l1cache['current']=array(
			'name'=>$_name,'data'=>$_data,'time'=>$_time
		);
		return TRUE;
	}

	/**
		Retrieve value from framework cache
			@return mixed
			@param $_name string
			@param $_quiet boolean
			@public
	**/
	public static function fetch($_name,$_quiet=FALSE) {
		if (is_null(self::$l1cache) && !self::prep())
			return FALSE;
		if (is_array(self::$l1cache['current']) &&
			self::$l1cache['current']['name']==$_name)
				return self::$l1cache['current']['data'];
		self::$l1cache['current']=FALSE;
		$_key=$_SERVER['SERVER_NAME'].'.'.$_name;
		$_val=FALSE;
		// Instruct back-end to fetch data
		switch (self::$l1cache['type']) {
			case 'folder':
				if (file_exists(self::$l1cache['id'].$_key)) {
					$_val=file_get_contents(self::$l1cache['id'].$_key);
				}
				break;
			case 'apc':
				$_val=apc_fetch($_key);
				break;
			case 'memcache':
				$_val=memcache_get(self::$l1cache['id'],$_key);
				break;
			case 'xcache':
				$_val=xcache_get($_key);
				break;
		}
		if (is_bool($_val) && !$_val) {
			// No error display if specified
			if (!$_quiet) {
				F3::$global['CONTEXT']=$_name;
				trigger_error(self::TEXT_Fetch);
			}
			return FALSE;
		}
		// Unserialize timestamp and data
		list($_time,$_data)=unserialize($_val);
		self::$l1cache['current']=array(
			'name'=>$_name,'data'=>$_data,'time'=>$_time
		);
		return $_data;
	}

	/**
		Delete variable from framework cache
			@return boolean
			@param $_name string
			@public
	**/
	public static function remove($_name) {
		if (is_null(self::$l1cache) && !self::prep())
			return FALSE;
		self::$l1cache['current']=FALSE;
		$_key=$_SERVER['SERVER_NAME'].'.'.$_name;
		// Instruct back-end to clear data
		$_ok=TRUE;
		switch (self::$l1cache['type']) {
			case 'folder':
				if (file_exists(self::$l1cache['id'].$_key))
					$_ok=unlink(self::$l1cache['id'].$_key);
				break;
			case 'apc':
				$_ok=apc_delete($_key);
				break;
			case 'memcache':
				$_ok=memcache_delete(self::$l1cache['id'],$_key);
				break;
			case 'xcache':
				$_ok=xcache_unset($_key);
				break;
		}
		if (is_bool($_ok) && !$_ok) {
			F3::$global['CONTEXT']=$_name;
			trigger_error(self::TEXT_Clear);
			return FALSE;
		}
		return TRUE;
	}

	/**
		Return FALSE if specified variable is not in cache; otherwise,
		return array containing Un*x timestamp and data size
			@return mixed
			@param $_name string
			@public
	**/
	public static function cached($_name) {
		return self::fetch($_name,TRUE)?
			array(
				'time'=>self::$l1cache['current']['time'],
				'size'=>strlen(self::$l1cache['current']['data'])
			):
			FALSE;
	}

}

//! Run-time services
final class Runtime {

	/**
		Provide sandbox for functions and import files to prevent direct
		access to framework internals and other scripts
			@param $_funcs mixed
			@public
	**/
	public static function call($_funcs) {
		if (is_string($_funcs)) {
			// Call each code segment
			foreach (explode('|',$_funcs) as $_func) {
				if ($_func[0]==':')
					// Run external PHP script
					include F3::get('IMPORTS').substr($_func,1).'.php';
				else
					// Call lambda function
					call_user_func($_func);
			}
		}
		else
			// Call lambda function
			call_user_func($_funcs);

	}

}

//! PHP DOMDocument extension
class XMLTree extends DOMDocument {

	//@{
	//! Default XMLTree settings
	public $formatOutput=FALSE;
	public $preserveWhiteSpace=FALSE;
	public $strictErrorChecking=FALSE;
	//@}

	//! Default DOMDocument fragment
	public $fragment;

	//! Node pointer
	public $nodeptr;


	/**
		Get inner HTML contents of node
			@return string
			@param $_node DOMElement
			@public
	**/
	public function innerHTML($_node) {
		return preg_replace(
			'/^<(\w+)\b[^>]*>(.*)<\/\1?>/s','$2',
			$_node->ownerDocument->saveXML($_node)
		);
	}

	/**
		General-purpose pre-order XML tree traversal
			@param $_pre mixed
			@param $_type integer
			@public
	**/
	public function traverse($_pre,$_type=XML_ELEMENT_NODE) {
		// Start at document root
		$_root=$this->documentElement;
		$_node=&$this->nodeptr;
		$_node=$_root;
		$_flag=FALSE;
		while (TRUE) {
			if (!$_flag) {
				// Call pre-order handler for specified node type
				if (is_null($_type) || $_node->nodeType==$_type)
					call_user_func($_pre);
				if ($_node->firstChild) {
					// Descend to branch
					$_flag=FALSE;
					$_node=$_node->firstChild;
					continue;
				}
			}
			if ($_node->isSameNode($_root))
				// Root node reached; Exit loop
				break;
			// Post-order sequence
			if ($_node->nextSibling) {
				// Stay on same level
				$_flag=FALSE;
				$_node=$_node->nextSibling;
			}
			else {
				// Ascend to parent node
				$_flag=TRUE;
				$_node=$_node->parentNode;
			}
		}
	}

}

// Quietly initialize the framework; NOT to be repeated
F3::start();

?>
