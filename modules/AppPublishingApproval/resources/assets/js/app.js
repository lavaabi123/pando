function movetoapp(id,_this){
		$(".loading").show();
		$.ajax({
		url: PATH+'approvals/move_to_queue',
		type: 'POST',
		data:{id:id},
		dataType:'json',
		error: function() {
		},
		success: function(res) {
			if(res.status == 'error'){
				iziToast.error({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: res.message,
				});
			}else{
				$(_this).closest('.finalmaindiv').hide();
				iziToast.success({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: res.message,
				});
			}
			$(".loading").hide();
		}
		});
	}
	

$(document).ready(function () {
	$('#notes_modal_approval').on('shown.bs.modal', function() { 
		$('.edit_note_approval_btn').attr('data-id','');
		$('.add_note_approval_btn').show();
		$('.edit_note_approval_btn').hide();
	});
});


function delete_note_approval(id){
	$.ajax({
		url: PATH+'approvals/delete_note/'+ id,
		type: 'POST',
		data:{},
		dataType:'html',
		error: function() {
		},
		success: function(res) {
			get_note();
			iziToast.success({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: 'Comment has been deleted successfully',
			});
		}
	});
	
}
function get_note(){
	$(".loading").show();
	$(".calendar-notes-approval-added").html('');
	var approval_id = $('#note_approval_id').val();
	$.ajax({
		url: PATH+'approvals/get_note/'+ approval_id,
		type: 'POST',
		data:{},
		dataType:'html',
		error: function() {
		},
		success: function(res) {
			$(".calendar-notes-approval-added").html(res);
			$(".loading").hide();
		}
	});
}

function open_approval_notes_modal(id){
	$(".calendar-notes-approval-added").html('');
	$('#note_approval_id').val(id);
	$('#notes_modal_approval').modal('show');
	get_note()
}
function add_note_approval(){
	var approval_id = $('#note_approval_id').val();
	var note_text = $("#approval_note_text").val();
	if(approval_id != '' && note_text != ''){
		$(".loading").show();
		$.ajax({
			url: PATH+'approvals/add_note',
			type: 'POST',
			data:{approval_id:approval_id,note_text:note_text},
			dataType:'json',
			error: function() {
			},
			success: function(res) {
				$(".loading").hide();
				$('#notes_modal_approval').modal('hide');
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
function click_edit_approval(_this,id){
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
			url: PATH+'approvals/edit_note/'+id,
			type: 'POST',
			data:{note_text:note_text},
			dataType:'json',
			error: function() {
			},
			success: function(res) {
				$(".loading").hide();
				$('#notes_modal_approval').modal('hide');
				
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

    $(function(){
        Layout.carousel();
    });
	function movetoapp(id,_this){
		$(".loading").show();
		$.ajax({
		url: PATH+'approvals/move_to_queue',
		type: 'POST',
		data:{id:id},
		dataType:'json',
		error: function() {
		},
		success: function(res) {
			if(res.status == 'error'){
				iziToast.error({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: res.message,
				});
			}else{
				$(_this).closest('.finalmaindiv').hide();
				iziToast.success({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: res.message,
				});
			}
			$(".loading").hide();
		}
		});
	}
	function load_preview_approval(id) {
		$(".loading").show();
		$('#calendarModalvbap').find('.modal-body').html('');
		$.ajax({
		url: PATH+'schedules/load_preview',
		type: 'POST',
		data:{ids:id,from:'approval'},
		dataType:'html',
		error: function() {
		},
		success: function(res) {
			$('#calendarModalvbap').find('.modal-body').html(res);
			$('#calendarModalvbap').find('.pv-header').hide();
			$('#calendarModalvbap').find('.pv-body .text-over-all a').html(event.name);
			$('#calendarModalvbap').find('.pv-body .text-over-all span').html(event.time);
			$(".loading").hide();
		}
		});
		$('#calendarModalvbap').modal('show');
	}
	$(document).on('click', ".actionApprovalconfirm", function(event) {
		event.preventDefault();   
		var searchIDs = $("input[name='approval_post_ids[]']:checked").map(function(){
		  return $(this).val();
		}).get();
		if (searchIDs.length === 0) {
			alert('Please select at least 1 post to download');
		}else{
			$.ajax({
				url: PATH+'approvals/move_to_queue_bulk',
				type: 'POST',
				data:{ids:searchIDs},
				dataType:'json',
				error: function() {
				},
				success: function(res) {
					if(res.status == 'error'){
						iziToast.error({
							icon: 'fad fa-bells',
							title: '',
							position: 'bottomCenter',
							message: res.message,
						});
					}else{
						$('input[name="approval_post_ids[]"]:checked').each(function() {
							$(this).closest('.finalmaindiv').hide();
						});
						if($('input[name="approval_post_ids[]"]:visible').length == 0){
							$('.load-empty-message').html('<div class="text-center"><h3 class="mb-4 mt-4">There are no approvals</h3></div>');
						}
						iziToast.success({
							icon: 'fad fa-bells',
							title: '',
							position: 'bottomCenter',
							message: res.message,
						});
					}
					$(".loading").hide();
				}
			});
		}
		
	});
	
	
	$('.sort-chronological').on('click', function () {
    var $this = $(this);
    var order = $this.attr('data-order'); // 'asc' or 'desc'
    var posts = $('.finalmaindiv:visible');

    posts.sort(function (a, b) {
        var t1 = new Date($(a).data('time')).getTime();
        var t2 = new Date($(b).data('time')).getTime();
        return order === 'asc' ? t1 - t2 : t2 - t1;
    });

    $.each(posts, function (i, post) {
        $(post).parent().append(post); // adjust if needed
    });

    // Toggle label and order
    if (order === 'asc') {
        $this.attr('data-order', 'desc');
        $this.html('<i class="fa fa-sort me-2"></i> Sort by Newest First');
    } else {
        $this.attr('data-order', 'asc');
        $this.html('<i class="fa fa-sort me-2"></i> Sort by Oldest First');
    }
});
