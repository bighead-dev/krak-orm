<?php

class Division extends Krak\Model
{
	protected $buddy_of = array(
		'Rider'
	);
	
	public function _before_save()
	{
		echo 'CALLBACKS WORKS' . PHP_EOL;
	}
}