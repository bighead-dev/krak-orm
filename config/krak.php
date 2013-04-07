<?php

/*
|--------------------------------------------------------------------------
| Extensions
|--------------------------------------------------------------------------
|
| A list of extensions to load up for Krak. These extensions must be
| placed in application/krakatoa/. Each extension class should be in the Krak
| namespace.
|
| e.g.
| <?php
| namespace Krak;
|
| class Extension {}
|
|	http://example.com/
|
| If multiple extensions use the same function names then the latest extension
| loaded will overwrite the previous extensions with the same name. Extensions
| are static and are only created once across every krak object.
|
*/

$config['extensions'] = array();

/*
|--------------------------------------------------------------------------
| Iterator
|--------------------------------------------------------------------------
|
| The default iterator to use for query results. The two options are Buffered
| and Unbuffered.
| Buffered - this iterator will store the entire result set in an array to allow
| for much faster looping. Buffered is much faster than Unbuffered for a few
| reasons. First, the buffered iterator is basically just an ArrayIterator which
| is a native php class which is written in c and is much faster than building your
| own iterator. The unbuffered iterator uses mysqli->fetch_object to get a single
| result at a time and it uses a handmade iterator which adds overhead.
| Unbuffered - this iterator is slower, but is more memory sensitive. Use when needed.
|
| Krak will use the Buffered iterator by default
| If you want to create your own iterator then place your new iterator in the Krak/Iterator/
| directory and make sure it's a proper iterator, then put the actual class name of your new
| iterator as the parameter.
|
*/

$config['iterator'] = 'Buffered';