<?php

namespace Krak;

/**
 * The base directory to run the autoloading from.
 * 
 * Normally it's: APPPATH . 'models/'
 */
const BASE_DIR = './application/models/';

/**
 * The base namespace to load your models from.
 *
 * Set this variable to whatever namespace your models are located in.
 * This namespace won't affect the table naming for Krak.
 * Leave empty if you want to leave in the global namespace.
 * 
 * example value: 'Km'
 * Make sure to **NOT** include the backward slash at the end
 */
const MODEL_NS = '';
