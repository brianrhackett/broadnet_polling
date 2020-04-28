<div class="container">
	<h2><?php echo (isset($data['poll_id'])) ? 'Edit Poll' : 'Create New Poll';?></h2>
	
	<form name="create_poll" id="create_poll" action="/polls/<?php echo isset($data['poll_id']) ? 'update' : 'create';?>" method="POST">
		<?php if(isset($data)):?>
			<input type="hidden" name="id" value="<?php echo $data['poll_id'];?>" />
		<?php endif;?>
		<div class="row form-group">
			<div class="col-md-4">
				<label for="question">Poll Question</label>
			</div>
			<div class="col-md-8">
				<input type="text" id="question" name="question" class="form-control" value="<?php echo isset($data['question']) ? $data['question'] : '';?>"  />
			</div>
		</div>

		<div class="row form-group">
			<div class="col-md-4">
				<label for="answers">Poll Options <br /> (use new line for each)</label>
			</div>
			<div class="col-md-8">
				<textarea id="answers" name="answers" class="form-control" rows="3"><?php echo isset($data['answer_output']) ? $data['answer_output'] : '';?></textarea>
			</div>
		</div>
		
		<div class="row form-group">
			<div class="col-md-8">
				<button type="submit" class="btn btn-primary btn-block">Save Poll</button>
			</div>
			<div class="col-md-4">
				<a type="button" class="btn btn-danger btn-block" href="/polls/list_all">Cancel</a>
			</div>
		</div>
	</form>
</div>