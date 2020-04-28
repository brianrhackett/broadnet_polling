<div class="container">
	<h3 class="m-2"><?php echo $data['question']?></h3>
	<div class="">
		<?php foreach($data['answers'] as $answer):?>
			<div class="row mb-2">
				<div class="col-12">
					<a class="btn btn-block btn-primary" href="/polls/answer/<?php echo $data['poll_id'];?>/<?php echo $answer['answer_alias'];?>"><?php echo $answer['answer_text'];?></a>
				</div>
			</div>
		<?php endforeach;?>
	</div>
</div>