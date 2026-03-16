"use strict";

var AppPubishing = new (function () 
{
    var AppPubishing = this;
    var CalendarMain = null;      // Main calendar instance
    var CalendarCompose = null;   // Compose calendar instance
    var calendarNotesData = {};
    /*
    * FULL CALENDAR
     */
    var CALENDAR_SELECTORS = {
        "TITLE": '.calendar-title',
        "HEADER": '.calendar-header',
        "MAIN": '.main',
    };

    AppPubishing.init = function( reload ) 
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': VARIABLES.csrf
            }
        });

        // Install the publish response interceptor once on init.
        // This intercepts the publishing/save AJAX call before Main.js processes
        // the response — giving us full control over the success/error display and redirect.
        // Guard against double-install on page re-init.
        if (!window._pandoPublishInterceptorInstalled) {
            window._pandoPublishInterceptorInstalled = true;
            AppPubishing.installPublishInterceptor();
        }

        if(reload || reload == undefined){
            
            AppPubishing.Calendar();
            AppPubishing.CalendarTitle();
            AppPubishing.CalendarEvents();
            AppPubishing.CalendarHeight();
            AppPubishing.CalendarAction();
            AppPubishing.Actions();
        }

        if ( $(".composer-scheduling").length > 0 )
        {
            AppPubishing.previewAction();
            AppPubishing.preview();
			setTimeout(function() {
				AppPubishing.updateCharacterCountIndicators();
				
				// Only update counts if emojioneArea is ready
				if ($(".post-caption").length > 0 && $(".post-caption")[0].emojioneArea) {
					AppPubishing.updateCharacterCounts();
				}
			}, 1000);
        }

    },
	
	AppPubishing.initn = function( reload ) 
    {
		
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': VARIABLES.csrf
            }
        });

        // Install the publish response interceptor once on init.
        // This intercepts the publishing/save AJAX call before Main.js processes
        // the response — giving us full control over the success/error display and redirect.
        // Guard against double-install on page re-init.
        if (!window._pandoPublishInterceptorInstalled) {
            window._pandoPublishInterceptorInstalled = true;
            AppPubishing.installPublishInterceptor();
        }

        if(reload || reload == undefined){
            
            AppPubishing.CalendarCompose();
            AppPubishing.CalendarTitleCompose();
			AppPubishing.CalendarEventsCompose();
            AppPubishing.CalendarHeightCompose();
            AppPubishing.CalendarActionCompose();
        }

        if ( $(".composer-scheduling").length > 0 )
        {
            AppPubishing.previewAction();
            AppPubishing.preview();
			setTimeout(function() {
				AppPubishing.updateCharacterCountIndicators();
				
				// Only update counts if emojioneArea is ready
				if ($(".post-caption").length > 0 && $(".post-caption")[0].emojioneArea) {
					AppPubishing.updateCharacterCounts();
				}
			}, 1000);
        }

    },

    AppPubishing.Actions = function(){
        $(document).on("click", ".closeCompose", function(){
            AppPubishing.closeCompose();
        });

        $(document).on("click", ".showCompose", function(){
            $(".compose-media,.compose-preview").removeClass("active");
        });

        $(document).on("click", ".showMedia", function(){
            $(".compose-media").addClass("active");
        });

        $(document).on("click", ".showPreview", function(){
            $(".compose-preview").addClass("active");
        });

        var type = $(".compose-type input:checked").val();
        AppPubishing.composeType(type);

        $(document).on("change", ".compose-type input", function(){
            type = $(this).val();
            AppPubishing.composeType(type);
        });

        $(document).on("change", ".compose select[name='post_by']", function(){
            var that = $(this);
            var type = $(this).val();
            $(".compose .post-by").addClass("d-none");
            $(".compose .post-by[data-by='"+type+"']").removeClass("d-none").show();

            if(type == 1){
                $(".btnPostNow").removeClass("d-none");
                $(".btnSchedulePost").addClass("d-none");
                $(".btnSaveApproval").addClass("d-none");
                $(".btnSaveDraft").addClass("d-none");
            }else if(type == 4){
                $(".btnPostNow").addClass("d-none");
                $(".btnSchedulePost").addClass("d-none");
                $(".btnSaveApproval").addClass("d-none");
                $(".btnSaveDraft").removeClass("d-none");
            }else if(type == 5){
                $(".btnPostNow").addClass("d-none");
                $(".btnSchedulePost").addClass("d-none");
                $(".btnSaveApproval").removeClass("d-none");
                $(".btnSaveDraft").addClass("d-none");
            }else{
                $(".btnPostNow").addClass("d-none");
                $(".btnSchedulePost").removeClass("d-none");
                $(".btnSaveApproval").addClass("d-none");
                $(".btnSaveDraft").addClass("d-none");
            }
        });

        $(document).on("click", ".compose .addSpecificDays", function(){
            var that = $(this);
            var item = $(".tempPostByDays").find(".item"); 
            var c = item.clone();
            c.find("input").attr("name", "time_posts[]").addClass("datetime").val("");
            $(".listPostByDays").append(c);
            Main.DateTime();

            if( $(".compose .listPostByDays .remove").length > 1 ){
                $(".compose .listPostByDays .remove").removeClass("disabled");
            }

            return false;
        });

        $(document).on("click", ".compose .listPostByDays .remove:not(.disabled)", function(){
            var that = $(this);
            that.parents(".item").remove();

            if( $(".compose .listPostByDays .remove").length < 2 ){
                $(".compose .listPostByDays .remove").addClass("disabled");
            }
        });
    },

    AppPubishing.previewAction = function(){
        function channelChanges() {
            var elements = document.querySelectorAll('.am-selected-list .am-selected-item');
            if (elements.length > 0) {
                $('.cpv-empty').addClass('d-none');
            }else{
                $('.cpv-empty').removeClass('d-none');
            }
            AppPubishing.preview();
			AppPubishing.updateCharacterCountIndicators();
        }

        // Setup MutationObserver
        var container = document.querySelector('.am-selected-list');
        if (container) {
            var fb_observer = new MutationObserver(channelChanges);
            fb_observer.observe(container, {
                childList: true,
                subtree: false,
                attributes: true
            });

            channelChanges();
        }

        if ($(".post-caption").length > 0) 
        {
            $(".post-caption")[0].emojioneArea.on("keyup", function(editor, event) {
                var text = $(".post-caption")[0].emojioneArea.getText();
                var content = editor.html();
                editor.parents(".wrap-input-emoji").find('.count-word span').html( text.length );
				AppPubishing.updateCharacterCounts();
                if(text != ""){
                    $(".cpv-text").html(content);
                }else{
                    $(".cpv-text").html('<div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1 wp-50"></div>');
                }
            });

            $(".post-caption")[0].emojioneArea.on("change", function(editor, event) {
                var text = $(".post-caption")[0].emojioneArea.getText();
                var content = editor.html();
                editor.parents(".wrap-input-emoji").find('.count-word span').html( text.length );
				AppPubishing.updateCharacterCounts();
                if(text != ""){
                    $(".cpv-text").html(content);
                }else{
                    $(".cpv-text").html('<div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1 wp-50"></div>');
                }
            });

            $(".post-caption")[0].emojioneArea.on("emojibtn.click", function(button, event) {
                var text = $(".post-caption")[0].emojioneArea.getText();
                var content = $(".post-caption")[0].parents(".wrap-input-emoji").find(".emojionearea-editor").html();
                button.parents(".wrap-input-emoji").find('.count-word span').html( text.length );
				AppPubishing.updateCharacterCounts();
                if(text != ""){
                    $(".cpv-text").html(content);
                }else{
                    $(".cpv-text").html('<div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1 wp-50"></div>');
                }
            });
        }
    },

    AppPubishing.preview = function () {
        var profileFound = false;
        $(".cpv").addClass("d-none");
        $(".am-list-account .am-choice-body .am-choice-item").each(function () {
            var $item = $(this);
            if ($item.find("input").is(':checked')) {
                var network = $item.data("social-network");
                var avatar = $item.data("avatar");
                var name = $item.data("name");
                var username = $item.data("username");
                $(".cpv").each(function () {
                    var $cpv = $(this);
                    var previewNetwork = $cpv.data("social-network");
                    if (network == previewNetwork) {
                        $cpv.removeClass("d-none");
                        $cpv.find(".cpv-avatar").attr("src", avatar);
                        $cpv.find(".cpv-name").text(name);
                        $cpv.find(".cpv-username").text(username);
                        profileFound = true;
                    }
                });
            }
        });
        if (!profileFound) {
            var $profile = $('.preview-profile');
            if ($profile.length) {
                var avatar = $profile.data('avatar');
                var name = $profile.data('name');
                var username = $profile.data('username');
                var network = $profile.data('social-network');
                $('.cpvx').each(function () {
                    var $cpv = $(this);
                    var previewNetwork = $cpv.data("social-network");
                    if (!previewNetwork || previewNetwork == network) {
                        $cpv.removeClass("d-none");
                        $cpv.find(".cpv-avatar").attr("src", avatar);
                        $cpv.find(".cpv-name").text(name);
                        $cpv.find(".cpv-username").text(username);
                    }
                });
            }
        }

        var postType = $('[name="type"]:checked').val();
        if ($('.preview-post-type').length > 0) {
            postType = $('.preview-post-type').val();
        }
        switch (postType) {
            case "text":
                $(".cpv-link, .cpv-media").addClass('d-none');
                break;
            case "link":
                $(".cpv-link").removeClass('d-none');
                $(".cpv-media").addClass('d-none');
                $(".compose-editor [name='link']").trigger("change");
                break;
            default:
                $(".cpv-media").removeClass('d-none');
                $(".cpv-link").addClass('d-none');
                break;
        }

        var caption = $('[name="caption"]').val();
        var $cpvText = $(".cpv-text");
        $cpvText.html('<div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1 wp-50"></div>');
        if (caption) {
            $cpvText.html(caption);
        }

        function onMediaItemsChange() {
            var fileItems = document.querySelectorAll('.file-selected-media .items .file-item');
            var allMedias;
            var isPreviewModal = fileItems.length === 0;

            if (!isPreviewModal) {
                // ── Composer mode: use selected file items ──────────────────
                allMedias = Array.from(fileItems);
            } else {
                // ── Preview modal mode ──────────────────────────────────────
                // CRITICAL: Each tab pane has its own .preview-list-medias.
                // querySelectorAll('.preview-list-medias img') would return
                // N imgs (one per tab = one per social account), making a
                // single uploaded file appear as N items in the grid.
                // FIX: read ONLY from the first tab pane — all posts in a
                // group share the same medias[] array.
                var sourcePane = document.querySelector('.tab-pane.active .preview-list-medias')
                              || document.querySelector('.tab-pane .preview-list-medias');
                var previewMedias = sourcePane ? sourcePane.querySelectorAll('img') : [];
                allMedias = Array.from(previewMedias);
            }

            $(".cpv-img").html('');
            $(".cpv-link .cpv-link-img").html('');

            if (allMedias.length === 0) return;

            // Build staging HTML: one <img data-type data-file data-poster> per media.
            // Channel MutationObservers pick these up and render the real grid.
            var stagingHtml = allMedias.map(function(media) {
                var type = (media.dataset && media.dataset.type) ? media.dataset.type : 'image';
                var file = (media.dataset && (media.dataset.file || media.src)) ? (media.dataset.file || media.src) : media.src;
                // data-poster: explicit poster > first generated thumbnail > none
                var poster = '';
                if (type === 'video') {
                    poster = (media.dataset && media.dataset.poster) ? media.dataset.poster : '';
                    if (!poster && media.dataset && media.dataset.thumbnails) {
                        // Composer mode: grab first thumbnail from generated frames
                        poster = media.dataset.thumbnails.split(',').filter(Boolean)[0] || '';
                    }
                }
                return '<img src="" data-type="' + type + '" data-file="' + file + '" data-poster="' + poster + '">';
            }).join('');

            // Write to ALL .cpv-img staging divs (all tabs get same content
            // — they all share the same medias[] array for a given group).
            $(".cpv-img").html(stagingHtml);

            // Update link preview thumbnail
            var firstMedia = allMedias[0];
            var firstType = (firstMedia.dataset && firstMedia.dataset.type) ? firstMedia.dataset.type : 'image';
            var firstFile = (firstMedia.dataset && (firstMedia.dataset.file || firstMedia.src)) ? (firstMedia.dataset.file || firstMedia.src) : firstMedia.src;
            if (firstType === 'image') {
                $(".cpv-link .cpv-link-img").html('<img src="' + firstFile + '"/>');
            }
        }

        var container = document.querySelector('.file-selected-media .items');
        if (container) {
            const observer = new MutationObserver(() => {
                onMediaItemsChange();
            });
            observer.observe(container, {
                childList: true,
                attributes: true,
                subtree: true,
                attributeFilter: ['src'],
            });
            onMediaItemsChange();
        } else {
            onMediaItemsChange();
        }

        // Re-populate preview when user switches platform tab
        $(document).on('shown.bs.tab', '[data-bs-toggle="pill"]', function() {
            onMediaItemsChange();
        });
    },

    AppPubishing.previewLink = function(result){

        var data = result.data;
        var web = data.host;
        var title = data.title;
        var description = data.description;
        var image = data.image;

        if(web != "" && title != ""){
            $(".cpv-link .cpv-link-img").html(`<img src="${ image }"/>`);
            $(".cpv-link .cpv-link-web").html(web);
            $(".cpv-link .cpv-link-title").html(title);
            $(".cpv-link .cpv-link-desc").html(description);
            $(".cpv-default").addClass("d-none");
        }

        var images = document.querySelectorAll('.file-selected-media .items .file-item');
        if (images.length > 0) 
        {
            var type = $(images[0]).data('type');
            var file = $(images[0]).data('file');
            
            if(type == "image")
            {
                $(".cpv-link .cpv-link-img").html(`<img src="${ file }"/>`);
            }
        }
    },

    /**
	 * Update character count indicators based on selected social networks
	 */
	AppPubishing.updateCharacterCountIndicators = function() {
		var networks = {
			x: false,
			instagram: false,
			facebook: false,
			linkedin: false,
			pinterest: false
		};
		
		// Check which networks are selected
		$(".am-selected-list .am-selected-item").each(function(){
			var network = $(this).attr('data-network');
			if (network) {
				networks[network.toLowerCase()] = true;
			}
		});
		
		// Show/hide character count indicators
		if (networks.x) {
			$(".count-word-x").css('display', 'flex');
		} else {
			$(".count-word-x").hide();
		}
		
		if (networks.instagram) {
			$(".count-word-instagram").css('display', 'flex');
			$(".count-word-hashtag").css('display', 'flex');
		} else {
			$(".count-word-instagram").hide();
			$(".count-word-hashtag").hide();
		}
		
		if (networks.facebook) {
			$(".count-word-facebook").css('display', 'flex');
		} else {
			$(".count-word-facebook").hide();
		}
		
		if (networks.linkedin) {
			$(".count-word-linkedin").css('display', 'flex');
		} else {
			$(".count-word-linkedin").hide();
		}
		
		if (networks.pinterest) {
			$(".count-word-pinterest").css('display', 'flex');
		} else {
			$(".count-word-pinterest").hide();
		}
	};

	/**
	 * Update character counts for all social networks
	 */
	AppPubishing.updateCharacterCounts = function() {
		// Early returns for safety
		if (!$(".post-caption").length) return;
		if (!$(".post-caption")[0].emojioneArea) return;
		
		try {
			var text = $(".post-caption")[0].emojioneArea.getText();
			var textLength = text.length;
			
			// Update main counter
			if ($(".count-word span").length) {
				$(".count-word span").html(textLength);
			}
			
			// Update each network-specific counter
			$(".word-reduce").each(function() {
				var limit = $(this).data("word-count");
				var remaining = limit - textLength;
				
				if (remaining < 0) {
					$(this).removeClass("text-gray-500 text-warning").addClass("text-danger");
				} else if (remaining < limit * 0.1) {
					$(this).removeClass("text-gray-500 text-danger").addClass("text-warning");
				} else {
					$(this).removeClass("text-danger text-warning").addClass("text-gray-500");
				}
				
				$(this).find("span").html(remaining);
			});
			
			// Count hashtags
			if ($(".count-word-hashtag .hashtag-current").length) {
				var hashtags = (text.match(/#[\w]+/g) || []).length;
				$(".count-word-hashtag .hashtag-current").html(hashtags);
				
				if (hashtags > 30) {
					$(".count-word-hashtag").removeClass("text-gray-500").addClass("text-danger");
				} else {
					$(".count-word-hashtag").removeClass("text-danger").addClass("text-gray-500");
				}
			}
		} catch (e) {
			// Silently handle any errors
		}
	};
	AppPubishing.closeCompose = function(){
        $(".compose,.compose_header").addClass("d-none");
        $(".composer-scheduling").addClass("d-none").html("");
        
        // Destroy compose calendar when closing
        if(CalendarCompose) {
            CalendarCompose.destroy();
            CalendarCompose = null;
        }
    },

    AppPubishing.openCompose = function(){
        $(".composer-scheduling")
        .removeClass("d-none")
        .fadeIn(300);
        $(".compose,.compose_header").removeClass("d-none");
		AppPubishing.initn();
    },

    AppPubishing.composeType = function(type){
        switch(type){
            case "media":
                $(".compose-type-link").addClass("d-none");
                $(".compose-type-media").removeClass("d-none");
                break;

            case "link":
                $(".compose-type-link").removeClass("d-none");
                $(".compose-type-media").removeClass("d-none");
                break;

            default:
                $(".compose-type-link").addClass("d-none");
                $(".compose-type-media").addClass("d-none");

        }

        AppPubishing.preview();
    },

    AppPubishing.shorten = function(result){
        var emojiArea = $("[name='caption']").data("emojioneArea");
        if(result.data.caption != ""  && result.data.caption !== null){
            emojiArea.setText(result.data.caption);
        }
        $(".compose-editor [name='link']").val(result.data.link);
    },

    AppPubishing.confirmPostModal = function(result){
        if (result.status == 2) {
            $('.data-post-confirm').html(result.errors);
            $('#confirmPostModal').modal('show');
        }
    },

    /**
     * Install a $.ajaxPrefilter on the publishing/save endpoint.
     * This fires BEFORE the request is sent, letting us wrap the success/error
     * callbacks so we can show our own SweetAlert and control the redirect —
     * before Main.js ever sees the response.
     *
     * Called once from AppPubishing.init().
     */
    AppPubishing.installPublishInterceptor = function(){
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {

            // Only intercept requests to the publishing/save endpoint
            if (!options.url || options.url.indexOf('publishing/save') === -1) return;

            // Wrap the success callback that Main.js registered
            var origSuccess = options.success;
            var origError   = options.error;
            var origComplete = options.complete;

            // Flag to track whether we've handled this response ourselves
            var _pandoHandled = false;

            // Replace success with our interceptor
            options.success = function(data, textStatus, jqXHR) {
                _pandoHandled = true;
                AppPubishing._handlePublishResponse(data, origSuccess, origError, origComplete, options);
            };

            // Replace error too (network/server 5xx errors)
            options.error = function(jqXHR, textStatus, errorThrown) {
                var msg = errorThrown || textStatus || 'An unexpected server error occurred.';
                try {
                    var body = JSON.parse(jqXHR.responseText || '{}');
                    if (body.message) msg = body.message;
                } catch(e){}

                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    html: '<p class="mb-0 fs-14">' + msg + '</p>',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#dc3545',
                    allowOutsideClick: false,
                }).then(function(){
					Main.overplay(true);
					$('.loading').hide();
					$form.removeClass('disabled');
				});
                // Do NOT call origError — we don't want Main.js to also show a toast
            };

            // Wrap complete: when we've handled the response ourselves, only allow
            // Main.js cleanup to run (button re-enable, spinner hide).
            // We block it entirely for non-publishing requests (origComplete = null there).
            if (origComplete) {
                options.complete = function(jqXHR, textStatus) {
                    if (_pandoHandled) {
                        // Suppress Main.js complete — cleanup already done in _handlePublishResponse
                    } else {
                        origComplete(jqXHR, textStatus);
                    }
                };
            }
        });
    },

    /**
     * Central handler for the publishing/save response.
     * Decides what to show based on status and media_limits.
     */
    AppPubishing._handlePublishResponse = function(data, origSuccess, origError, origComplete, options) {

        // Read redirect URL from the form's data-redirect attribute
        var $form = $('#compose-editor').length ? $('#compose-editor') : $('form.actionForm');
        var redirectUrl = $form.attr('data-redirect') || null;

        // Replicate what Main.js does in its success callback:
        //   Main.overplay(true)  → $('.loading').hide()
        //   that.removeClass('disabled')  → the form loses disabled class
        // We must do this ourselves since we suppress origSuccess.
        $('.loading').hide();
        $form.removeClass('disabled');

        // Normalise data — Main.js may not set dataType:'json' so data could arrive
        // as a raw JSON string instead of a parsed object. Parse it if needed.
        if (typeof data === 'string') {
            try { data = JSON.parse(data); } catch(e) { data = null; }
        }

        var status  = data ? parseInt(data.status, 10) : -1;
        var message = (data && data.message) ? data.message : '';

        // ── status:2 → pre-flight validation, show confirm modal (let Main.js handle) ──
        if (status === 2) {
            if (origSuccess) origSuccess(data);
            return;
        }

        // ── status:0 / unknown → validation or publish error ──────────────────
        // Covers: validator failures ("Please select at least one media" etc.)
        // AND real publish errors. Neither redirects — user stays to fix the issue
        // UNLESS it's a publish error (accounts selected etc.) where redirect makes sense.
        // We distinguish: if message is a short form-validation message (no '--' separator)
        // → stay on page. If it contains '--' (platform--error) → redirect after close.
        if (status !== 1) {
            var isFormValidation = message && message.indexOf('--') === -1
                                   && message.indexOf('profiles') === -1;

            // Hide loader and re-enable submit buttons — Main.js normally does this
            // inside its success callback which we suppress, so we do it manually.
            $('.loading').hide();
            $('.btnPostNow, .btnSchedulePost, .btnSaveDraft, .btnSaveApproval').prop('disabled', false).removeClass('disabled loading');
            $('.compose-editor').removeData('submitted').removeData('loading');

            Swal.fire({
                icon: 'error',
                title: isFormValidation ? 'Please check your post' : 'Failed',
                html: '<p class="mb-0 fs-14">' + (message || 'An unknown error occurred.') + '</p>',
                confirmButtonText: 'Close',
                confirmButtonColor: '#dc3545',
                allowOutsideClick: false,
            }).then(function(){
                // Only redirect for real publish errors, not form validation errors
                if (!isFormValidation && redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
            return;
        }

        // ── status:1 → success ────────────────────────────────────────────────
        var limits = Array.isArray(data.media_limits) ? data.media_limits : [];

        if (limits.length === 0) {
            // No limits — show simple success Swal then redirect
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: '<p class="mb-0">' + message + '</p>',
                confirmButtonText: 'OK, got it',
                confirmButtonColor: '#198754',
                allowOutsideClick: false,
            }).then(function(){
                if (redirectUrl) window.location.href = redirectUrl;
            });
            return;
        }

        // Success WITH media limit notes — show our Swal, then redirect after OK
        // Do NOT call origSuccess so Main.js never redirects
        var listHtml = limits.map(function(note){
            return '<li class="text-start py-1">' +
                       '<i class="fa-solid fa-circle-info text-warning me-2"></i>' + note +
                   '</li>';
        }).join('');

        Swal.fire({
            icon: 'success',
            title: 'Success!',
            html: '<p class="mb-2">' + message + '</p>' +
                  '<div class="alert alert-warning border-warning-300 text-start p-3 mt-2 mb-0">' +
                      '<p class="fw-600 mb-1 fs-13">' +
                          '<i class="fa-solid fa-triangle-exclamation me-1"></i>' +
                          'Some platforms cap the number of media files per post:' +
                      '</p>' +
                      '<ul class="list-unstyled mb-0 fs-13">' + listHtml + '</ul>' +
                  '</div>',
            confirmButtonText: 'OK, got it',
            confirmButtonColor: '#198754',
            allowOutsideClick: false,
        }).then(function(){
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });
    },

    /**
     * Initialize owl carousel on .owl-carousel elements inside .schedule-list.
     */
    AppPubishing.initOwlCarousels = function() {
        setTimeout(function() {
            if (typeof $.fn.owlCarousel !== 'function') return;
            $('.post-media-thumb .owl-carousel').each(function() {
                var $owl = $(this);
                if ($owl.hasClass('owl-loaded')) {
                    $owl.trigger('destroy.owl.carousel');
                    $owl.find('.owl-stage-outer').children().unwrap();
                    $owl.removeClass('owl-center owl-loaded owl-text-select-on');
                }
                var itemCount = $owl.find('.item').length;
                if (itemCount === 0) return;
                $owl.owlCarousel({
                    loop:  itemCount > 1,
                    nav:   itemCount > 1,
                    dots:  itemCount > 1,
                    items: 1,
                    responsive: { 0: {items:1}, 600: {items:1}, 1000: {items:1} }
                });
            });
        }, 150);
    },

    AppPubishing.reloadCalendar = function(){
        if($(".compose-calendar").length == 0) return false;
        if(CalendarMain) {
            CalendarMain.refetchEvents();
        }
    },
	
	AppPubishing.reloadCalendarCompose = function(){
        if($(".compose-calendar-new").length == 0) return false;
        if(CalendarCompose) {
            CalendarCompose.refetchEvents();
        }
    },

    AppPubishing.closePopoverCalendar = function(){
        $(".fc-popover-overplay").remove();
    },

    AppPubishing.CalendarAction = function() {
        $(document).on('change', '.calendar-filter', function() {
            AppPubishing.reloadCalendar();
        });
    },
	
	AppPubishing.CalendarActionCompose = function() {
        $(document).on('change', '.calendar-filter', function() {
            AppPubishing.reloadCalendarCompose();
        });
    },

    AppPubishing.getCalendarFilters = function() {
        if($(".compose-calendar").length == 0) return false;

        let filters = {};
        $('.calendar-filter').each(function() {
            let name = $(this).attr('name');
            let value = $(this).val();
            if (name) {
                filters[name] = value;
            }
        });
        return filters;
    },
	
	AppPubishing.getCalendarFiltersCompose = function() {
        if($(".compose-calendar-new").length == 0) return false;

        let filters = {};
        $('.calendar-filter').each(function() {
            let name = $(this).attr('name');
            let value = $(this).val();
            if (name) {
                filters[name] = value;
            }
        });
        return filters;
    },

    AppPubishing.Calendar = function() {
        if($(".compose-calendar").length == 0) return false;

        var calendarHeight = $(CALENDAR_SELECTORS.MAIN).outerHeight() - $(CALENDAR_SELECTORS.HEADER).outerHeight() - Main.getScrollbarWidth();
        var calendarEl = document.getElementById('calendar');
        var countClick = 0;

        CalendarMain = Main.Calendar(calendarEl, {
            timeZone: 'local',
            themeSystem: 'bootstrap5',
            initialView: 'dayGridMonth',
            editable: true,
            direction: document.querySelector('html').getAttribute('dir'),
            headerToolbar: {
                center: 'title'
            },
            height: calendarHeight,
            dayMaxEvents: 2,
            displayEventTime: false,
            stickyHeaderDates: false,
            views: {
                dayGridMonth: {
                    dayMaxEvents: 3
                },
                week: {
                    dayMaxEvents: 100
                },
                day: {}
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                omitZeroMinute: true,
                meridiem: true
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                let filters = AppPubishing.getCalendarFilters();

                $.ajax({
                    url: VARIABLES.url + 'app/publishing/events', 
                    dataType: 'json',
                    data: {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                        ...filters
                    },
                    success: function(response) {
                        successCallback(response.data);
                    },
                    error: function() {
                        failureCallback();
                    },

                });
            },
            eventsSet: function(events) {
                var currentDate = new Date();
                currentDate.setHours(0, 0, 0, 0);

                document.querySelectorAll('.fc-day').forEach(function(dayEl) {
                    var dateAttr = dayEl.getAttribute('data-date');
                    if (dateAttr) {
                        var date = new Date(dateAttr);
                        date.setHours(0, 0, 0, 0);
                        if (date < currentDate) {
                            dayEl.classList.add('past-day');
                        }
                    }
                });
            },
            eventAllow: function(dropInfo, draggedEvent) {
                return !draggedEvent.extendedProps.isPastDay;
            },
            eventDragStart: function(info) {
                if ( $(info.el).parents(".fc-day").hasClass('past-day') ) {
                    CalendarMain.refetchEvents();
                }
            },
            eventDrop: function(info) {
                var $new_date = info.event.start;
                var currentDate = new Date();
                currentDate.setHours(0, 0, 0, 0);

                if ($new_date < currentDate) {
                    info.revert();
                }else{
                    Main.ConfirmDialog("Are you sure about this change?", function(s){
                        if(!s){
                            info.revert();
                            return false;
                        }

                        var $el = $(info.el).find('.event-item');
                        var $id = $el.data("id");
                        var $action = $el.data("url");

                        var data   = new FormData();
                        if($id != undefined) data.append("id", $id);
                        if($new_date != undefined) data.append("new_date", $new_date);

                        Main.ajaxPost( $el, $action, data, function(){

                        });
                    });
                }
            },
            eventDidMount: function(info) {
                var border;
                var status;
                var eventEl = $(info.el);
                var eventItemEl = $('.calendar-event-item').html();
                var data = info.event.extendedProps;
                var media;

                switch (data.status) {
                    case 1:
                        border = "border-dark-200";
                        status = $('.calendar-status[data-status=' + data.status + ']').html();
                        break;
                    case 3:
                        border = "border-primary-200";
                        status = $('.calendar-status[data-status=' + data.status + ']').html();
                        break;
                    case 2:
                        border = "border-warning-200";
                        status = $('.calendar-status[data-status=' + data.status + ']').html();
                        break;
                    case 4:
                        border = "border-success-200";
                        status = $('.calendar-status[data-status=' + data.status + ']').html();
                        status = status.replaceAll("[[posted_link]]", data.response.url);
                        break;
                    case 5:
                        border = "border-danger-200";
                        status = $('.calendar-status[data-status=' + data.status + ']').html();
                        status = status.replaceAll("[[msg]]", data.response.message);
                        break;
                    default:
                        border = "border-danger-200";
                        status = $('.calendar-status[data-status=5]').html();
                        break;
                }

                switch (data.type) {
                    case 1:
                        media = $('.calendar-media-view[data-type=' + data.type + ']').html();
                        break;
                    case 2:
                        media = $('.calendar-media-view[data-type=' + data.type + ']').html();
                        media = media.replaceAll("[[link]]", data.link);
                        break;
                    case 3:
                        if (AppPubishing.isImage(data.image)) {
                            media = '<div class="wp-100 hp-100 bg-cover b-r-6" style="background-image: url(' + data.image + ')"></div>';
                        } else if (AppPubishing.isVideo(data.image)) {
                            media = `
                                <i class="fa-solid fa-play text-white position-relative zIndex-1"></i>
                                <video muted>
                                    <source src="` + data.image + `" type="video/mp4">
                                </video>`;
                        } else {
                            media = '<div class="wp-100 hp-100 bg-cover b-r-6" style="background-image: url(' + data.image + ')"></div>';
                        }
                        break;
                    case 4:
                        media = `
                            <i class="fa-solid fa-play text-white position-relative zIndex-1"></i>
                            <video muted>
                                <source src="` + data.image + `" type="video/mp4">
                            </video>`;
                        break;
                    default:
                        media = $('.calendar-media-view[data-type=1]').html();
                        break;
                }

                const replacements = {
                    '[[id]]': data.id,
                    '[[grouping_data]]': data.grouping_data,
                    '[[icon]]': data.icon,
                    '[[color]]': data.color,
                    '[[caption]]': data.caption,
                    '[[account_name]]': data.account_name,
                    '[[time_post]]': data.time_post,
                    '[[media]]': media,
                    '[[status]]': status,
                    '[[border_color]]': border,
                };

                for (const [key, value] of Object.entries(replacements)) {
                    eventItemEl = eventItemEl.replaceAll(key, value);
                }

                if(info.view.type == "listWeek"){
                    eventEl.html('<td>' + eventItemEl + '</td>');
                } else {
                    eventEl.html(eventItemEl);
                }

                var date = new Date();
                date.setHours(0, 0, 0, 0);

                if (new Date(info.event.start) < date) {
                    info.event.setExtendedProp('isPastDay', true);
                }

                return false;
            },
            eventContent: function(info) {
                
            },
            eventChange: function() {
            },
            eventClick: function(info) {
                var eventEl = $(info.el);
                eventEl.parent().css('z-index', countClick + 10000);
                countClick++;
            },
            moreLinkClick: function(info) {
                setTimeout(function() {
                    var eventEl = $(info.el);
                    $(".fc-popover").wrap('<div class="fc-popover-overplay"></div>');
                    $(".fc-popover").removeClass("d-none");

                    const observer = new MutationObserver(function(mutationsList) {
                        mutationsList.forEach(function(mutation) {
                            mutation.removedNodes.forEach(function(removed_node) {
                                $(".fc-popover-overplay").remove();
                            });
                        });
                    });

                    observer.observe(document.querySelector(".fc-popover-overplay"), { subtree: false, childList: true });
                }, 10);
            }
        });

        setTimeout(() => {
            $(document).on("mouseenter", ".fc-daygrid-day", function () {
                const $day = $(this);
                const dateStr = $day.data("date");
                if (!dateStr) return;

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const hoverDate = new Date(dateStr);
                hoverDate.setHours(0, 0, 0, 0);

                if (hoverDate >= today && $day.find(".add-button").length === 0) {
                    const now = new Date();
                    const plus15 = new Date(now.getTime() + 15 * 60000);

                    const fullDate = new Date(hoverDate);
                    fullDate.setHours(plus15.getHours());
                    fullDate.setMinutes(plus15.getMinutes());

                    const formatted = Main.formatDateTime(fullDate);

                    let addBtnHtml = $('.calendar-add-button').html();
                    addBtnHtml = addBtnHtml.replaceAll('[[date]]', encodeURIComponent(formatted));

                    $day.css("position", "relative").append($(addBtnHtml));
                }
            });
        }, 200);

        return CalendarMain;
    },

    AppPubishing.CalendarCompose = function() {
        if($(".compose-calendar-new").length == 0) return false;

        var calendarHeight = $(CALENDAR_SELECTORS.MAIN).outerHeight() - $(CALENDAR_SELECTORS.HEADER).outerHeight() - Main.getScrollbarWidth();
        var calendarEl = document.getElementById('calendar-new');
        var countClick = 0;

        CalendarCompose = Main.Calendar(calendarEl, {
            timeZone: 'local',
            themeSystem: 'bootstrap5',
            initialView: 'dayGridMonth',
			showNonCurrentDates: false,
			fixedWeekCount: false,
            editable: true,
            direction: document.querySelector('html').getAttribute('dir'),
            headerToolbar: {
                center: 'title'
            },
            height: calendarHeight,
            dayMaxEvents: 2,
            displayEventTime: false,
            stickyHeaderDates: false,
            views: {
                dayGridMonth: {
                    dayMaxEvents: 3
                },
                week: {
                    dayMaxEvents: 100
                },
                day: {}
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                omitZeroMinute: true,
                meridiem: true
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                let filters = AppPubishing.getCalendarFiltersCompose();

                $.ajax({
                    url: VARIABLES.url + 'app/publishing/events_count', 
                    dataType: 'json',
                    data: {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                        ...filters
                    },
                    success: function(response) {
                        successCallback(response.data);
                    },
                    error: function() {
                        failureCallback();
                    },

                });
            },
            eventsSet: function(events) {
                var currentDate = new Date();
                currentDate.setHours(0, 0, 0, 0);

                document.querySelectorAll('.fc-day').forEach(function(dayEl) {
                    var dateAttr = dayEl.getAttribute('data-date');
                    if (dateAttr) {
                        var date = new Date(dateAttr);
                        date.setHours(0, 0, 0, 0);
                        if (date < currentDate) {
                            dayEl.classList.add('past-day');
                        }
                    }
                });
            },
            eventAllow: function(dropInfo, draggedEvent) {
                return !draggedEvent.extendedProps.isPastDay;
            },
            eventDragStart: function(info) {
                if ( $(info.el).parents(".fc-day").hasClass('past-day') ) {
                    CalendarCompose.refetchEvents();
                }
            },
            eventDrop: function(info) {
                var $new_date = info.event.start;
                var currentDate = new Date();
                currentDate.setHours(0, 0, 0, 0);

                if ($new_date < currentDate) {
                    info.revert();
                }else{
                    Main.ConfirmDialog("Are you sure about this change?", function(s){
                        if(!s){
                            info.revert();
                            return false;
                        }

                        var $el = $(info.el).find('.event-item');
                        var $id = $el.data("id");
                        var $action = $el.data("url");

                        var data   = new FormData();
                        if($id != undefined) data.append("id", $id);
                        if($new_date != undefined) data.append("new_date", $new_date);

                        Main.ajaxPost( $el, $action, data, function(){

                        });
                    });
                }
            },
			eventDidMount: function(info) {
				var eventEl = $(info.el);
				var eventItemEl = $('.calendar-event-item-new').html();
				var data = info.event.extendedProps;
								
				// Ensure grouping_data exists and convert to string
				var groupingData = data.grouping_data ? String(data.grouping_data) : '';
				
				const replacements = {
					'[[post_count]]': String(data.post_count || 0),
					'[[post_plural]]': (data.post_count || 0) > 1 ? 's' : '',
					'[[date]]': String(data.date || ''),
					'[[grouping_data]]': groupingData,
				};

				// Replace the placeholders
				for (const [key, value] of Object.entries(replacements)) {
					eventItemEl = eventItemEl.replaceAll(key, value);
				}
				
				if(info.view.type == "listWeek"){
					eventEl.html('<td>' + eventItemEl + '</td>');
				} else {
					eventEl.html(eventItemEl);
				}

				return false;
			},            eventContent: function(info) {
                
            },
            eventChange: function() {
            },
            eventClick: function(info) {
				var eventEl = $(info.el);
				eventEl.parent().css('z-index', countClick + 10000);
				countClick++;
				
				// Get the date from the event
				var selectedDate = info.event.start;
				var selectedDateStr = moment(selectedDate).format('YYYY-MM-DD');
				
				var data = info.event.extendedProps;
				// Ensure grouping_data exists and convert to string
				var groupingData = data.grouping_data ? String(data.grouping_data) : '';
				
				// Highlight the selected day
				$('.fc-day').removeClass('fc-highlight-day');
				$('.fc-day[data-date="' + selectedDateStr + '"]').addClass('fc-highlight-day');
				
				// Show loading
				$('.schedule-list').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
				
				// Fetch the posts for this date
				$.ajax({
					url: VARIABLES.url + 'app/publishing/alllist/all/all/' + selectedDateStr,
					type: 'POST',
					data: {
						ids: groupingData,
						f_status: '',
						f_account_ids: '',
						_token: VARIABLES.csrf
					},
					dataType: 'html',
					success: function(res) {
						$('.schedule-list').html(res);
						
						// Initialize owl carousel — load library dynamically if not present
						AppPubishing.initOwlCarousels();
					},
					error: function(xhr) {
						$('.schedule-list').html('<div class="alert alert-danger">Error loading posts</div>');
					}
				});
			},
            moreLinkClick: function(info) {
                setTimeout(function() {
                    var eventEl = $(info.el);
                    $(".fc-popover").wrap('<div class="fc-popover-overplay"></div>');
                    $(".fc-popover").removeClass("d-none");

                    const observer = new MutationObserver(function(mutationsList) {
                        mutationsList.forEach(function(mutation) {
                            mutation.removedNodes.forEach(function(removed_node) {
                                $(".fc-popover-overplay").remove();
                            });
                        });
                    });

                    observer.observe(document.querySelector(".fc-popover-overplay"), { subtree: false, childList: true });
                }, 10);
            }
        });

        setTimeout(() => {
            $(document).on("mouseenter", "#calendar-new .fc-daygrid-day", function () {
                const $day = $(this);
                const dateStr = $day.data("date");
                if (!dateStr) return;

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const hoverDate = new Date(dateStr);
                hoverDate.setHours(0, 0, 0, 0);

                /*if (hoverDate >= today && $day.find(".add-button").length === 0) {
                    const now = new Date();
                    const plus15 = new Date(now.getTime() + 15 * 60000);

                    const fullDate = new Date(hoverDate);
                    fullDate.setHours(plus15.getHours());
                    fullDate.setMinutes(plus15.getMinutes());

                    const formatted = Main.formatDateTime(fullDate);

                    let addBtnHtml = $('.calendar-add-button').html();
                    addBtnHtml = addBtnHtml.replaceAll('[[date]]', encodeURIComponent(formatted));

                    $day.css("position", "relative").append($(addBtnHtml));
                }*/
            });
        }, 200);

        return CalendarCompose;
    },

    AppPubishing.isImage = function(url) {
        return /\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i.test(url);
    },

    AppPubishing.isVideo = function(url) {
        return /\.(mp4|mov|webm|avi|mkv|flv|ogg)$/i.test(url);
    },

    AppPubishing.CalendarTitle = function(){
        if($(".compose-calendar").length == 0) return false;
        var target = document.querySelector('#calendar .fc-toolbar-title');
        if(!target) return false;
        $(CALENDAR_SELECTORS.TITLE).html(target.innerText);
        var observer = new MutationObserver(function(mutations) {
            $(CALENDAR_SELECTORS.TITLE).html(target.innerText);  
        });
        observer.observe(target, {
            childList: true,
            subtree: true,
            characterDataOldValue: true
        });
    },
	
	AppPubishing.CalendarTitleCompose = function(){
		if($(".compose-calendar-new").length == 0) return false;
		var target = document.querySelector('#calendar-new .fc-toolbar-title');
		if(!target) return false;
		
		$(".compose-calendar-new").find(CALENDAR_SELECTORS.TITLE).html(target.innerText);
		
		var observer = new MutationObserver(function(mutations) {
			$(".compose-calendar-new").find(CALENDAR_SELECTORS.TITLE).html(target.innerText);
			
			// ⭐ ADD THIS: Load notes when calendar title changes (view changes)
			if(CalendarCompose) {
				const view = CalendarCompose.view;
				const start = view.activeStart.toISOString().split('T')[0];
				const end = view.activeEnd.toISOString().split('T')[0];
				AppPubishing.loadCalendarNotes(start, end);
			}
		});
		
		observer.observe(target, {
			childList: true,
			subtree: true,
			characterDataOldValue: true
		});
		
		// ⭐ ADD THIS: Load notes on initial calendar load
		if(CalendarCompose) {
			const view = CalendarCompose.view;
			const start = view.activeStart.toISOString().split('T')[0];
			const end = view.activeEnd.toISOString().split('T')[0];
			AppPubishing.loadCalendarNotes(start, end);
		}
	},

    AppPubishing.CalendarEvents = function(){
        $(document).on("click", ".compose-calendar .calendar-event", function(){
            var type = $(this).data("calendar-type");
            if(!CalendarMain) return;
            
            switch (type) {
                case 'prev':
                    CalendarMain.prev();
                    break;
                case 'next':
                    CalendarMain.next();
                    break;
                case 'today':
                    CalendarMain.today();
                    break;
                case 'dayGridMonth':
                    CalendarMain.changeView(type);
                    break;
                case 'timeGridWeek':
                    CalendarMain.changeView(type);
                    break;
                case 'listWeek':
                    CalendarMain.changeView(type);
                    break;
                default:
                    CalendarMain.today();
                    break;
            }
        });
    },
    
	AppPubishing.CalendarEventsCompose = function(){
        $(document).on("click", ".compose-calendar-new .calendar-event-new", function(){
            var type = $(this).data("calendar-type");
            if(!CalendarCompose) return;
            
            switch (type) {
                case 'prev':
                    CalendarCompose.prev();
                    break;
                case 'next':
                    CalendarCompose.next();
                    break;
                case 'today':
                    CalendarCompose.today();
                    break;
                case 'dayGridMonth':
                    CalendarCompose.changeView(type);
                    break;
                case 'timeGridWeek':
                    CalendarCompose.changeView(type);
                    break;
                case 'listWeek':
                    CalendarCompose.changeView(type);
                    break;
                default:
                    CalendarCompose.today();
                    break;
            }
        });
    },
    
    AppPubishing.CalendarHeight = function(){
        if($(".compose-calendar").length == 0) return false;
        $(window).resize(function() {
            var calendarHeight = $(CALENDAR_SELECTORS.MAIN).outerHeight() - $(CALENDAR_SELECTORS.HEADER).outerHeight() - Main.getScrollbarWidth();
            if(CalendarMain) {
                CalendarMain.setOption('height', calendarHeight);
            }
        });
    },
	
	AppPubishing.CalendarHeightCompose = function(){
        if($(".compose-calendar-new").length == 0) return false;
        $(window).resize(function() {
            var calendarHeight = $(CALENDAR_SELECTORS.MAIN).outerHeight() - $(CALENDAR_SELECTORS.HEADER).outerHeight() - Main.getScrollbarWidth();
            if(CalendarCompose) {
                CalendarCompose.setOption('height', calendarHeight);
            }
        });
    }

	AppPubishing.buildSimpleLeftAlignedTooltip = function(noteData) {
		const maxNotesInTooltip = 5;
		const maxNoteLength = 70;
		
		let tooltip = '<div style="padding: 10px; max-width: 300px; text-align: left;">';
		
		// Header
		tooltip += '<div style="font-weight: bold; font-size: 12px; margin-bottom: 8px; text-align: left;">';
		tooltip += 'Notes (' + noteData.count + ')';
		tooltip += '</div>';
		
		// Notes
		const notesToShow = noteData.notes.slice(0, maxNotesInTooltip);
		
		notesToShow.forEach(function(note, index) {
			let text = note.text;
			if (text.length > maxNoteLength) {
				text = text.substring(0, maxNoteLength) + '...';
			}
			text = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
			
			tooltip += '<div style="margin: 5px 0; padding: 5px 0; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;">';
			tooltip += '<span style="color: #28a745; font-weight: bold; margin-right: 6px;">' + (index + 1) + '.</span>';
			tooltip += '<span style="color: #fff; font-size: 11px; line-height: 1.4;">' + text + '</span>';
			tooltip += '</div>';
		});
		
		// More notes
		if (noteData.count > maxNotesInTooltip) {
			tooltip += '<div style="margin-top: 6px; font-size: 10px; color: rgba(255,255,255,0.6); font-style: italic; text-align: left;">';
			tooltip += '...and ' + (noteData.count - maxNotesInTooltip) + ' more';
			tooltip += '</div>';
		}
		
		// Footer
		tooltip += '<div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid rgba(255,255,255,0.2); font-size: 10px; color: rgba(255,255,255,0.7); text-align: left;">';
		tooltip += '<i class="fas fa-arrow-circle-down"></i> Click green dot to view/edit';
		tooltip += '</div>';
		
		tooltip += '</div>';
		
		return tooltip;
	},
	AppPubishing.reloadCalendarNotes = function() {
		if (!CalendarCompose) return;
		
		const view = CalendarCompose.view;
		const start = view.activeStart.toISOString().split('T')[0];
		const end = view.activeEnd.toISOString().split('T')[0];
		
		AppPubishing.loadCalendarNotes(start, end);
	};
// DEBUGGING STEPS - Run these in browser console


	AppPubishing.renderNoteDots = function() {
    
    // Remove existing dots
    $('.note-dots-container').remove();
    
    if(!calendarNotesData || Object.keys(calendarNotesData).length === 0) {
        return;
    }
    
    let totalDots = 0;
    
    // Add dots to dates with notes
    Object.keys(calendarNotesData).forEach(function(date) {
        const noteData = calendarNotesData[date];
        const $dayCell = $('.fc-daygrid-day[data-date="' + date + '"]');
        
        if ($dayCell.length && noteData.count > 0) {
            // Find the date number link
            const $dayNumber = $dayCell.find('.fc-daygrid-day-number');
            
            if($dayNumber.length) {
                // Create dots HTML - use SPAN instead of DIV
                let dotsHtml = '<span class="note-dots-container pointer" data-date="' + date + '" style="cursor: pointer;">';
                
                
                dotsHtml += '<span class="note-dot"></span>';
				if (noteData.count > 1) {
					dotsHtml += '<span class="note-count">(' + noteData.count + ')</span>';
				}

                
                dotsHtml += '</span>';
                
                // Append dots inside the date number link
                $dayNumber.append(dotsHtml);
                
                // Build tooltip
                const tooltipContent = AppPubishing.buildSimpleLeftAlignedTooltip(noteData);
                const $container = $dayCell.find('.note-dots-container');
                
                $container.attr({
                    'data-bs-toggle': 'tooltip',
                    'data-bs-html': 'true',
                    'data-bs-placement': 'top',
                    'title': tooltipContent
                });
                
                // Initialize Bootstrap tooltip
                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    try {
                        new bootstrap.Tooltip($container[0]);
                    } catch(e) {
                        // Silently fail if tooltip fails
                    }
                }
            }
        }
    });
    
	// Add click handler for dots
    $('.note-dots-container').off('click').on('click', function(e) {
        e.stopPropagation(); // Prevent day click
        e.preventDefault();
        
        const date = $(this).attr('data-date');
        if (date) {
            openNotesModal(date);
        }
    });
},
	
	AppPubishing.loadCalendarNotes = function(start, end) {
    
    $.ajax({
        url: VARIABLES.url + 'app/publishing/notes_for_calendar',
        type: 'GET',
        data: {
            start: start,
            end: end
        },
        dataType: 'json',
        success: function(response) {
            calendarNotesData = response.data || {};
            
            // Wait a bit for calendar to finish rendering
            setTimeout(function() {
                AppPubishing.renderNoteDots();
            }, 300);
        },
        error: function(xhr, status, error) {
            console.error('❌ Failed to load calendar notes');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
        }
    });
};


	// 6. Manually try to render dots
	AppPubishing.renderNoteDots();

});

AppPubishing.init();

$(document).ready(function() {
	
	if ( $(".compose").length > 0 )
	{
		AppPubishing.openCompose();
	}
	
	
    var approvalPage = 0;
    var isLoadingApproval = false;
    var hasMoreApproval = true;
    var draftPage = 0;
    var isLoadingdraft= false;
    var hasMoredraft = true;
    
    // Load approval list when tab is clicked
    $('#contact-tab').on('shown.bs.tab', function (e) {
        //if (!$(this).hasClass('loaded')) {
            approvalPage = 0;
            hasMoreApproval = true;
            $('#approval-list-content').empty();
            loadApprovalList();
            //$(this).addClass('loaded');
        //}
    });
	$('#profile-tab').on('shown.bs.tab', function (e) {
        //if (!$(this).hasClass('loaded')) {
            draftPage = 0;
            hasMoredraft = true;
            $('#draft-list-content').empty();
            loadDraftList();
            //$(this).addClass('loaded');
        //}
    });
	
	// Function to load approval list
    function loadApprovalList() {
        if (isLoadingApproval || !hasMoreApproval) {
            return;
        }
        
        isLoadingApproval = true;
        
        // Show loading indicator
        if (approvalPage === 0) {
            $('#approval-list-content').html(`
                <div class="text-center py-5" id="initial-loader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading approval list...</p>
                </div>
            `);
        } else {
            $('#approval-list-content').append(`
                <div class="text-center py-3" id="load-more-spinner">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }
        
        $.ajax({
            url: VARIABLES.url+'app/publishing/approval/list',
            type: 'GET',
            data: {
                page: approvalPage,
                team_id: '',
                keyword: $('#approval-search').val() || '',
                status: 2, // Pending approval status
				from:'compose'
            },
            success: function(response) {
                
                // Remove loading indicators
                $('#initial-loader').remove();
                $('#load-more-spinner').remove();
                
                if (response.status === 1) {
                    if (approvalPage === 0) {
                        $('#approval-list-content').html(response.data);
                    } else {
                        $('#approval-list-content').append(response.data);
                    }
                    
                    approvalPage++;
                    
                    // Check if there's more data
                    var $content = $(response.data);
                    var itemsCount = $content.filter('.approval-item').length;
                    if (itemsCount < 30) { // Per page is 30
                        hasMoreApproval = false;
                    }
                } else {
                    hasMoreApproval = false;
                    
                    if (approvalPage === 0) {
                        $('#approval-list-content').html(`
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fa-light fa-inbox fs-48 text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted">{{ __('No posts pending approval') }}</h5>
                                <p class="text-muted">{{ __('All caught up! There are no posts waiting for approval.') }}</p>
                            </div>
                        `);
                    }
                }
                
                isLoadingApproval = false;
            },
            error: function(xhr, status, error) {
                console.error('Error loading approval list:', error);
                
                $('#initial-loader').remove();
                $('#load-more-spinner').remove();
                
                $('#approval-list-content').html(`
                    <div class="text-center py-5">
                        <div class="text-danger mb-3">
                            <i class="fa-light fa-exclamation-triangle fs-48"></i>
                        </div>
                        <h5 class="text-danger">{{ __('Error loading approval list') }}</h5>
                        <p class="text-muted">{{ __('Please try again.') }}</p>
                        <button class="btn btn-primary btn-sm" onclick="reloadApprovalList()">
                            <i class="fa-light fa-refresh"></i> {{ __('Retry') }}
                        </button>
                    </div>
                `);
                
                isLoadingApproval = false;
            }
        });
    }
    
    // Function to load draft list
    function loadDraftList() {
        if (isLoadingdraft || !hasMoredraft) {
            return;
        }
        
        isLoadingdraft = true;
        
        // Show loading indicator
        if (draftPage === 0) {
            $('#draft-list-content').html(`
                <div class="text-center py-5" id="initial-loader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading draft list...</p>
                </div>
            `);
        } else {
            $('#draft-list-content').append(`
                <div class="text-center py-3" id="load-more-spinner">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }
        
        $.ajax({
            url: VARIABLES.url+'app/publishing/draft/list',
            type: 'GET',
            data: {
                page: draftPage,
                team_id: '',
                keyword: $('#draft-search').val() || '',
                status: 1, // Pending draft status
				from:'compose'
            },
            success: function(response) {
                
                // Remove loading indicators
                $('#initial-loader').remove();
                $('#load-more-spinner').remove();
                
                if (response.status === 1) {
                    if (draftPage === 0) {
                        $('#draft-list-content').html(response.data);
                    } else {
                        $('#draft-list-content').append(response.data);
                    }
                    
                    draftPage++;
                    
                    // Check if there's more data
                    var $content = $(response.data);
                    var itemsCount = $content.filter('.draft-item').length;
                    if (itemsCount < 30) { // Per page is 30
                        hasMoredraft = false;
                    }
                } else {
                    hasMoredraft = false;
                    
                    if (draftPage === 0) {
                        $('#draft-list-content').html(`
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fa-light fa-inbox fs-48 text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted">No draft post</h5>
                                <p class="text-muted">Start by creating a new draft to save your ideas and prepare content before publishing.</p>
                            </div>
                        `);
                    }
                }
                
                isLoadingdraft = false;
            },
            error: function(xhr, status, error) {
                console.error('Error loading draft list:', error);
                
                $('#initial-loader').remove();
                $('#load-more-spinner').remove();
                
                $('#draft-list-content').html(`
                    <div class="text-center py-5">
                        <div class="text-danger mb-3">
                            <i class="fa-light fa-exclamation-triangle fs-48"></i>
                        </div>
                        <h5 class="text-danger">Error loading draft list</h5>
                        <p class="text-muted">Please try again.</p>
                        <button class="btn btn-primary btn-sm" onclick="reloaddraftList()">
                            <i class="fa-light fa-refresh"></i>Retry
                        </button>
                    </div>
                `);
                
                isLoadingdraft = false;
            }
        });
    }
    
    // Infinite scroll for draft list
    $('#draft-list-content').on('scroll', function() {
        var $container = $(this);
        if ($container.scrollTop() + $container.height() >= $container[0].scrollHeight - 100) {
            loadApprovalList();
        }
    });
    
    // Search functionality
    $('#draft-search').on('input', debounce(function() {
        draftPage = 0;
        hasMoredraft = true;
        $('#draft-list-content').empty();
        loadDraftList();
    }, 500));
    
    // Reload function (global so it can be called from error state)
    window.reloaddraftList = function() {
        draftPage = 0;
        hasMoredraft = true;
        $('#draft-list-content').empty();
        $('#profile-tab').removeClass('loaded').trigger('click');
    };
	
    // Infinite scroll for approval list
    $('#approval-list-content').on('scroll', function() {
        var $container = $(this);
        if ($container.scrollTop() + $container.height() >= $container[0].scrollHeight - 100) {
            loadApprovalList();
        }
    });
    
    // Search functionality
    $('#approval-search').on('input', debounce(function() {
        approvalPage = 0;
        hasMoreApproval = true;
        $('#approval-list-content').empty();
        loadApprovalList();
    }, 500));
    
    // Reload function (global so it can be called from error state)
    window.reloadApprovalList = function() {
        approvalPage = 0;
        hasMoreApproval = true;
        $('#approval-list-content').empty();
        $('#contact-tab').removeClass('loaded').trigger('click');
    };
    
    // Debounce helper
    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }
	
	const PATH = '{{ url("/") }}/';

});



	function open_notes_modal() {
		$(".calendar-notes-added").html('');
		$('#notes_modal').modal('show');
		$('.date').datepicker('setDate', 'today');
		
	}

	$(document).ready(function () {
		//load_calendar();
		
		// On date change, trigger the AJAX call
		$('.date').off('changeDate').on('changeDate', function(e) {
			// Skip if this is a programmatic change (not user click)
			if ($(this).data('skip-change-event')) {
				$(this).data('skip-change-event', false);
				return;
			}
			
			// User manually changed the date
			var date = moment(e.date).format('YYYY-MM-DD');
			
			// Warn if in edit mode
			var isEditMode = $('.edit_note_btn').is(':visible');
			if (isEditMode) {
				if (!confirm('Changing date will discard your current edits. Continue?')) {
					// Revert to previous date
					var originalDate = $(this).data('current-date');
					$(this).data('skip-change-event', true);
					$(this).datepicker('setDate', originalDate);
					return;
				}
			}
			
			// Clear and load new date
			$('#note_text').val('');
			$('.edit_note_btn').attr('data-id', '');
			$('.add_note_btn').show();
			$('.edit_note_btn').hide();
			$('.cancel-edit-btn').hide();
			$(this).data('current-date', e.date);
			
			get_note(date);
		});
		
		$('.date').on('focus click', function () {
			$(this).datepicker('show');
		});
		
		$('#notes_modal').on('shown.bs.modal', function() {  
			$('.edit_note_btn').attr('data-id', '');
			$('.add_note_btn').show();
			$('.edit_note_btn').hide();
		});
	});

	function add_note() {
		var note_date = moment($("#note_date").datepicker("getDate")).format('YYYY-MM-DD');
		var note_text = $("#note_text").val();
		
		if (note_date != '' && note_text != '') {
			$(".loading").show();
			$.ajax({
				url: VARIABLES.url + 'app/publishing/add_note',
				type: 'POST',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				data: { note_date: note_date, note_text: note_text },
				dataType: 'json',
				error: function() {
					$(".loading").hide();
				},
				success: function(res) {
					AppPubishing.reloadCalendarCompose();
					AppPubishing.reloadCalendarNotes();
					$(".loading").hide();
					$('#notes_modal').modal('hide');
					iziToast.success({
						icon: 'fad fa-bells',
						title: '',
						position: 'bottomCenter',
						message: 'Note has been added successfully',
					});
				}
			});
		} else {
			iziToast.error({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: 'Please enter note',
			});
		}
	}

	function click_edit(_this, id) {
		$("#note_text").val($(_this).closest('.note-items').find('.notetext').text());
		$('.edit_note_btn').attr('data-id', id);
		$('.add_note_btn').hide();
		$('.edit_note_btn').show();
		$('.cancel-edit-btn').show();
	}

	function edit_note() {
		var note_text = $("#note_text").val();
		var id = $('.edit_note_btn').attr('data-id');
		var note_date = moment($("#note_date").datepicker("getDate")).format('YYYY-MM-DD');
		if (note_text != '') {
			$(".loading").show();
			$.ajax({
				url: VARIABLES.url + 'app/publishing/edit_note/' + id,
				type: 'POST',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				data: { note_date: note_date, note_text: note_text },
				dataType: 'json',
				error: function() {
					$(".loading").hide();
				},
				success: function(res) {
					AppPubishing.reloadCalendarCompose();
					AppPubishing.reloadCalendarNotes();
					$(".loading").hide();
					$('#notes_modal').modal('hide');
					iziToast.success({
						icon: 'fad fa-bells',
						title: '',
						position: 'bottomCenter',
						message: 'Note has been updated successfully',
					});
				}
			});
		} else {
			iziToast.error({
				icon: 'fad fa-bells',
				title: '',
				position: 'bottomCenter',
				message: 'Please enter note',
			});
		}
	}

	function get_note(date) {
		$(".loading").show();
		$(".calendar-notes-added").html('');
		
		$.ajax({
			url: VARIABLES.url + 'app/publishing/get_note/' + date,
			type: 'GET',
			dataType: 'html',
			error: function() {
				$(".loading").hide();
				iziToast.error({
					title: 'Error',
					message: 'Failed to load notes',
					position: 'bottomCenter'
				});
			},
			success: function(res) {
				$(".calendar-notes-added").html(res);
				$(".loading").hide();
			}
		});
	}

	function delete_note(id, date) {
		Main.ConfirmDialog("Are you sure you want to delete this note?", function(s){
			if(!s){
				return false;
			}
			$.ajax({
				url: VARIABLES.url + 'app/publishing/delete_note/' + id,
				type: 'DELETE',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(res) {
					AppPubishing.reloadCalendarCompose();
					AppPubishing.reloadCalendarNotes();
					get_note(date);
					iziToast.success({
						icon: 'fad fa-bells',
						title: '',
						position: 'bottomCenter',
						message: 'Note has been deleted successfully',
					});
				}
			});
		});
	}

	function openNotesModal(date) {
		$('#notes_modal').modal('show');
		
		const [year, month, day] = date.split('-');
		const dateObj = new Date(year, month - 1, day);
		
		// Set flag to skip changeDate event
		$('.date').data('skip-change-event', true);
		$('.date').data('current-date', dateObj);
		
		$('.date').datepicker('setDate', dateObj);
		
		// Reset modal
		$('.edit_note_btn').attr('data-id', '');
		$('.add_note_btn').show();
		$('.edit_note_btn').hide();
		$('.cancel-edit-btn').hide();
		$('#note_text').val('');
		
		get_note(date);
	}
	
	$('#notes_modal').on('show.bs.modal', function() {
		$('.cancel-edit-btn').hide();
	});
	
	function reset_to_add_mode() {
		$('#note_text').val('');
		$('.edit_note_btn').attr('data-id', '');
		$('.add_note_btn').show();
		$('.edit_note_btn').hide();
		$('.cancel-edit-btn').hide();
	}