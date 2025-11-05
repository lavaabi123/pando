"use strict";
  
$(document).ready(function() {
        loadInboxList();
    });

    function loadInboxList(page = 1) {
        const formData = $('#filter_form').serialize() + '&page=' + page;
        $.ajax({
            url: routes.inboxAjax,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#inbox-list-container').html(response.list);
                $('#inbox-detail-container').html(response.list_detail);
                
                if (response.filter_text) {
                    $('.filtered-list').show();
                    $('.load-filter-text').html(response.filter_text);
                } else {
                    $('.filtered-list').hide();
                }
            },
            error: function(xhr) {
                $('#inbox-list-container').html('<div class="alert alert-danger">Error loading inbox. Please try again.</div>');
            }
        });
    }

    function applyFilter() {
        loadInboxList(1);
    }

    function resetFilter() {
        $('#filter_form')[0].reset();
        loadInboxList(1);
    }

    function pagechange(page) {
        $('#pagenos').val(page);
        loadInboxList(page);
    }
	
	
	function add_tag_modal(){
		$('#addtagmodal').modal('show');
	}	
	
	function add_tag(){
		var tag_name = $("#tag_name_add").val();
		if(tag_name != ''){
			$(".loading").show();
			$.ajax({
				url: routes.addTag,
				type: 'POST',
				data:{tag_name:tag_name},
				dataType:'json',
				error: function() {
				},
				success: function(res) {
					$(".loading").hide();
					$('#addtagmodal').modal('hide');
					$('#flush-collapseTags').find('.accordion-body ul.load-tag-list').append(res.html);
					iziToast.success({
						icon: 'fad fa-bells',
						title: '',
						position: 'bottomCenter',
						message: 'Tag has been added successfully',
					});
				}
			});
		}else{
			iziToast.error({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: 'Please enter tag name',
			});	
		}
	}
	function clear_tag_selected(){
		$("input[name='tags[]']:checkbox").prop('checked',false);
	}

	
$(document).ready(function() {	
    // Attach an onclick event handler to checkboxes with name "tags[]"
    $('input[name="tags[]"]').on('click', function() {
        // Get the value of the clicked checkbox
        var checkboxValue = $(this).val();

        // Check if the value is "0"
        if (checkboxValue === "0") {
			if ($(this).prop('checked')) {            
				// hide other option
				$('input[name="tags[]"]').not(':first').prop('checked', false);
				// Set opacity of all <li> elements to 0.3
				$('input[name="tags[]"]').closest('li').not(':first').css('opacity', '0.3');
				$('input[name="tags[]"]').closest('li').not(':first').css('pointer-events', 'none');
			}else{
				$('input[name="tags[]"]').prop('checked', false);
				$('input[name="tags[]"]').closest('li').css('opacity', '1');
				$('input[name="tags[]"]').closest('li').css('pointer-events', 'auto');
			}
        } else {
			if ($(this).prop('checked')) {   
				// hide first option
				$('input[name="tags[]"]:first').prop('checked', false);
				$('#flush-collapseOne ul li:first').css('opacity', '0.3');
				$('#flush-collapseOne ul li:first').css('pointer-events', 'none');
			}else{
				if($('input[name="tags[]"]:checked').not($('li:first input')).length == 0){
					$('input[name="tags[]"]:first').prop('checked', false);
					$('#flush-collapseOne ul li:first').css('opacity', '1');
					$('#flush-collapseOne ul li:first').css('pointer-events', 'auto');
				}
			}
        }
    });
});
function loadDetail(element) {
    const id = $(element).data('id');
    const type = $(element).data('type');
    
    $('.inbox-item').removeClass('active');
    $(element).addClass('active');
	var conversation_id = $(element).data('conversation-id');
	var post_id = $(element).data('post-id');
	var network = $(element).data('network');
    
    // Load detail view via AJAX
	get_inbox_detail(conversation_id,post_id,id,network);
    // Implementation depends on your specific requirements
}

function get_inbox_detail(conversation_id,post_id,id,network){	
	$('.loading').show();
	//Load Inbox List
	$.ajax({
		type: 'POST',
		url: routes.inboxDetail,
		data: {conversation_id:conversation_id,post_id:post_id,id:id,network:network},
		dataType: 'JSON',
		success: function (res) {
			$('.loading').hide();
			$('#inbox-detail-container').html(res.list_detail);
		},
		error: function(xhr) {
		}
	});	
}
