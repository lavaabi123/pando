
<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-md">
<div class="modal-content">
<div  class="modal-header border-0">
<h4 id="add-user-modal-head" class="modal-title">Comment History & Collaboration</h4>
<button type="button" data-bs-dismiss="modal" class="btn btn-sm btn-light-danger btn-close-preview w-35 h-35 b-r-40 d-flex justify-content-center align-items-center"><i class="fad fa-times pe-0"></i></button>
</div>
<div class="modal-body scrollable max-h-400 py-0">
<div class="calendar-notes-approval-added">

	@if(!empty($comments))
		@foreach($comments as $vc => $v)	
			<div class="bg-light p-10 border mb-2 rounded note-items">
				<div class="d-flex align-items-start justify-content-between">
					<div>
						<h5 class="mb-0">{{ $v->fullname }}</h5>
						<small>{{ date("M d, Y H:i A",strtotime($v->created_at)) }}</small>
					</div>
					<div class="ml-auto d-flex align-item-center">
						<div class="me-2">
							<i class="fa fa-edit font-14 text-muted pointer" onclick="clicked_edit_approval(this, {{$v->id}})" role=button></i>
						</div>
						<div class="mx-1">
							<i class="fa fa-close font-20 text-muted pointer" onclick="delete_note_approval({{$v->id}})" role=button></i>
						</div>
					</div>
				</div>
				<p dir="auto" class="mt-3 mb-0 notetext">{{ $v->comment }}</p>
			</div>		
		@endforeach
	@endif
</div>
<div  class="calendar-notes-input mb-1">
	<textarea maxlength="1024" autofocus="" rows="5" placeholder="Write your comment (1,024 characters max)" class="form-control border rounded pb-5" id="approval_note_text"></textarea>
</div>
		<input type="hidden" id="grouping_data" value="{{ $grouping_data }}">
</div>
<div class="modal-footer justify-content-start border-0 pl-4 pr-3 py-3">
<button type="button" onclick="add_note_approval()" class="btn btn-primary mr-3 add_note_approval_btn">Save</button>
<button type="button" onclick="edit_note_approval()" data-id="" class="btn btn-primary mr-3 edit_note_approval_btn" style="display:none;">Save</button>
<button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cancel</button></div>
</div>
</div>
</div>

<script type="text/javascript">
    Main.init(false);
    AppPubishing.init(false);
    Files.init(false);

function delete_note_approval(id){
	$(".loading").show();
	$.ajax({
		url: '{{ route("app.publishing.comments.destroy") }}',
		type: 'POST',
		data:{id:id},
		dataType:'html',
		error: function() {
		},
		success: function(res) {
			$(".loading").hide();
			$('#commentModal').modal('hide');	
			iziToast.success({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: 'Comment has been deleted successfully',
			});
		}
	});
	
}

function clicked_edit_approval(_this,id){
	$("#approval_note_text").text($(_this).closest('.note-items').find('.notetext').text());
	$('.edit_note_approval_btn').attr('data-id',id);
	$('.add_note_approval_btn').hide();
	$('.edit_note_approval_btn').show();
}
function edit_note_approval(){
	var note_text = $("#approval_note_text").val();
	var id = $('.edit_note_approval_btn').attr('data-id');
	if(note_text != ''){
		$(".loading").show();
		$.ajax({
			url: '{{ route("app.publishing.comments.store") }}',
			type: 'POST',
			data:{id:id,comment:note_text},
			dataType:'json',
			error: function() {
			},
			success: function(res) {
				$(".loading").hide();
				$('#commentModal').modal('hide');				
				$("#approval_note_text").text('');
				$("#approval_note_text").val('');
				iziToast.success({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: 'Comment has been updated successfully',
				});
			}
		});
	}else{
		iziToast.error({
			icon: 'fad fa-bells',
			title: '',
			position: 'bottomCenter',
			message: 'Please enter note',
		});	
	}
}	
function add_note_approval(){
	console.log('adads');
	var grouping_data = $('#grouping_data').val();
	var comment = $("#approval_note_text").val();
	if(grouping_data != '' && comment != ''){
		$(".loading").show();
		$.ajax({
			url: '{{ route("app.publishing.comments.store") }}',
			type: 'POST',
			data:{grouping_data:grouping_data,comment:comment},
			dataType:'json',
			error: function() {
			},
			success: function(res) {
				$(".loading").hide();
				$('#commentModal').modal('hide');
				iziToast.success({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: 'Comment has been added successfully',
				});
			}
		});
	}else{
		iziToast.error({
			icon: 'fad fa-bells',
			title: '',
			position: 'bottomCenter',
			message: 'Please enter Comment',
		});	
	}
}
</script>