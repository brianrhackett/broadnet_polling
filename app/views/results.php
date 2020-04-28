<div class="container">
	<h3 class="m-2"><?php echo $data['question']?></h3>
	<?php foreach($data['answers'] as $answer):?>
	<div class="row mb-2">
		<div class="col-6">
			<?php echo $answer['answer_text'];?>
		</div>
		<div class="col-3">
			<?php echo isset($answer['raw_total']) ? $answer['raw_total'] : 0;?> votes
		</div>
		<div class="col-3">
			<?php echo isset($answer['percentage']) ? $answer['percentage'] : '0%';?>
		</div>
	</div>
<?php endforeach;?>
</div>