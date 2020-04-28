<?php

class Home extends Controller
{
	protected $content; 

	public function index($name = '')
	{
		$poll = $this->model('Poll');

		$this->view('poll', []);
	}
}