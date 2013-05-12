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
		//$this->test_save();
		//$this->test_update();
		//$this->test_get();
		//$this->test_related();
		$this->test_count();
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
		$r[1]->get();
		echo $r[1][0]->data . PHP_EOL;
		
		$r->get(10, 10);
		
		echo "DELETING ALL\N";
		$r->delete_all();
		
		foreach ($r as $ro)
		{
			$ro->data = 'yo00';
			$ro->delete();
		}
	}
	
	public function test_related()
	{
		$v = new Rider\Video();
		$v->data = '3';
		$r = new Rider();
		$r->id = 1;
		//$r->save_relation($v);
		//echo $v->rider_id . PHP_EOL;
		$r->save($v);
		
		$r->rider_video->get();
		$v->id = 3;
		
		$r->id = 58;
		$v->save_relation($r);
		echo $v->rider_id . PHP_EOL;
		
		//$v->rider_id = 3;
		//$v->rider->get();
		
		$d = new Division();
		$d->data = 'asd';
		$id = $d->save();
		$d->id = $id;
		
		$data = array();
		$r->save_relation($d, $data);
		print_r($data);
		
		$v->id = 10;
		$r->save(array($d, $v));
		$r->delete($v);
		
		$data = array();
		$r->delete($d);
		
		//$r->delete(array($d, $v));
	}
	
	public function test_count()
	{
		$r = new Rider();
		$v = $r->get();
		echo count($v) . PHP_EOL;
		echo $v->num_rows() . PHP_EOL;
	}
}

/* End of file krak.php */
/* Location: ./application/controllers/krak.php */
