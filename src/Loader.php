<?php namespace WpEnv;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Loader
{
    protected $filepath;
    protected $filename;
    protected $data;

    public function __construct( $filepath )
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
        if ( ! file_exists($this->filepath) ) {
            throw new \Exception("WpEnv could not load your configuration file at $this->filepath");
        }

        try {
            $this->data = Yaml::parse( $this->filepath );
        }
        catch ( ParseException $e ) {
            wp_die("<h1>There was a problem parsing {$this->filename}</h1>" . $e->getMessage(), 'WpEnv Error');
        }
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
        $missing_keys = [];

        foreach ( $keys as $key )
        {
            // handle hierarchical check
            if ( false !== strpos($key, '.') )
            {
                $data = $this->data;
                $hierarchy = explode('.', $key);

                while ( count( $hierarchy ) )
                {
                    // check the next link from the front of the chain
                    $check = array_shift($hierarchy);

                    if ( ! isset($data[ $check ]) ) {
                        $missing_keys[] = $key;
                        continue; // no need to go deeper
                    }
                    else
                    {
                        // if it is set, update our data-check for the next loop
                        $data = $data[ $check ];
                    }
                }
            }
            // the required key is a top-level key
            elseif ( ! isset($this->data[ $key ]) ) {
                $missing_keys[] = $key;
            }
        }

        if ( ! empty( $missing_keys ) )
        {
            throw new \Exception("Missing required keys: " . join(', ', $missing_keys));
        }
    }

    public function activate()
    {
        WpEnv::register_loader( $this );
    }

    protected function hooks()
    {
        $this->hooks_set = true;
    }

    /**
     * Create an anonymous callback to use for the filter
     *
     * @param $option  the option name
     * @param $override  the override value
     *
     * @return \Closure
     */
    protected function get_override_callback( $option, $override )
    {
        return function( $saved ) use ( $option, $override )
        {
            if ( $option && ! is_null( $override ) )
            {
                // need logic to determine replace or merge
                // maybe $override['_wpenv'] = 'override' ? or ['!{key}']
                // default: merge
                if ( is_array( $saved ) && is_array( $override ) )
                    return array_merge( $saved, $override );
                else
                    return $override;
            }

            return $saved;
        };
    }

    /**
     * Set override filters
     * Can only be called once WordPress plugin api has been loaded
     */
    public function enforce()
    {
        if ( $this->data ) {
            $this->hooks();
        }
    }
}
