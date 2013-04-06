<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Krak extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		
		require_once './application/third_party/Krak/Model.php';
		echo 'REQUIRE TEST PASSED!' . PHP_EOL;
	}

	public function index()
	{
		// run the tests here
		$this->test_autoload();
		//$this->test_init();
		
		$this->db->krak_debug = TRUE;
		$this->test_save();
		$this->test_update();
		$this->test_get();
		$this->db->krak_debug = FALSE;
		
		//$this->db->query('TRUNCATE TABLE riders');
	}
	
	public function test_autoload()
	{
		$r = new Rider();
		
		if ($r instanceof Krak\Model)
		{
			echo "AUTOLOAD TEST PASSED!\n";
		}
		else
		{
			echo "AUTOLOAD TEST FAILED!\n";		
		}
	}
	
	public function test_init()
	{
		$r = new Rider();
		
		echo "ITER CLASS: " . Rider::$iter_class . "\n";
		
		print_r($r);
	}
	
	public function test_save()
	{
		$r = new Rider();
		$r->data = 'a';
		$r->save();
		$r->data = 'b';
		$r->save();
	}
	
	public function test_update()
	{
		$r = new Rider();
		$r->id = 1;
		$r->data = 'A';
		$r->save();
		
		$r->id = 2;
		$r->data = 'B';
		$r->save();
	}
	
	public function test_get()
	{
		$r = new Rider();
		$r->get();
		$r->id = 1;
		$r[0]->data = '129e';
		$r[0]->save();
		$r[1]->delete();
	}
}

/* End of file krak.php */
/* Location: ./application/controllers/krak.php */