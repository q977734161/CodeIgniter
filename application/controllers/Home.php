<?php
class Home extends CI_Controller
{
	public function index()
	{
		//缓存本页面
		//$this->output->cache($n);
		//删除缓存
		//$this->output->delete_cache()
		//启动分析器
		//$this->output->enable_profiler(TRUE);
		$csrf = array(
				'name' => $this->security->get_csrf_token_name(),
				'hash' => $this->security->get_csrf_hash()
		);
		$newdata = array(
				'username'  => 'johndoe',
				'email'     => 'johndoe@some-site.com',
				'logged_in' => TRUE
		);
		
		$this->session->set_userdata($newdata);
		//将seesion中的username标记为只使用一次
		$this->session->mark_as_flash('username');
		
		
		$this->load->library('calendar');
		
		$data = array(
				3  => 'http://example.com/news/article/2006/03/',
				7  => 'http://example.com/news/article/2006/07/',
				13 => 'http://example.com/news/article/2006/13/',
				26 => 'http://example.com/news/article/2006/26/'
		);
		
		echo $this->calendar->generate(2006, 6, $data);
		//$this->load->view('home/index',array('csrf'=>$csrf));
	}
	
	
// 	public function _remap($method,$params)
// 	{
// 			if (method_exists($this, $method))
// 		    {
// 		        return call_user_func_array(array($this, $method), $params);
// 		    }else {
// 				$this->otherMethod();
// 		}
// 	}
	
	public function otherMethod()
	{
		echo "other";
	}
	
	public function test()
	{
		$name = $_POST['name'];
		$name = $this->security->xss_clean($name);
		print_r($this->session->userdata());
		echo $name;
	}
}