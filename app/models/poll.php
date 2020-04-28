<?php

class Poll
{
	public $name;
	
	private static function slugify($string){
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }
	
	public function create($question, $answers)
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		$sql = "INSERT INTO polls (id, question)
				VALUES (NULL, '{$question}');";

		if ($conn->query($sql) === TRUE) {
			$poll_id = $conn->insert_id;
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		
		foreach($answers as $answer)
		{
			$answer_alias = self::slugify(trim($answer));
			$sql = "INSERT INTO poll_options (id, poll_id, answer_text, answer_alias)
					VALUES (NULL, {$poll_id}, '{$answer}', '{$answer_alias}');";
			
			if ($conn->query($sql) === TRUE) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}
		$conn->close();
		
		
	}
	
	public function list_all()
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		
		$sql = "SELECT id, question
				FROM polls;";
				
		$result = $conn->query($sql);
		$data = $result->fetch_all(MYSQLI_ASSOC);
		return $data;
	}
	
	public function read($poll_id)
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		
		$sql = "SELECT a.id as poll_id, a.question, GROUP_CONCAT(b.answer_text SEPARATOR '----') as answers
				FROM polls a 
				INNER JOIN poll_options b ON a.id = b.poll_id
				WHERE a.id = {$poll_id}
				GROUP BY b.poll_id;";
				
		$result = $conn->query($sql);
		$data = $result->fetch_array(MYSQLI_ASSOC);
		$data['answer_output'] = str_replace('----', "\n", $data['answers']);
		return $data;
	}
	
	public function update($poll_id, $question, $answers)
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		$sql = "UPDATE polls SET question = '{$question}';";
					
		if ($conn->query($sql) === TRUE) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
			exit;
		}
		
		$sql = "SELECT answer_alias FROM poll_options WHERE poll_id = {$poll_id};";
		$result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
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
				$sql = "INSERT INTO poll_options (id, poll_id, answer_text)
						VALUES (NULL, {$poll_id}, '{$answer}');";
				
				if ($conn->query($sql) === TRUE) {
					echo "New record created successfully";
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
					exit;
				}
			}
		}
		
		foreach($answer_aliases as $alias)
		{
			if(!in_array($alias, $new_aliases))
			{
				$sql = "DELETE FROM poll_options WHERE answer_alias = '{$alias}';";
				if ($conn->query($sql) === TRUE) {
					echo "Alias deleted successfully";
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
					exit;
				}
			}
		}
		$conn->close();		
	}
	
	public function delete($poll_id)
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		$sql = "DELETE FROM polls WHERE id = {$poll_id};";

		if ($conn->query($sql) === TRUE) {
			echo "Poll Deleted successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
			exit;
		}
	}
	
	public function vote($poll_id)
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		
		$sql = "SELECT id as poll_id, question
				FROM polls 
				WHERE id = {$poll_id};";
				
		$result = $conn->query($sql);
		$data = $result->fetch_array(MYSQLI_ASSOC);
		
		$sql = "SELECT answer_text, answer_alias
				FROM poll_options 
				WHERE poll_id = {$poll_id};";
		$result = $conn->query($sql);
		$answer_data = $result->fetch_all(MYSQLI_ASSOC);
		
		$data['answers'] = $answer_data;
		
		$conn->close();
		
		return $data;
	}
	
	public function display_results($poll_id)
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		
		$sql = "SELECT id as poll_id, question
				FROM polls 
				WHERE id = {$poll_id};";
				
		$result = $conn->query($sql);
		$data = $result->fetch_array(MYSQLI_ASSOC);
		
		$sql = "SELECT answer_text, answer_alias
				FROM poll_options 
				WHERE poll_id = {$poll_id};";
		$result = $conn->query($sql);
		$answer_data = $result->fetch_all(MYSQLI_ASSOC);
		
		$sql = "SELECT response_id, COUNT(id) as total
				FROM poll_responses
				WHERE poll_id = {$poll_id}
				GROUP BY response_id;";
				

		$result = $conn->query($sql);
		$response_data = $result->fetch_all(MYSQLI_ASSOC);

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
		
		$conn->close();
		
		return $data;
	}
	
	public function answer($poll_id, $answer_alias)
	{
		$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		
		$user = $_SERVER['REMOTE_ADDR'];
		
		$sql_check = "SELECT id FROM poll_responses WHERE poll_id = {$poll_id} AND user_ip = '{$user}';";
		$check_result = $conn->query($sql_check);
		if(mysqli_num_rows($check_result) > 0){
			$sql = "UPDATE poll_responses SET response_id = '{$answer_alias}' WHERE user_ip = '{$user}';";
		}
		else
		{
			$sql = "INSERT INTO poll_responses (id, poll_id, response_id, user_ip)
					VALUES (NULL, {$poll_id}, '{$answer_alias}', '{$user}');";
		}	
		if ($conn->query($sql) === TRUE) {
			echo "Vote cast successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
			exit;
		}
	}
}