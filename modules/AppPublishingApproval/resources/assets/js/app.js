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