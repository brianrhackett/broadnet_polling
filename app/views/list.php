<div class="container">
<h2 class="m-2">All Polls</h2>
<br/>
<?php foreach($data as $poll):?>
	<div class="row mb-2">
		<div class="col-md-8">
			<?php echo $poll['question'];?>
		</div>
		<div class="col-md-4 align-right">
			<a class="btn btn-success" href="/polls/vote/<?php echo $poll['id'];?>">Vote</a>
			<a class="btn btn-primary" href="/polls/edit/<?php echo $poll['id'];?>">Edit</a>
			<a class="btn btn-danger" href="/polls/delete/<?php echo $poll['id'];?>">Delete</a>
		</div>
	</div>
<?php endforeach;?>
	<div class="row mb-2">
		<div class="col-md-8">
			<a class="btn btn-primary" href="/">Create New Poll</a>
		</div>
	</div>
</div>