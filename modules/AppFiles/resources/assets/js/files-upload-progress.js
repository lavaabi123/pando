/**
 * files-upload-progress.js
 * Circular SVG progress overlay + error notifications for file uploads.
 * Include AFTER files.js.
 * Location: Modules/AppFiles/resources/assets/js/files-upload-progress.js
 */
(function ($) {
    "use strict";

    var uploadUrl = VARIABLES.url + 'app/files/upload_files';
    var CIRCUMF   = 263.9;

    function notify(type, msg) {
        iziToast[type]({
            icon: 'fad fa-bells',
            title: '',
            message: msg,
            position: 'bottomCenter'
        });
    }

    function showProgress(label) {
        $('#pando-upload-label').text(label || 'Uploading...');
        setProgress(0);
        $('#pando-upload-progress-wrap').css('display', 'flex');
    }

    function setProgress(pct) {
        pct = Math.min(100, Math.max(0, pct));
        var offset = CIRCUMF - (pct / 100) * CIRCUMF;
        $('#pando-upload-ring').css('stroke-dashoffset', offset.toFixed(1));
        $('#pando-upload-pct').text(Math.round(pct) + '%');
    }

    function hideProgress(callback) {
        setProgress(100);
        setTimeout(function () {
            $('#pando-upload-progress-wrap').css('display', 'none');
            setProgress(0);
            if (typeof callback === 'function') callback();
        }, 500);
    }

    function xhrUpload(url, formData, label, onDone) {
        showProgress(label);

        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                setProgress((e.loaded / e.total) * 100);
            }
        });

        xhr.addEventListener('load', function () {

            if (xhr.status === 413) {
                hideProgress(function () {
                    notify('error', 'File is too large. Ask your administrator to increase the server upload limit.');
                });
                return;
            }

            if (xhr.status >= 400) {
                hideProgress(function () {
                    notify('error', 'Upload failed (HTTP ' + xhr.status + ').');
                });
                return;
            }

            var result = null;
            try { result = JSON.parse(xhr.responseText); } catch (e) {}

            if (!result) {
                hideProgress(function () {
                    notify('error', 'Unexpected server response.');
                });
                return;
            }

            if (result.status === 0) {
                var errMsg = result.message || 'Upload failed.';
                if (result.errors && typeof result.errors === 'object') {
                    var lines = [];
                    $.each(result.errors, function (filename, msg) {
                        lines.push(msg);
                    });
                    if (lines.length) errMsg = lines.join(' ');
                }
                hideProgress(function () {
                    notify('error', errMsg);
                });
                return;
            }

            hideProgress(function () {
                if (typeof onDone === 'function') onDone();
            });
        });

        xhr.addEventListener('error', function () {
            hideProgress(function () {
                notify('error', 'Upload failed. Check your connection and try again.');
            });
        });

        xhr.timeout = 600000;
        xhr.addEventListener('timeout', function () {
            hideProgress(function () {
                notify('error', 'Upload timed out. Try a smaller file or check your connection.');
            });
        });

        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', VARIABLES.csrf);
        xhr.send(formData);
    }

    function install() {

        $(document).off('change', '#file-upload');
        $(document).on('change', '#file-upload', function () {
            var inputEl = this;
            if (!inputEl.files || !inputEl.files.length) return false;

            var totalFiles = inputEl.files.length;
            var label      = totalFiles === 1 ? inputEl.files[0].name : totalFiles + ' files';
            var folder_id  = $('[name="folder_id"]:checked').val() || 0;
            var team_id    = VARIABLES.team_id || 0;

            var form_data = new FormData();
            form_data.append('name', 'file-upload');
            form_data.append('folder_id', folder_id);
            form_data.append('team_id', team_id);
            for (var i = 0; i < totalFiles; i++) {
                form_data.append('files[]', inputEl.files[i]);
            }

            $(inputEl).val('');

            xhrUpload(uploadUrl, form_data, label, function () {
                Main.ajaxScroll(true);
            });

            return false;
        });

        $('body').off('drop').on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('#drag-overlay').removeClass('active');

            var files = e.originalEvent.dataTransfer.files;
            if (!files || !files.length) return;

            var label     = files.length === 1 ? files[0].name : files.length + ' files';
            var folder_id = $('[name="folder_id"]:checked').val() || 0;
            var team_id   = VARIABLES.team_id || 0;

            var formData = new FormData();
            formData.append('folder_id', folder_id);
            formData.append('team_id', team_id);
            for (var i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            xhrUpload(uploadUrl, formData, label, function () {
                Main.ajaxScroll(true);
            });
        });
    }

    $(function () {
        install();
    });

}(jQuery));