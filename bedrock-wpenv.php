<?php
/**
 * Plugin Name: Bedrock WP Env
 * Description: Environment-specific control
 * Version: 0.1
 */
namespace Aaemnnosttv;

use Symfony\Component\Yaml\Yaml;

class WPEnv
{
	private static $instance;

	private $data;

	private $config_file;

	/**
	 * Initialize the class
	 * PROJECT_ROOT should be defined as the absolute path to the root directory where .env resides
	 * @return [type] [description]
	 */
	public static function init()
	{
		$config = defined('PROJECT_ROOT') ? path_join(PROJECT_ROOT,'.wpenv.yml') : null;
		
		if ( file_exists( $config ) && is_null( self::$instance ) )
		{
			self::$instance = new self( $config );
		}
	}

	private function __construct( $config )
	{
		$this->config_file = $config;

		$this->data = Yaml::parse( $config );

		$this->hooks();
	}

	private function hooks()
	{
		if ( ! empty( $this->data['options'] ) && is_array( $this->data['options'] ) )
		{
			foreach ( $this->data['options'] as $option => $override )
			{
				// set value if it doesn't exist
				add_filter( "default_option_$option", array($this, 'option_control'), 500, 1 );
				// override return if it does
				add_filter( "option_$option"        , array($this, 'option_control'), 500, 1 );
			}
		}
	}

	public function option_control( $saved )
	{
		$option   = explode('option_', current_filter(), 2);
		$option   = $option[1];
		$override = $this->get_override( 'options', $option );

		if ( $option && ! is_null( $override ) )
		{
			if ( is_array( $saved ) && is_array( $override ) )
				return array_merge( $saved, $override );
			else
				return $override;
		}

		return $saved;
	}

	function get_override( $key, $get )
	{
		return isset( $this->data[ $key ][ $get ] )
			? $this->data[ $key ][ $get ]
			: null;
	}

	private function dump_data()
	{
		ob_start();
		var_dump( $this->data );
		$dump = ob_get_clean();
		wp_die( "<pre>$dump</pre>" );
	}
}

WPEnv::init();
