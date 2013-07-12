<?php

/*
|--------------------------------------------------------------------------
| Extensions
|--------------------------------------------------------------------------
|
| A list of extensions to load up for Krak on startup. These extensions must be
| placed in USER_PATH . Ext/ Each extension class MUST be in the Krak\Ext
| namespace.
|
| e.g.
| <?php
| namespace Krak\Ext;
|
| class Extension {}
|
| If multiple extensions use the same function names then the latest extension
| loaded will overwrite the previous extensions with the same name. Extensions
| are static and are only created once across every krak object. You can always
| load up extension at runtime also.
|
*/

$config['extensions'] = array();
