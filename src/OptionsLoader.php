<?php namespace WpEnv;

class OptionsLoader extends Loader
{
    const KEY = 'options';

    protected function hooks()
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
}
