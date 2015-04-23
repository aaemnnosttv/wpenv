<?php namespace WpEnv;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Loader
{
    private $filepath;
    private $data;
    private $hooks_set;

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
    function load()
    {
        if ( ! file_exists($this->filepath) ) {
            throw new \Exception("WpEnv could not load your configuration file at $this->filepath");
        }

        if ( is_null( $this->data ) )
        {
            try {
                $this->data = Yaml::parse( $this->filepath );
            }
            catch ( ParseException $e ) {
                wp_die("<h1>There was a problem parsing {$this->filename}</h1>" . $e->getMessage(), 'WpEnv Error');
            }
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
    function required( array $keys )
    {
        if ( ! $this->data )
        $missing_keys = [];

        foreach ( $keys as $key )
        {
            if ( false !== strpos($key, '.') )
            {
                $data = $this->data;
                $hierarchy = explode('.', $key);

                while ( count( $hierarchy ) )
                {
                    // check the next link from the front of the chain
                    $check = array_shift($hierarchy);

                    if ( ! isset( $data[ $check ] ) ) {
                        $missing_keys[] = $key;
                        break; // no need to go deeper
                    }
                    else
                    {
                        // if it is set, update our data-check for the next loop
                        $data = $data[ $check ];
                    }
                }
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

    private function hooks()
    {
        $this->hooks_set = true;

        if ( ! empty( $this->data['options'] ) && is_array( $this->data['options'] ) )
        {
            $this->override_options();
        }
    }

    /**
     * Create an anonymous callback to use for the filter
     *
     * @param $option  the option name
     * @param $override  the override value
     *
     * @return \Closure
     */
    private function get_override_callback( $option, $override )
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

    private function override_options()
    {
        foreach ( $this->data[ 'options' ] as $option => $override )
        {
            $callback = $this->get_override_callback($option, $override);

            // pre_option "short circuit" filter..
            // If we knew we would want to completely replace the returned value, we use this filter
            // to bypass the others. We don't have a convention to set that yet, so we will
            // disable this for now to ensure that our override will take precedence.
            //
            // To disable this, we just need to return false.
            add_filter("pre_option_$option"    , '__return_false', 99999);

            // set value if it doesn't exist
            add_filter("default_option_$option", $callback, 99999, 1);
            // override return if it does
            add_filter("option_$option"        , $callback, 99999, 1);
        }
    }

    /**
     * Set override filters
     * Can only be called once WordPress plugin api has been loaded
     */
    public function enforce()
    {
        if ( $this->data && ! $this->hooks_set ) {
            $this->hooks();
        }
    }
}
