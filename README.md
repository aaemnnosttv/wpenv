Bedrock WPEnv
=============

## Environment-specific Control for [Roots/Bedrock](https://github.com/roots/bedrock) WordPress installs

Similar to the way Bedrock uses Dotenv for establishing environment variables, WPEnv allows WordPress-specific configuration at the environment level.

At present, WPEnv simply provides a way for manipulating WordPress options (`get_option()`), by hard-coding the values in a `.wpenv.yml` file.

Unlike Dotenv, we are defining our values in a Yaml file, which allows us to store complex values like arrays, integers or boolean values, rather than just strings.

WPEnv modifies the return value of the options we want to control.  Simple values are replaced by the values defined in `.wpenv.yml`.  Arrays are merged on top of the returned value (if it is an array), otherwise all defined values simply become the returned value.

# Requirements

- Bedrock v1.3.0
- Requires `"symfony/yaml": "~2.6"`

# Getting Started

We need to define a constant so that WPEnv can load the config file.
Edit `config/application.php`, near the top, just under the `$root_dir` add `define( 'WPENV_ROOT', $root_dir );`.

Copy `.wpenv.sample.yml` from `mu-plugins/bedrock-wpenv` to the same directory as `.env`.
Rename the new file to `.wpenv.yml`.
Edit to your heart's content.
