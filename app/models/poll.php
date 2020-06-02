<?php

class Poll
{
	public $name;
	
	private static function slugify($string){
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }
	
	public function create($question, $answers)
	{
		$db = new Db();
		
		$question_arr = array(
			"question" => $question
		);
		
		try
		{
			$poll_id = $db->create('polls', $question_arr);
		}
		catch (Exception $e)
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		
		foreach($answers as $answer)
		{
			$answer_alias = self::slugify(trim($answer));
			$answer_arr = array(
				"poll_id" => $poll_id,
				"answer_text" => $answer,
				"answer_alias" => $answer_alias,
			);
			try
			{
				$db->create('poll_options', $answer_arr);
			}
			catch (Exception $e)
			{
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}
	}
	
	public function list_all()
	{
		$db = new Db();
		$column_arr = array(
			'id',
			'question'
		);
		$data = $db->read('polls', $column_arr);
		return $data;
	}
	
	public function read($poll_id)
	{
		$db = new Db();
		$column_arr = array(
			'poll_options.poll_id',
			'polls.question',
			'GROUP_CONCAT(poll_options.answer_text SEPARATOR \'----\') AS answers'
		);
		
		$conditions = array(
			array(
				'column' => 'polls.id',
				'value' => $poll_id
			)
		);
		
		$join_tables = array(
			array(
				'type' => 'INNER JOIN',
				'name' => 'poll_options',
				'condition' => ' polls.id = poll_options.poll_id',
			)
		);
		
		$group_by = 'poll_options.poll_id';
		
		$data = $db->read('polls', $column_arr, $conditions, $join_tables, $group_by);
		$data = array_shift($data);
		
		$data['answer_output'] = str_replace('----', "\n", $data['answers']);
		return $data;
	}
	
	public function update($poll_id, $question, $answers)
	{
		$db = new Db();
		
		// update question
		$column_arr = array(
			'question' => $question
		);
		$conditions_arr = array(
			'id' => $poll_id
		);
		$db->update('polls', $column_arr, $conditions_arr);
		
		$options_conditions_arr = array(
			'poll_id' => $poll_id
		);
		
		
		//add new response values
		$result = $db->read('poll_options', 'answer_alias', $options_conditions_arr);
		$answer_aliases = array_map(function($e){
			return $e['answer_alias'];
		}, $result);	
		
		$new_aliases = [];
		foreach($answers as $answer)
		{
			$answer_alias = self::slugify(trim($answer));
			$new_aliases[] = $answer_alias;
			if(!in_array($answer_alias, $answer_aliases))
			{
				$columns_arr = array(
					'poll_id' => $poll_id,
					'answer_text' => $answer
				);
				$db->create('poll_options', $columns_arr);
			}
		}
		
		// DELETE response values that were removed
		foreach($answer_aliases as $alias)
		{
			if(!in_array($alias, $new_aliases))
			{
				$delete_conditions = array(
					'answer_alias' => $alias
				);
				$db->delete('poll_options', $delete_conditions);
			}
		}
	}
	
	public function delete($poll_id)
	{
		$db = new Db();
		$delete_conditions = array(
			'poll_id' => $poll_id
		);
		$db->delete('poll_options', $delete_conditions);

		$delete_conditions = array(
			'id' => $poll_id
		);
		$db->delete('polls', $delete_conditions);
	}
	
	public function vote($poll_id)
	{
		$db = new Db();
		
		//get question data
		$columns_arr = array('id', 'question');
		$conditions_arr = array(
			array(
				'column' => 'id',
				'value' => $poll_id
			)
		);
		$data = $db->read('polls', $columns_arr, $conditions_arr);
		$data = array_shift($data);
		
		// get answer data
		$columns_arr = array('answer_text, answer_alias');
		$conditions_arr = array(
			array(
				'column' => 'poll_id',
				'value' => $poll_id
			)
		);
		$answer_data = $db->read('poll_options', $columns_arr, $conditions_arr);
		$data['answers'] = $answer_data;

		return $data;
	}
	
	public function display_results($poll_id)
	{
		$db = new Db();
		
		// get question data
		$columns = array('id', 'question');
		$condition = array(
			array(
				'column' => 'id',
				'value' => $poll_id,
			)
		);
		$data = $db->read('polls', $columns, $condition);
		$data = array_shift($data);
		
		// get answer data
		$columns_arr = array('answer_text, answer_alias');
		$conditions_arr = array(
			array(
				'column' => 'poll_id',
				'value' => $poll_id
			)
		);
		$answer_data = $db->read('poll_options', $columns_arr, $conditions_arr);
		
		
		// get response data
		$columns_arr = array('response_id, COUNT(id) as total');
		$conditions_arr = array(
			array(
				'column' => 'poll_id',
				'value' => $poll_id
			)
		);
		$response_data = $db->read('poll_responses', $columns_arr, $conditions_arr, NULL, NULL, 'response_id');

		$total_votes = 0;
		foreach($response_data as $response)
		{
			$total_votes += $response['total'];
		}
		foreach($answer_data as &$answer)
		{
			$response = array_filter($response_data, function($e) use ($answer){
				return $e['response_id'] == $answer['answer_alias'];
			});
			if(count($response)){
				$answer['raw_total'] = $response[0]['total'];
				$answer['percentage'] = (round($response[0]['total']/$total_votes, 2) * 100) .'%';
			}
		}
		$data['answers'] = $answer_data;
		
		return $data;
	}
	
	public function answer($poll_id, $answer_alias)
	{
		$db = new Db();
		$user = $_SERVER['REMOTE_ADDR'];
		$check_conditions = array(
			array(
				'column' => 'poll_id',
				'value' => $poll_id,
			),
			array(
				'column' => 'user_ip',
				'value' => $user,
			),
		);
		$check = $db->read('poll_responses', 'poll_id', $check_conditions);
		
		if(count($check) > 0)
		{
			$columns = array(
				'response_id' => $answer_alias
			);
			$conditions = array(
				'user_ip' => $user
			);
			$db->update('poll_responses', $columns, $conditions);
		} 
		else
		{
			$columns = array(
				'poll_id' => $poll_id,
				'response_id' => $answer_alias,
				'user_ip' => $user
			);
			$db->create('poll_responses', $columns);
		}
		
	}
}