<?php

namespace Krak;

/*
 * Add any additional requires or autoloaders here.
 * Make sure you return an instance of Krak\iLoader
 * that points to your models directory.
 */

/**
 * Set to true if you want the Base NS to act as a table prefix
 * or false if you don't want the base model prefix to show up
 * in the table names.
 */
const USE_NS_AS_PREFIX = false;

/**
 * Must return an instance of Krak\iLoader
 */
return new Loader('Km', './application/models/');
