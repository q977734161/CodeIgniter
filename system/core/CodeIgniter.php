<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System Initialization File
 *
 * Loads the base classes and executes the request.
 *
 * @package		CodeIgniter
 * @subpackage	CodeIgniter
 * @category	Front-controller
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/
 */

/**
 * CodeIgniter Version
 *
 * @var	string
 *
 */
	define('CI_VERSION', '3.1.0-dev');

/*
 * ------------------------------------------------------
 *  Load the framework constants
 *  加载框架constants信息，先检查生产环境下即 controller/config/development/
 *     controller/config/testing
 *     controller/config/production
 *     文件夹下的constants.php的文件最后加载controller/config/文件夹下的
 *  constants.php中使用defined定义各种常量
 * ------------------------------------------------------
 */
	if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php'))
	{
		require_once(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
	}

	require_once(APPPATH.'config/constants.php');

/*
 * ------------------------------------------------------
 *  Load the global functions
 *  加载全局函数
 * ------------------------------------------------------
 */
	require_once(BASEPATH.'core/Common.php');


/*
 * ------------------------------------------------------
 * Security procedures
 * ------------------------------------------------------
 */

if ( ! is_php('5.4'))
{
	ini_set('magic_quotes_runtime', 0);

	if ((bool) ini_get('register_globals'))
	{
		$_protected = array(
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_REQUEST',
			'_SESSION',
			'_ENV',
			'_COOKIE',
			'GLOBALS',
			'HTTP_RAW_POST_DATA',
			'system_path',
			'application_folder',
			'view_folder',
			'_protected',
			'_registered'
		);

		$_registered = ini_get('variables_order');
		foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $superglobal)
		{
			if (strpos($_registered, $key) === FALSE)
			{
				continue;
			}
			/**
			 * 如果register_globals 开启，会将$_ENV,$_GET等这些变量x的值设置为全局变量
			 * 检查x是否为全局变量是的话过滤，不是既保留,只保留$_protected中的变量
			 */
			foreach (array_keys($$superglobal) as $var)
			{
				if (isset($GLOBALS[$var]) && ! in_array($var, $_protected, TRUE))
				{
					$GLOBALS[$var] = NULL;
				}
			}
		}
	}
}

/*
 *----------------------改进的建议----------------------------- 
 * 能否将$_protected改成如下格式数组
	if ((bool) ini_get('register_globals'))
	{
		$_protected = array(
			'_SERVER'=>true,
			'_GET'=>true,
			'_POST'=>true,
			'_FILES'=>true,
			'_REQUEST'=>true,
			'_SESSION'=>true,
			'_ENV'=>true,
			'_COOKIE'=>true,
			'GLOBALS'=>true,
			'HTTP_RAW_POST_DATA'=>true,
			'system_path'=>true,
			'application_folder'=>true,
			'view_folder'=>true,
			'_protected'=>true,
			'_registered'=>true,
		);

		查找的时候使用array_key_exists,查找key的值会比查找value快很多
			foreach (array_keys($$superglobal) as $var)
			{
				if (isset($GLOBALS[$var]) && ! array_key_exists($var, $_protected))
				{
					$GLOBALS[$var] = NULL;
				}
			}
		}

 * 
 */



/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 *  设置异常，错误，关闭自定义函数，相关函数在Common.php中
 * ------------------------------------------------------
 */
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');

/*
 * ------------------------------------------------------
 *  Set the subclass_prefix
 *  检查是否设置类的前缀
 * ------------------------------------------------------
 *
 * Normally the "subclass_prefix" is set in the config file.
 * The subclass prefix allows CI to know if a core class is
 * being extended via a library in the local application
 * "libraries" folder. Since CI allows config items to be
 * overridden via data set in the main index.php file,
 * before proceeding we need to know if a subclass_prefix
 * override exists. If so, we will set this value now,
 * before any classes are loaded
 * Note: Since the config file data is cached it doesn't
 * hurt to load it here.
 */
	if ( ! empty($assign_to_config['subclass_prefix']))
	{
		get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
	}

/*
 * ------------------------------------------------------
 *  Should we use a Composer autoloader?
 *  是否使用第三方类自动加载文件
 * ------------------------------------------------------
 */
	if ($composer_autoload = config_item('composer_autoload'))
	{
		if ($composer_autoload === TRUE)
		{
			file_exists(APPPATH.'vendor/autoload.php')
				? require_once(APPPATH.'vendor/autoload.php')
				: log_message('error', '$config[\'composer_autoload\'] is set to TRUE but '.APPPATH.'vendor/autoload.php was not found.');
		}
		elseif (file_exists($composer_autoload))
		{
			require_once($composer_autoload);
		}
		else
		{
			log_message('error', 'Could not find the specified $config[\'composer_autoload\'] path: '.$composer_autoload);
		}
	}

/*
 * ------------------------------------------------------
 *  Start the timer... tick tock tick tock...
 *  加载性能测试类，设置开始加载的时间
 * ------------------------------------------------------
 */
	$BM =& load_class('Benchmark', 'core');
	$BM->mark('total_execution_time_start');
	$BM->mark('loading_time:_base_classes_start');

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 *  加载系统钩子
 * ------------------------------------------------------
 */
	$EXT =& load_class('Hooks', 'core');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 *  调用pre_system钩子，执行系统初始化前的工作
 * ------------------------------------------------------
 */
	$EXT->call_hook('pre_system');

/*
 * ------------------------------------------------------
 *  Instantiate the config class
 * ------------------------------------------------------
 *
 * Note: It is important that Config is loaded first as
 * most other classes depend on it either directly or by
 * depending on another class that uses it.
 * 加载系统配置
 *
 */
	$CFG =& load_class('Config', 'core');

	// Do we have any manually set config items in the index.php file?
	if (isset($assign_to_config) && is_array($assign_to_config))
	{
		foreach ($assign_to_config as $key => $value)
		{
			$CFG->set_item($key, $value);
		}
	}

/*
 * ------------------------------------------------------
 * Important charset-related stuff
 * 导入文字编码相关
 * ------------------------------------------------------
 *
 * Configure mbstring and/or iconv if they are enabled
 * and set MB_ENABLED and ICONV_ENABLED constants, so
 * that we don't repeatedly do extension_loaded() or
 * function_exists() calls.
 *
 * Note: UTF-8 class depends on this. It used to be done
 * in it's constructor, but it's _not_ class-specific.
 *
 */
	$charset = strtoupper(config_item('charset'));
	ini_set('default_charset', $charset);

	if (extension_loaded('mbstring'))
	{
		define('MB_ENABLED', TRUE);
		// mbstring.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('mbstring.internal_encoding', $charset);
		// This is required for mb_convert_encoding() to strip invalid characters.
		// That's utilized by CI_Utf8, but it's also done for consistency with iconv.
		mb_substitute_character('none');
	}
	else
	{
		define('MB_ENABLED', FALSE);
	}

	// There's an ICONV_IMPL constant, but the PHP manual says that using
	// iconv's predefined constants is "strongly discouraged".
	if (extension_loaded('iconv'))
	{
		define('ICONV_ENABLED', TRUE);
		// iconv.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('iconv.internal_encoding', $charset);
	}
	else
	{
		define('ICONV_ENABLED', FALSE);
	}

	if (is_php('5.6'))
	{
		ini_set('php.internal_encoding', $charset);
	}

/*
 * ------------------------------------------------------
 *  Load compatibility features
 *  加载通用特性
 * ------------------------------------------------------
 */

	require_once(BASEPATH.'core/compat/mbstring.php');
	require_once(BASEPATH.'core/compat/hash.php');
	require_once(BASEPATH.'core/compat/password.php');
	require_once(BASEPATH.'core/compat/standard.php');

/*
 * ------------------------------------------------------
 *  Instantiate the UTF-8 class
 *  初始化utf-8类
 * ------------------------------------------------------
 */
	$UNI =& load_class('Utf8', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the URI class
 *  初始化uri类
 * ------------------------------------------------------
 */
	$URI =& load_class('URI', 'core', $CFG);

/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 *  初始化路由类
 * ------------------------------------------------------
 */
	$RTR =& load_class('Router', 'core', isset($routing) ? $routing : NULL);

/*
 * ------------------------------------------------------
 *  Instantiate the output class
 *  初始化output类
 * ------------------------------------------------------
 */
	$OUT =& load_class('Output', 'core');

/*
 * ------------------------------------------------------
 *	Is there a valid cache file? If so, we're done...
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('cache_override') === FALSE && $OUT->_display_cache($CFG, $URI) === TRUE)
	{
		exit;
	}

/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
	$SEC =& load_class('Security', 'core');

/*
 * ------------------------------------------------------
 *  Load the Input class and sanitize globals
 *  初始化input类
 * ------------------------------------------------------
 */
	$IN	=& load_class('Input', 'core');

/*
 * ------------------------------------------------------
 *  Load the Language class
 *  初始化语言类
 * ------------------------------------------------------
 */
	$LANG =& load_class('Lang', 'core');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 *  加载controller类
 * ------------------------------------------------------
 *
 */
	// Load the base controller class
	require_once BASEPATH.'core/Controller.php';

	/**
	 * Reference to the CI_Controller method.
	 *
	 * Returns current CI instance object
	 *
	 * 获取框架controller实例
	 * CI_Controller单例模式
	 * 1.初始化所有已经加载的类，该类名的小写格式为controller的成员变量
	 * 2.设置类加载工具$this->load
	 * 3.初始化要自动加载的类
	 * @return CI_Controller
	 */
	function &get_instance()
	{
		return CI_Controller::get_instance();
	}

	if (file_exists(APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php'))
	{
		require_once APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php';
	}

	// Set a mark point for benchmarking
	$BM->mark('loading_time:_base_classes_end');

/*
 * ------------------------------------------------------
 *  Sanity checks
 * ------------------------------------------------------
 *
 *  The Router class has already validated the request,
 *  leaving us with 3 options here:
 *
 *	1) an empty class name, if we reached the default
 *	   controller, but it didn't exist;
 *	2) a query string which doesn't go through a
 *	   file_exists() check
 *	3) a regular request for a non-existing page
 *
 *  We handle all of these as a 404 error.
 *
 *  Furthermore, none of the methods in the app controller
 *  or the loader class can be called via the URI, nor can
 *  controller methods that begin with an underscore.
 *  
 *  1.第一次请求跳转到系统指定的controller中，没有指定404
 *  2.根据请求url解析出要跳转的controller及method
 *  
 *  
 */

	$e404 = FALSE;
	$class = ucfirst($RTR->class);
	$method = $RTR->method;

	if (empty($class) OR ! file_exists(APPPATH.'controllers/'.$RTR->directory.$class.'.php'))
	{
		$e404 = TRUE;
	}
	else
	{
		require_once(APPPATH.'controllers/'.$RTR->directory.$class.'.php');

		if ( ! class_exists($class, FALSE) OR $method[0] === '_' OR method_exists('CI_Controller', $method))
		{
			$e404 = TRUE;
		}
		elseif (method_exists($class, '_remap'))
		{
			$params = array($method, array_slice($URI->rsegments, 2));
			$method = '_remap';
		}
		// WARNING: It appears that there are issues with is_callable() even in PHP 5.2!
		// Furthermore, there are bug reports and feature/change requests related to it
		// that make it unreliable to use in this context. Please, DO NOT change this
		// work-around until a better alternative is available.
		elseif ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($class)), TRUE))
		{
			$e404 = TRUE;
		}
	}

	if ($e404)
	{
		if ( ! empty($RTR->routes['404_override']))
		{
			if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2)
			{
				$error_method = 'index';
			}

			$error_class = ucfirst($error_class);

			if ( ! class_exists($error_class, FALSE))
			{
				if (file_exists(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php'))
				{
					require_once(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php');
					$e404 = ! class_exists($error_class, FALSE);
				}
				// Were we in a directory? If so, check for a global override
				elseif ( ! empty($RTR->directory) && file_exists(APPPATH.'controllers/'.$error_class.'.php'))
				{
					require_once(APPPATH.'controllers/'.$error_class.'.php');
					if (($e404 = ! class_exists($error_class, FALSE)) === FALSE)
					{
						$RTR->directory = '';
					}
				}
			}
			else
			{
				$e404 = FALSE;
			}
		}

		// Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
		if ( ! $e404)
		{
			$class = $error_class;
			$method = $error_method;

			$URI->rsegments = array(
				1 => $class,
				2 => $method
			);
		}
		else
		{
			show_404($RTR->directory.$class.'/'.$method);
		}
	}

	if ($method !== '_remap')
	{
		$params = array_slice($URI->rsegments, 2);
	}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 *  在实例化controller之前要执行的工作
 * ------------------------------------------------------
 */
	$EXT->call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the requested controller
 * ------------------------------------------------------
 */
	// Mark a start point so we can benchmark the controller
	$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

	$CI = new $class();

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 *  在controller实例化执行方法前的工作
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller_constructor');

/*
 * ------------------------------------------------------
 *  Call the requested method
 *  调用controller方法
 * ------------------------------------------------------
 */
	call_user_func_array(array(&$CI, $method), $params);

	// Mark a benchmark end point
	$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 *  controller方法执行完成后的工作
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('display_override') === FALSE)
	{
		$OUT->_display();
	}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 *  所有工作完成以后要执行的工作
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_system');
