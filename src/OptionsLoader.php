<?php namespace WpEnv;

class OptionsLoader extends Loader
{
    const KEY = 'options';

    protected function apply_overrides()
    {
        if ( !empty($this->data[ static::KEY ]) && is_array($this->data[ static::KEY ]) )
        {
            foreach ( $this->data[ static::KEY ] as $option => $override )
            {
                $callback = $this->get_override_callback($option, $override);

                // pre_option "short circuit" filter..
                // If we knew we would want to completely replace the returned value, we use this filter
                // to bypass the others. We don't have a convention to set that yet, so we will
                // disable this for now to ensure that our override will take precedence.
                //
                // To disable this, we just need to return false.
                add_filter("pre_option_$option", '__return_false', 99999);

                // set value if it doesn't exist
                add_filter("default_option_$option", $callback, 99999, 1);
                // override return if it does
                add_filter("option_$option", $callback, 99999, 1);
            }
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
    protected function get_override_callback( $option, $override )
    {
        return function( $saved ) use ( $option, $override )
        {
            if ( $option && ! is_null( $override ) )
            {
                // need logic to determine replace or merge
                // maybe $override['_wpenv'] = 'override' ? or ['!{key}']
                // default: merge
                if ( is_array( $saved ) && is_array( $override ) ) {
                    return array_merge( $saved, $override );
                } else {
                    return $override;
                }
            }

            return $saved;
        };
    }
}
