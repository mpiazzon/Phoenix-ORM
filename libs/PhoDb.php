<?php
/*
 * This file is part of the Phoenix package
 *
 * (c) 2011 Martin Piazzon
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once  'PhoDbBaseInterface.php';
require_once  'PhoDbBase.php';

class PhoDb
{
    protected static $_connections = array();
	protected static $_config = array();

    public static function factory($database = NULL, $new = false)
    {
		if (! $database) {
            $database = 'Production';
        }

		if (isset(self::$_connections[$database])) {
			return self::$_connections[$database];
        }

		return self::connect($database);
    }

	public static function configure($key, $value = NULL)
	{
		if (is_null($value)) {
            $value = $key;
            $key   = 'connection_string';
        }

		self::$_config[$key] = $value;
	}

   	private static function connect($database)
    {
    	$baseDir = dirname(__FILE__);

		if (array_key_exists('connection_string', self::$_config)) {
			$explode = explode(":",self::$_config['connection_string']);
			self::$_config['type'] = $explode[0];
			self::$_config['dsn'] = $explode[1];
			$class = 'Pdo'.ucfirst($explode[0]);
           	$file = $baseDir . '/drivers/pdo/'. $class . '.php';
		}

	    if (! include_once $file) {
			throw new Exception('Driver not found: $dbclass');
		}

		return new $class(self::$_config);
    }
}
