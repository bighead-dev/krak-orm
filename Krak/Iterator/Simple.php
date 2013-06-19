<?php
namespace Krak\Iterator;

function simple_create(&$krak)
{
	return new Buffered($krak->result('stdClass'));
}
