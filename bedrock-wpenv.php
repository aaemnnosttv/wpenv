<?php namespace WpEnv;
/**
 * Plugin Name: WpEnv
 * Description: Environment-specific control
 * Version: 1.0
 */

// load classes whether installed as a package or standalone
$autoloader = __DIR__ . '/vendor/autoload.php';
if ( file_exists($autoloader) ) require $autoloader;

/*
 * Initialize and activate any registered loaders
 */
WpEnv::init();
