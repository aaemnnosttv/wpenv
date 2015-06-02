WPEnv
=====

## Environment-specific Control for WordPress

Inspired by [phpdotenv](https://github.com/vlucas/phpdotenv) for establishing environment variables, WPEnv allows WordPress-specific configuration at the environment level.

At present, WPEnv simply provides a way for manipulating WordPress options (`get_option()`), by hard-coding the values in a `.wpenv.yml` file.

Unlike Dotenv, WPEnv values are defined in a Yaml file, which allows us to store complex values like arrays, integers or boolean values, rather than just strings.

WPEnv modifies the return value of the options we want to control.  Simple values are replaced by the values defined in `.wpenv.yml`.  
Arrays are merged on top of the returned value (if it is an array), otherwise all defined values simply become the returned value.

# Getting Started

