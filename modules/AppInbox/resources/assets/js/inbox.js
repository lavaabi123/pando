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


function click_complete(_this){
	$(".loading").show();
	var id = $(_this).attr('data-id');
	var conversation_id = $(_this).attr('data-conversation-id');
	
	var completed = $(_this).attr('data-completed');
	if(completed == 1){
		$.ajax({
			type: 'POST',
		dataType: 'JSON',
			url: 'inbox/make-post-uncomplete',
			data: {
				inbox_id: id,conversation_id:conversation_id
			},
			success: function (res) {
				$('.loading').hide();
				loadInboxList(1);
				iziToast.success({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: 'Marked Incomplete',
				});								
				$('#notifCount').html(res.inbox_count);
			}
		});			
	}else{
		$.ajax({
			type: 'POST',
		dataType: 'JSON',
			url: 'inbox/make-post-complete',
			data: {
				inbox_id: id,conversation_id:conversation_id
			},
			success: function (res) {
				$('.loading').hide();
				$('.inbox-'+id).hide();
				loadInboxList(1);
				iziToast.success({
					icon: 'fad fa-bells',
					title: '',
					position: 'bottomCenter',
					message: 'Marked Complete',
				});
				$('#notifCount').html(res.inbox_count);
			}
		});			
	}
}

function close_filter(fname, fvalue){	
	$('input[name="'+fname+'"][value="' + fvalue.toString() + '"]').prop("checked", false);
	loadInboxList();
}

function clear_form(){
	$('#filter_form')[0].reset();
}
function clear_detail_form(){
	$('#comment_form')[0].reset();
}

function load_detail(_this){
	$('.social-comment').removeClass('active');
	$(_this).closest('.social-comment').addClass('active');
	var conversation_id = $(_this).attr('data-conversation-id');
	var post_id = $(_this).attr('data-post-id');
	var id = $(_this).attr('data-id');
	var network = $(_this).attr('data-network');
	get_inbox_detail(conversation_id,post_id,id,network);
}


function open_list_tag(_this,table){
	$('#listtagmodal').modal('show');
	const selectedTags = $(_this).attr('data-tag-ids');  // The string variable with tag IDs
    const selectedIds = selectedTags.split(",");  // Split string into an array of values

    // Get all checkboxes with name 'select_tags[]'
    const checkboxes = document.querySelectorAll('input[name="select_tags[]"]');
	// If selectedTags is empty, uncheck all checkboxes
    if (selectedTags === "") {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false; // Uncheck all checkboxes
        });
    } else {
        // Otherwise, split the string into an array of selected IDs
        const selectedIds = selectedTags.split(","); 

        // Loop through all checkboxes
        checkboxes.forEach((checkbox) => {
            // If checkbox value is in the selectedIds array, check it
            if (selectedIds.includes(checkbox.value)) {
                checkbox.checked = true;
            } else {
                checkbox.checked = false; // Uncheck other checkboxes
            }
        });
    }
	$('#listtagmodal').find('.selected_inbox').attr('data-id',$(_this).attr('data-id'));
	$('#listtagmodal').find('.selected_inbox').attr('data-table',table);	
}

function assign_tag(){
	var inbox_id = $('#listtagmodal').find(".selected_inbox").attr('data-id');	
	var checkedValues = $('input[name="select_tags[]"]:checked').map(function() {
      return $(this).val();
    }).get();
	var table = $('#listtagmodal').find('.selected_inbox').attr('data-table');
	
	$(".loading").show();
	$.ajax({
		url: 'inbox/assign-tag',
		type: 'POST',
		data:{selected_tags:checkedValues,inbox_id:inbox_id,table:table},
		dataType:'json',
		error: function() {
		},
		success: function(res) {
			$(".loading").hide();
			$('#listtagmodal').modal('hide');
			
			var $element = $('.inbox-' + inbox_id).find('.tag-icon-ids');			
			if ($element.length > 0) {
				$element.attr('data-tag-ids', res.ids);
			}
			
			$('.tag-roles-'+inbox_id).html(res.html);
			
			
			iziToast.success({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: 'Tag has been added successfully',
			});
		}
	});	
}

function open_list_user(_this,table){
	$('#listusermodal').modal('show');
	const selectedusers = $(_this).attr('data-user-ids');  // The string variable with user IDs
	const selectedIds = selectedusers.split(",");  // Split string into an array of values

	// Get all checkboxes with name 'select_users[]'
	const checkboxes = document.querySelectorAll('input[name="select_users[]"]');
	// If selectedusers is empty, uncheck all checkboxes
	if (selectedusers === "") {
		checkboxes.forEach((checkbox) => {
			checkbox.checked = false; // Uncheck all checkboxes
		});
	} else {
		// Otherwise, split the string into an array of selected IDs
		const selectedIds = selectedusers.split(","); 

		// Loop through all checkboxes
		checkboxes.forEach((checkbox) => {
			// If checkbox value is in the selectedIds array, check it
			if (selectedIds.includes(checkbox.value)) {
				checkbox.checked = true;
			} else {
				checkbox.checked = false; // Uncheck other checkboxes
			}
		});
	}
	$('#listusermodal').find('.selected_inbox').attr('data-id',$(_this).attr('data-id'));
	$('#listusermodal').find('.selected_inbox').attr('data-table',table);	
}

function assign_user(){
	var inbox_id = $('#listusermodal').find(".selected_inbox").attr('data-id');	
	var checkedValues = $('input[name="select_users[]"]:checked').map(function() {
      return $(this).val();
    }).get();
	var table = $('#listusermodal').find('.selected_inbox').attr('data-table');
	
	$(".loading").show();
	$.ajax({
		url: 'inbox/assign-user',
		type: 'POST',
		data:{selected_users:checkedValues,inbox_id:inbox_id,table:table},
		dataType:'json',
		error: function() {
		},
		success: function(res) {
			$(".loading").hide();
			$('#listusermodal').modal('hide');
			
			var $element = $('.inbox-' + inbox_id).find('.user-icon-ids');			
			if ($element.length > 0) {
				$element.attr('data-user-ids', res.ids);
			}
			
			$('.user-roles-'+inbox_id).html(res.html);
			iziToast.success({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: 'user has been added successfully',
			});
		}
	});	
}


function favourite_toggle(_this,table){
	var inbox_id = $(_this).attr('data-id');
	var fav = ($(_this).attr('data-fav') == '0' ) ? 1 : 0;
	var fill_color = ($(_this).attr('data-fav') == '0' ) ? 'red' : '';
	var fav_msg = ($(_this).attr('data-fav') == '0' ) ? 'Added to Favourite!' : 'Removed from Favourite!';
	$.ajax({
		url: 'inbox/set-favourite',
		type: 'POST',
		data:{fav:fav,inbox_id:inbox_id,table:table},
		dataType:'json',
		error: function() {
		},
		success: function(res) {
			$(_this).attr('data-fav',fav);
			$(_this).find('svg').css('fill',fill_color);			
			(fav == 1) ? $(_this).addClass('is_fav') : $(_this).removeClass('is_fav');
			(fav == 1) ? $(_this).attr('title','Remove from Favourite!') : $(_this).attr('title','Add to Favorite!');
			(fav == 1) ? $(_this).attr('data-bs-original-title','Remove from Favourite!') : $(_this).attr('data-bs-original-title','Add to Favorite!');
			
			iziToast.success({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: fav_msg,
			});
		}
	});
	
}


function delete_inbox_message(id,table){
	$('.loading').show();
	$.ajax({
		type: 'POST',
		url: 'inbox/delete-message',
		data: {id:id,table:table},
		dataType: 'JSON',
		success: function (res) {
			$('.loading').hide();
			iziToast.show({
                theme: 'dark',
                icon: 'fad fa-bells',
                title: '',
                position: 'bottomCenter',
                message: 'success',
                backgroundColor: "#04c8c8",
                progressBarColor: 'rgb(255, 255, 255, 0.5)',
            });
			loadInboxList();
		}
	});
}
