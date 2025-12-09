"use strict";

var AppPubishing = new (function () 
{
    var AppPubishing = this;
    var CalendarMain = null;      // Main calendar instance
    var CalendarCompose = null;   // Compose calendar instance
    
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
        }

    },
	
	AppPubishing.initn = function( reload ) 
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': VARIABLES.csrf
            }
        });

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
            var images = document.querySelectorAll('.file-selected-media .items .file-item');
            let allMedias;
            if (images.length > 0) {
                allMedias = Array.from(images);
            } else {
                var previewMedias = document.querySelectorAll('.preview-list-medias img');
                allMedias = Array.from(previewMedias);
            }
            const previewHtml = allMedias.map(media => {
                var type = media.dataset?.type || 'image';
                var file = media.dataset?.file || media.src;
                if (type == "image") {
                    return `<img src="${file}"/>`;
                } else if (type == "video") {
                    return `<div class="bg-gray-400 hp-100 d-flex align-items-center justify-content-center fs-60 text-white"><i class="fa-solid fa-play"></i></div>`;
                }
            }).join('');
            if (allMedias.length === 0) {
                $(".cpv-img").html('');
                $(".cpv-link .cpv-link-img").html('');
                return;
            }
            var firstMedia = allMedias[0];
            var firstFileType = firstMedia.dataset?.type || 'image';
            var firstFile = firstMedia.dataset?.file || firstMedia.src;
            $(".cpv-img").html(previewHtml);
            if (firstFileType == "image") {
                $(".cpv-link .cpv-link-img").html(`<img src="${firstFile}"/>`);
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
				
				// Debug - check what data you're getting
				console.log('Event extendedProps:', data);
				console.log('Grouping data:', data.grouping_data);
				
				// Ensure grouping_data exists and convert to string
				var groupingData = data.grouping_data ? String(data.grouping_data) : '';
				
				const replacements = {
					'[[post_count]]': String(data.post_count || 0),
					'[[post_plural]]': (data.post_count || 0) > 1 ? 's' : '',
					'[[date]]': String(data.date || ''),
					'[[grouping_data]]': groupingData,
				};
				
				console.log('Replacements:', replacements);

				// Replace the placeholders
				for (const [key, value] of Object.entries(replacements)) {
					eventItemEl = eventItemEl.replaceAll(key, value);
				}
				
				console.log('Final HTML:', eventItemEl);

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
						
						// Initialize owl carousel if needed
						if ($('.owl-carousel').length > 0) {
							if ($('.owl-carousel').hasClass('owl-theme')) {
								$('.owl-carousel').trigger('destroy.owl.carousel');
								$('.owl-carousel').find('.owl-stage-outer').children().unwrap();
								$('.owl-carousel').removeClass("owl-center owl-loaded owl-text-select-on");
							}
							
							$(".owl-carousel").owlCarousel({
								loop: true,
								nav: true,
								responsive: {
									0: { items: 1 },
									600: { items: 1 },
									1000: { items: 1 }
								}
							});
						}
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
        });
        observer.observe(target, {
            childList: true,
            subtree: true,
            characterDataOldValue: true
        });
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

});

AppPubishing.init();
