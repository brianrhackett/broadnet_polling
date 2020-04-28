<?php

class Polls extends Controller
{
	public function __construct()
	{
		
	}
	
	public function create()
	{
		$question = $_POST['question'];
		
		$answers = preg_split('/\r\n|\r|\n/', $_POST['answers']);
		
		$poll = $this->model('Poll');
		
		$poll->create($question, $answers);
	}
	
	public function edit($poll_id)
	{
		$poll = $this->model('Poll');
		
		$data = $poll->read($poll_id);
		
		$this->view('poll', $data);
	}
	
	public function update()
	{
		$question = $_POST['question'];
		
		$answers = preg_split('/\r\n|\r|\n/', $_POST['answers']);
		
		$poll = $this->model('Poll');

		$poll->update($_POST['id'], $question, $answers);
	}
	
	public function list_all()
	{
		$poll = $this->model('Poll');

		$data = $poll->list_all();
		
		$this->view('list', $data);
	}
	
	public function delete($poll_id)
	{
		$poll = $this->model('Poll');

		$poll->delete($poll_id);
		
		$this->list_all();
	}
	

	public function vote($poll_id)
	{
		$poll = $this->model('Poll');

		$data = $poll->vote($poll_id);
		
		$this->view('vote', $data);
	}
	
	public function answer($poll_id, $answer_alias)
	{
		$poll = $this->model('Poll');

		$data = $poll->answer($poll_id, $answer_alias);
		
		$this->display_results($poll_id);
	}
	
	public function display_results($poll_id)
	{
		$poll = $this->model('Poll');

		$data = $poll->display_results($poll_id);
		
		$this->view('results', $data);
	}
}