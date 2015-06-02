<?php namespace WpEnv;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

abstract class Loader
{
    protected $filepath;
    protected $filename;
    protected $data;

    public function __construct( $filepath = '.wpenv.yml' )
    {
        $this->filepath = $filepath;
        $this->filename = pathinfo($this->filepath, PATHINFO_FILENAME);
        $this->load();
    }

    /**
     * Attempt to parse the Yaml config and load the data
     * @throws \Exception
     */
    protected function load()
    {
        if ( ! file_exists($this->filepath) || ! is_readable($this->filepath) ) {
            throw new \Exception("WpEnv could not load your configuration file at {$this->filepath}");
        }

        $this->data = Yaml::parse( file_get_contents($this->filepath) );
    }

    /**
     * Check for keys which must be set
     * Uses object.property dot notation for nested levels
     *
     * @param array $keys
     *
     * @throws \Exception
     */
    public function required( array $keys )
    {
        $missing_keys = [ ];

        foreach ( $keys as $key )
        {
            if ( ! $found = $this->validate_data_is_set_for_key($key) ) {
                $missing_keys[ ] = $key;
            }
        }

        if ( $missing_keys )
        {
            throw new \Exception("Missing required keys: " . json_encode($missing_keys));
        }
    }

	/**
     * Register this loader to be activated
     */
    public function activate()
    {
        WpEnv::register_loader( $this );
    }


    /**
     * Enforce overrides
     * Can only be called once WordPress plugin api has been loaded
     */
    public function enforce()
    {
        if ( $this->data ) {
            $this->apply_overrides();
        }
    }

	/**
     * @return mixed
     */
    abstract protected function apply_overrides();

    /**
     * Parses a string key to check if the related internal data is set
     *
     * @param $key
     *
     * @return bool
     */
    protected function validate_data_is_set_for_key( $key )
    {
        // check to see if the key has children
        // i.e. : 'settings.thing.foo'
        if ( false !== strpos( $key, '.' ) )
        {
            $data      = $this->data;
            $hierarchy = explode( '.', $key );

            // loop through the hierarchy from left to right
            while ( count( $hierarchy ) )
            {
                // check the next link from the front of the chain
                $check = array_shift( $hierarchy );

                // if it is set, update our data-check for the next loop
                if ( isset( $data[ $check ] ) ) {
                    $data = $data[ $check ];
                } else {
                    return false;
                }
            }
        }
        // the key has no children (.)s - it must be a top-level key
        elseif ( ! isset( $this->data[ $key ] ) ) {
            return false;
        }

        // we made it through the gauntlet, success!

        return true;
    }

}
