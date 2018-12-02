BootstrapMultimodal = (function ($) {
    var BASE_ZINDEX = 100001;
    var ZINDEX_MULTIPLIER = 20;
    var ZINDEX_MODAL = 10;
    var NAVBAR_SELECTOR = '.apply-modal-open-padding .navbar';

    var modalsCount = 0;
    var $firstModal = null;


    /**
     * Hides any extra backdrops created by bootstrap and arranges the first one to always be below the top modal.
     */
    function adjustBackdrops() {
        var modalIndex = modalsCount - 1;
        var $firstBackdrop = $('.modal-backdrop:first');

        $('.modal-backdrop').not(':first').addClass('hidden');

        if (modalIndex == 0) {
            $firstBackdrop.css('z-index', '');
        } else {
            $firstBackdrop.css('z-index', BASE_ZINDEX + (modalIndex * ZINDEX_MULTIPLIER));
        }
    }

    /**
     * Moves a modal to the correct z-index position.
     *
     * @param $modal
     */
    function adjustModal($modal) {
        var modalIndex = modalsCount - 1;

        $modal.css('z-index', BASE_ZINDEX + (modalIndex * ZINDEX_MULTIPLIER) + ZINDEX_MODAL);
    }

    /**
     * Monkey patches modal's hide for resetting of counts and body adjustments.
     *
     * @param {Object} modal
     */
    function patchModalHide(modal) {
        if (modal.__isHidePatched === true) {
            return;
        }
        modal.__isHidePatched = true;

        var hide = modal.hide;

        modal.hide = function (event) {
            var wasShown = modal.isShown;
            hide.apply(modal, arguments);

            if (!wasShown || (wasShown && this.isShown)) {
                return;
            }

            modalsCount--;

            if (modalsCount > 0) {
                adjustBackdrops();
            }
        }.bind(modal);
    }

    /**
     * Monkey patches modal's adjustDialog for resetting of counts and body adjustments.
     *
     * @param {Object} modal
     */
    function patchModalAdjustDialog(modal) {
        if (modal.__isAdjustDialogPatched === true) {
            return;
        }
        modal.__isAdjustDialogPatched = true;

        var firstModal = $firstModal.data('bs.modal');
        var bodyIsOverflowing = firstModal.bodyIsOverflowing;
        var scrollbarWidth = firstModal.scrollbarWidth;

        modal.adjustDialog = function () {
            var modalIsOverflowing = this.$element[0].scrollHeight > document.documentElement.clientHeight;

            // make sure paddings are set correctly according to first modal's determination of paddings
            this.$element.css({
                paddingLeft: !bodyIsOverflowing && modalIsOverflowing ? scrollbarWidth : '',
                paddingRight: bodyIsOverflowing && !modalIsOverflowing ? scrollbarWidth : ''
            });
        }.bind(modal);
    }

    /**
     * Monkey patches modal's backdrop for positional adjustments.
     * Only executed for stacked modals.
     *
     * @param {Object} modal
     */
    function patchModalBackdrop(modal) {
        if (modal.__isBackdropPatched === true) {
            return;
        }
        modal.__isBackdropPatched = true;

        var backdrop = modal.backdrop;

        modal.backdrop = function () {
            backdrop.apply(modal, arguments);
            adjustBackdrops();
        };
    }

    /**
     * Patches a modal's padding setting for hidden body scrollbars.
     * Only executed for stacked modals.
     *
     * @param modal
     */
    function patchModalSetScrollbar(modal) {
        var $navbars = $(NAVBAR_SELECTOR);

        var setScrollbar = modal.setScrollbar;
        modal.setScrollbar = function () {
            setScrollbar.apply(modal, arguments);

            if (modal.bodyIsOverflowing) {
                $navbars.css('padding-right', modal.$body.css('padding-right'));
            }
        };

        var resetScrollbar = modal.resetScrollbar;
        modal.resetScrollbar = function () {
            resetScrollbar.apply(modal, arguments);

            $navbars.css('padding-right', '');
        };
    }

    /**
     * Patches a modal's methods.
     *
     * @param $modal
     */
    function patchModal($modal) {
        var modal = $modal.data('bs.modal');

        patchModalHide(modal);

        if (modalsCount == 1) {
            patchModalSetScrollbar(modal);

        } else if (modalsCount > 1) {
            adjustModal($modal);
            patchModalAdjustDialog(modal);
            patchModalBackdrop(modal);

            modal.setScrollbar = function () { /* noop */
            };
            modal.resetScrollbar = function () {
                if (modalsCount > 0) {
                    modal.$body.addClass('modal-open');
                }
            };
        }
    }


    /**
     * Bootstrap triggers the show event at the beginning of the show function and before
     * the modal backdrop element has been created.
     *
     * The additional event listener allows bootstrap to complete show, after which the modal backdrop will have been
     * created and appended to the DOM.
     *
     * @param event
     */
    function onShow(event) {
        if (event && event.isDefaultPrevented()) {
            return;
        }

        var $modal = $(event.target);

        if ($modal.data('multimodal') == 'disabled') {
            return;
        }

        modalsCount++;

        if (!$firstModal || modalsCount == 1) {
            $firstModal = $modal;
        }

        patchModal($modal);
    }


    /**
     * Enables multimodal patching.
     */
    function enable() {
        $(document).on('show.bs.modal.multimodal', onShow);
    }

    /**
     * Disables multimodal patching.
     */
    function disable() {
        $(document).off('show.bs.modal.multimodal', onShow);
    }


    // enable by default
    enable();


    return {
        disable: disable,
        enable: enable
    };
}(jQuery));

var showCloudMediaModal = function (options) {
    var defaults = {
        text: {
            internet: "Internet",
            select: "Select",
            cancel: "Cancel",
            cloud_placeholder: "Insert your URL here, Supports Vimeo and Youtube.",
            cloud_title: "Cloud Media"
        },
        type: ['image', 'video', 'audio', 'file'],
        uploadUrl: "/admin/media/upload2",
        resource: null,
        onMediaCreated: function () {

        }
    };

    var settings = $.extend({}, defaults, options);

    var $cloudMediaModal = $(
        '<div class="modal fade"' +
        '     id="CloudMediaModal"' +
        '     tabindex="-1"' +
        '     role="dialog">' +
        '    <div class="modal-dialog modal-dialog-centered"' +
        '         role="document">' +
        '        <div class="modal-content">' +
        '            <div class="modal-header">' +
        '                <button type="button"' +
        '                        class="close"' +
        '                        aria-label="Close">' +
        '                    <span aria-hidden="true">&times;</span></button>' +
        '                <h4 class="modal-title">' + settings.text.cloud_title + '</h4>' +
        '            </div>' +
        '            <div class="modal-body">' +
        '                <div class="internet-files">' +
        '                    <div class="internet-file-input">' +
        '                        <input type="text"' +
        '                               class="form-control"' +
        '                               placeholder="' + settings.text.cloud_placeholder + '">' +
        '                    </div>' +
        '                    <div class="internet-file-preview"></div>' +
        '                </div>' +
        '            </div>' +
        '            <div class="modal-footer">' +
        '                <button type="button"' +
        '                        disabled="disabled"' +
        '                        class="btn btn-primary btn-select">' + settings.text.select + '</button>' +
        '                <button type="button"' +
        '                        class="btn btn-default btn-cancel"' +
        '                        >' + settings.text.cancel + '</button>' +
        '            </div>' +
        '        </div>' +
        '    </div>' +
            '<style>' +
            '#CloudMediaModal .modal-header {' +
            '  flex-direction: row-reverse;' +
            '}' +
            '#CloudMediaModal .modal-header .close {' +
            '  margin: -1rem auto -1rem -1rem;' +
            '}' +
            '#CloudMediaModal .internet-files .internet-file-input {' +
            '  margin-bottom: 15px;' +
            '}' +
            '#CloudMediaModal .internet-files .internet-file-preview {' +
            '  height: 400px;' +
            '}' +
            '#CloudMediaModal .modal-footer button {' +
            '  padding-left: 30px;' +
            '  padding-right: 30px;' +
            '  margin: 0 5px;' +
            '}' +
            '</style>' +
        '</div>');

    var $cloudFilesEl = $cloudMediaModal.find('.internet-files');

    function parseVideo(url) {
        // - Supported YouTube URL formats:
        //   - http://www.youtube.com/watch?v=My2FRPA3Gf8
        //   - http://youtu.be/My2FRPA3Gf8
        //   - https://youtube.googleapis.com/v/My2FRPA3Gf8
        // - Supported Vimeo URL formats:
        //   - http://vimeo.com/25451551
        //   - http://player.vimeo.com/video/25451551
        // - Also supports relative URLs:
        //   - //player.vimeo.com/video/25451551
        // - DailyMotion
        //   - http://www.dailymotion.com/video/x6ga7eg

        url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com)|dailymotion.com)\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

        if (RegExp.$3.indexOf('youtu') > -1) {
            var type = 'youtube';
        } else if (RegExp.$3.indexOf('vimeo') > -1) {
            var type = 'vimeo';
        } else if (RegExp.$3.indexOf('dailymotion') > -1) {
            var type = 'dailymotion';
        }

        return {
            type: type,
            id: RegExp.$6
        };
    }

    function getResourceDetails(url, cb) {
        // Obtains the video's thumbnail and passed it back to a callback function.
        var videoObj = parseVideo(url);
        if (videoObj.type == 'youtube') {
            cb({
                'cloud_id': videoObj.id,
                'cloud_thumbnail': '//img.youtube.com/vi/' + videoObj.id + '/maxresdefault.jpg',
                'cloud_host': 'youtube',
            });
        } else if (videoObj.type == 'vimeo') {
            // Requires jQuery
            $.get('http://vimeo.com/api/v2/video/' + videoObj.id + '.json', function (data) {
                cb({
                    'cloud_id': videoObj.id,
                    'cloud_thumbnail': data[0].thumbnail_large,
                    'cloud_host': 'vimeo',
                });
            });
        } else if (videoObj.type == 'dailymotion') {
            cb({
                'cloud_id': videoObj.id,
                'cloud_thumbnail': '//www.dailymotion.com/thumbnail/video/' + videoObj.id,
                'cloud_host': 'dailymotion',
            });
        }
    }

    function createVideo(url, width, height) {
        // Returns an iframe of the video with the specified URL.
        var videoObj = parseVideo(url);
        var $iframe = $('<iframe>', {width: width, height: height});
        $iframe.attr('frameborder', 0);
        if (videoObj.type == 'youtube') {
            $iframe.attr('src', '//www.youtube.com/embed/' + videoObj.id);
        } else if (videoObj.type == 'vimeo') {
            $iframe.attr('src', '//player.vimeo.com/video/' + videoObj.id);
        } else if (videoObj.type == 'dailymotion') {
            $iframe.attr('src', '//www.dailymotion.com/embed/video/' + videoObj.id);
        }
        return $iframe;
    };

    var selectMedia = function () {
        var url = $cloudMediaModal.find('input[type="text"]').val();
        getResourceDetails(url, function (resource) {

            var data = new FormData();
            data.append('url', url);
            data.append('resource', settings.resource);
            data.append('cloud_id', resource.cloud_id);
            data.append('cloud_thumbnail', resource.cloud_thumbnail);
            data.append('cloud_host', resource.cloud_host);

            $.ajax({
                url: settings.uploadUrl,
                type: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                processData: false, // Don't process the files
                contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                success: function (data, textStatus, jqXHR) {
                    hideModal();
                    settings.onMediaCreated && settings.onMediaCreated(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('ERRORS: ' + textStatus);
                }
            });
        });
    };

    var hideModal = function () {
        $cloudMediaModal.modal('hide');
    };

    var init = function () {
        $cloudMediaModal.on('hidden.bs.modal', function () {
            $cloudMediaModal.remove();
        });

        $cloudFilesEl.find('input[type="text"]').bind('input', function () {
            if ($cloudFilesEl.find('input[type="text"]').val() && $cloudFilesEl.find('input[type="text"]').val().trim() != "") {
                var iFramWidth = $cloudFilesEl.find('.internet-file-preview').width();
                var iFramHeight = 400;
                var iFrame = createVideo($cloudFilesEl.find('input[type="text"]').val(), iFramWidth, iFramHeight);
                $cloudFilesEl.find('.internet-file-preview').empty();
                $cloudFilesEl.find('.internet-file-preview').append(iFrame);
                $cloudMediaModal.find('.modal-footer .btn-select').prop('disabled', false);
            } else {
                $cloudMediaModal.find('.modal-footer .btn-select').prop('disabled', true);
            }
        });

        $cloudMediaModal.find('.btn-select').click(function () {
            selectMedia();
        });

        $cloudMediaModal.find('.close, .btn-cancel').click(function () {
            hideModal();
        });

        $('body').append($cloudMediaModal);
        $cloudMediaModal.modal({
            backdrop: 'static'
        });
    };

    init();
};

var showMediaModal = function (options) {
    var defaults = {
        text: {
            title: "Media",
            library: "Library",
            internet: "Internet",
            upload_new_file: "Upload new file",
            select: "Select",
            cancel: "Cancel",
            cloud_placeholder: "Insert your URL here, Supports Vimeo and Youtube.",
            cloud_title: "Cloud Media"
        },
        type: ['image', 'video', 'audio', 'file'],
        hosted: ['local', 'cloud'],
        uploadUrl: "/admin/media/upload2",
        listFilesUrl: "/admin/media/files",
        resource: null,
        onMediaCreated: function () {

        }
    };

    var settings = $.extend({}, defaults, options);

    var cssStyle = '<style>' +
        '#modal-media-lib .modal-header {' +
        '  flex-direction: row-reverse;' +
        '}' +
        '#modal-media-lib .modal-header .close {' +
        '  margin: -1rem auto -1rem -1rem;' +
        '}' +
        '#modal-media-lib .local-files {' +
        '  padding-bottom: 40px;' +
        '  position: relative;' +
        '  height: 400px;' +
        '}' +
        '#modal-media-lib .local-files .upload-wrap {' +
        '  position: absolute;' +
        '  bottom: 0;' +
        '  left: 0;' +
        '  width: 100%;' +
        '  text-align: center;' +
        '}' +
        '#modal-media-lib .local-files .files {' +
        '  max-height: 100%;' +
        '  overflow-y: auto;' +
        '}' +
        '#modal-media-lib .local-files .files::-webkit-scrollbar-track {' +
        '  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);' +
        '  border-radius: 10px;' +
        '  background-color: #f5f5f5;' +
        '}' +
        '#modal-media-lib .local-files .files::-webkit-scrollbar {' +
        '  width: 12px;' +
        '  background-color: #f5f5f5;' +
        '}' +
        '#modal-media-lib .local-files .files::-webkit-scrollbar-thumb {' +
        '  border-radius: 10px;' +
        '  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);' +
        '  background-color: #555;' +
        '}' +
        '#modal-media-lib .local-files .files .file {' +
        '  width: 20%;' +
        '  float: left;' +
        '  padding: 0 5px;' +
        '}' +
        '#modal-media-lib .local-files .files .file .inner-details {' +
        '  margin-bottom: 10px;' +
        '  border: 1px solid #ccc;' +
        '  border-radius: 5px;' +
        '  background: #f5f5f5;' +
        '  position: relative;' +
        '  cursor: pointer;' +
        '}' +
        '#modal-media-lib .local-files .files .file .inner-details .avatar{' +
        '  height: 100px;' +
        '  background-size: cover;' +
        '  background-repeat: no-repeat;' +
        '  background-position: center;' +
        'text-align: center;' +
        'line-height: 100px;' +
        'font-size: 65px;' +
        'background-color: #afafaf;' +
        'color: #fff;' +
        '}' +
        '#modal-media-lib .local-files .files .file .inner-details .avatar.has-thumbnail i{' +
            'display: none;'+
        '}' +
        '#modal-media-lib .local-files .files .file .inner-details .file-name {' +
        '  word-wrap: break-word;' +
        '  padding: 5px;' +
        '  white-space: nowrap;' +
        '  overflow: hidden;' +
        '  text-overflow: ellipsis;' +
        'text-align: center;' +
        '}' +
        '#modal-media-lib .local-files .files .file .inner-details .iwan-check {' +
        '  position: absolute;' +
        '  top: 5px;' +
        '  left: 5px;' +
        '  color: #fff;' +
        '  display: none;' +
        '  width: 30px;' +
        '  height: 30px;' +
        '  text-align: center;' +
        '  background: #0d6aad;' +
        '  border-radius: 50%;' +
        '  line-height: 30px;' +
        '}' +
        '#modal-media-lib .local-files .files .file .inner-details .upload-progress {' +
        '  position: absolute;' +
        '  width: calc(100% - 20px);' +
        '  left: 10px;' +
        '  top: 50%;' +
        '  height: 15px;' +
        '  background: #fff;' +
        '  border-radius: 10px;' +
        '  margin-top: -7.5px;' +
        '  overflow: hidden;' +
        '}' +
        '#modal-media-lib .local-files .files .file .inner-details .upload-progress .complete {' +
        '  position: absolute;' +
        '  top: 0;' +
        '  left: 0;' +
        '  width: 0;' +
        '  background: #00a7d0;' +
        '  height: 100%;' +
        '}' +
        '#modal-media-lib .local-files .files .file.is-selected .iwan-check {' +
        '  display: block;' +
        '}' +
        '#modal-media-lib .modal-footer button {' +
        '  padding-left: 30px;' +
        '  padding-right: 30px;' +
        '  margin: 0 5px;' +
        '}' +
        '#modal-media-lib .is-loading {' +
        '  text-align: center;' +
        '  font-size: 40px;' +
        '  padding: 10px 0;' +
        '  clear: both;' +
        '}' +
        '.sk-fading-circle {' +
        '  margin: 20px auto;' +
        '  width: 40px;' +
        '  height: 40px;' +
        '  position: relative;' +
        '}' +
        '' +
        '.sk-fading-circle .sk-circle {' +
        '  width: 100%;' +
        '  height: 100%;' +
        '  position: absolute;' +
        '  left: 0;' +
        '  top: 0;' +
        '}' +
        '' +
        '.sk-fading-circle .sk-circle:before {' +
        '  content: "";' +
        '  display: block;' +
        '  margin: 0 auto;' +
        '  width: 15%;' +
        '  height: 15%;' +
        '  background-color: #333;' +
        '  border-radius: 100%;' +
        '  -webkit-animation: sk-circleFadeDelay 1.2s infinite ease-in-out both;' +
        '          animation: sk-circleFadeDelay 1.2s infinite ease-in-out both;' +
        '}' +
        '.sk-fading-circle .sk-circle2 {' +
        '  -webkit-transform: rotate(30deg);' +
        '      -ms-transform: rotate(30deg);' +
        '          transform: rotate(30deg);' +
        '}' +
        '.sk-fading-circle .sk-circle3 {' +
        '  -webkit-transform: rotate(60deg);' +
        '      -ms-transform: rotate(60deg);' +
        '          transform: rotate(60deg);' +
        '}' +
        '.sk-fading-circle .sk-circle4 {' +
        '  -webkit-transform: rotate(90deg);' +
        '      -ms-transform: rotate(90deg);' +
        '          transform: rotate(90deg);' +
        '}' +
        '.sk-fading-circle .sk-circle5 {' +
        '  -webkit-transform: rotate(120deg);' +
        '      -ms-transform: rotate(120deg);' +
        '          transform: rotate(120deg);' +
        '}' +
        '.sk-fading-circle .sk-circle6 {' +
        '  -webkit-transform: rotate(150deg);' +
        '      -ms-transform: rotate(150deg);' +
        '          transform: rotate(150deg);' +
        '}' +
        '.sk-fading-circle .sk-circle7 {' +
        '  -webkit-transform: rotate(180deg);' +
        '      -ms-transform: rotate(180deg);' +
        '          transform: rotate(180deg);' +
        '}' +
        '.sk-fading-circle .sk-circle8 {' +
        '  -webkit-transform: rotate(210deg);' +
        '      -ms-transform: rotate(210deg);' +
        '          transform: rotate(210deg);' +
        '}' +
        '.sk-fading-circle .sk-circle9 {' +
        '  -webkit-transform: rotate(240deg);' +
        '      -ms-transform: rotate(240deg);' +
        '          transform: rotate(240deg);' +
        '}' +
        '.sk-fading-circle .sk-circle10 {' +
        '  -webkit-transform: rotate(270deg);' +
        '      -ms-transform: rotate(270deg);' +
        '          transform: rotate(270deg);' +
        '}' +
        '.sk-fading-circle .sk-circle11 {' +
        '  -webkit-transform: rotate(300deg);' +
        '      -ms-transform: rotate(300deg);' +
        '          transform: rotate(300deg); ' +
        '}' +
        '.sk-fading-circle .sk-circle12 {' +
        '  -webkit-transform: rotate(330deg);' +
        '      -ms-transform: rotate(330deg);' +
        '          transform: rotate(330deg); ' +
        '}' +
        '.sk-fading-circle .sk-circle2:before {' +
        '  -webkit-animation-delay: -1.1s;' +
        '          animation-delay: -1.1s; ' +
        '}' +
        '.sk-fading-circle .sk-circle3:before {' +
        '  -webkit-animation-delay: -1s;' +
        '          animation-delay: -1s; ' +
        '}' +
        '.sk-fading-circle .sk-circle4:before {' +
        '  -webkit-animation-delay: -0.9s;' +
        '          animation-delay: -0.9s; ' +
        '}' +
        '.sk-fading-circle .sk-circle5:before {' +
        '  -webkit-animation-delay: -0.8s;' +
        '          animation-delay: -0.8s; ' +
        '}' +
        '.sk-fading-circle .sk-circle6:before {' +
        '  -webkit-animation-delay: -0.7s;' +
        '          animation-delay: -0.7s; ' +
        '}' +
        '.sk-fading-circle .sk-circle7:before {' +
        '  -webkit-animation-delay: -0.6s;' +
        '          animation-delay: -0.6s; ' +
        '}' +
        '.sk-fading-circle .sk-circle8:before {' +
        '  -webkit-animation-delay: -0.5s;' +
        '          animation-delay: -0.5s; ' +
        '}' +
        '.sk-fading-circle .sk-circle9:before {' +
        '  -webkit-animation-delay: -0.4s;' +
        '          animation-delay: -0.4s;' +
        '}' +
        '.sk-fading-circle .sk-circle10:before {' +
        '  -webkit-animation-delay: -0.3s;' +
        '          animation-delay: -0.3s;' +
        '}' +
        '.sk-fading-circle .sk-circle11:before {' +
        '  -webkit-animation-delay: -0.2s;' +
        '          animation-delay: -0.2s;' +
        '}' +
        '.sk-fading-circle .sk-circle12:before {' +
        '  -webkit-animation-delay: -0.1s;' +
        '          animation-delay: -0.1s;' +
        '}' +
        '' +
        '@-webkit-keyframes sk-circleFadeDelay {' +
        '  0%, 39%, 100% { opacity: 0; }' +
        '  40% { opacity: 1; }' +
        '}' +
        '' +
        '@keyframes sk-circleFadeDelay {' +
        '  0%, 39%, 100% { opacity: 0; }' +
        '  40% { opacity: 1; } ' +
        '}' +
        '</style>';
    var $mediaModal = $(
        '<div class="modal fade"' +
        '     id="modal-media-lib"' +
        '     tabindex="-1"' +
        '     role="dialog">' +
        '    <div class="modal-dialog modal-lg modal-dialog-centered"' +
        '         role="document">' +
        '        <div class="modal-content">' +
        '            <div class="modal-header">' +
        '                <button type="button"' +
        '                        class="close"' +
        '                        aria-label="Close">' +
        '                    <span aria-hidden="true">&times;</span>' +
        '               </button>' +
        '                <h4 class="modal-title">' + settings.text.title + '</h4>' +
        '            </div>' +
        '            <div class="modal-body">' +
        '                <input type="file"' +
        '                       name="hidden_file"' +
        '                       style="display: none;">' +
        '                <div class="local-files">' +
        '                    <div class="files clearfix">' +
        '                        <div class="is-loading"' +
        '                             style="display: none;">' +
        '                            <div class="sk-fading-circle">' +
        '  <div class="sk-circle1 sk-circle"></div>' +
        '  <div class="sk-circle2 sk-circle"></div>' +
        '  <div class="sk-circle3 sk-circle"></div>' +
        '  <div class="sk-circle4 sk-circle"></div>' +
        '  <div class="sk-circle5 sk-circle"></div>' +
        '  <div class="sk-circle6 sk-circle"></div>' +
        '  <div class="sk-circle7 sk-circle"></div>' +
        '  <div class="sk-circle8 sk-circle"></div>' +
        '  <div class="sk-circle9 sk-circle"></div>' +
        '  <div class="sk-circle10 sk-circle"></div>' +
        '  <div class="sk-circle11 sk-circle"></div>' +
        '  <div class="sk-circle12 sk-circle"></div>' +
        '</div>' +
        '                        </div>' +
        '                    </div>' +
        '                    <div class="upload-wrap">' +
        '                        <button class="btn btn-primary BtnCloudMedia">' + settings.text.internet + '</button>' +
        '                        <button class="btn btn-primary btn-select-file">' + settings.text.upload_new_file + '</button>' +
        '                    </div>' +
        '                </div>' +
        '            </div>' +
        '            <div class="modal-footer">' +
        '                <button type="button"' +
        '                        disabled="disabled"' +
        '                        class="btn btn-primary btn-select">' + settings.text.select + '</button>' +
        '                <button type="button"' +
        '                        class="btn btn-default BtnCancel"' +
        '                        data-dismiss="modal">' + settings.text.cancel + '</button>' +
        '            </div>' +
        '        </div>' +
        '    </div>' +
        cssStyle +
        '</div>');


    var $localFilesEl = $mediaModal.find('.local-files');
    var localFilesPage = 1;
    var isLoadingLocalFiles = false;
    var canLoadMoreLocalFiles = true;

    var audioExtensions = ['mp3', 'mp4', 'm4a'];
    var imageExtensions = ['png', 'gif', 'jpeg', 'jpg'];
    var videoExtensions = [
        "3g2",
        "3gp",
        "aaf",
        "asf",
        "avchd",
        "avi",
        "drc",
        "flv",
        "m2v",
        "m4p",
        "m4v",
        "mkv",
        "mng",
        "mov",
        "mp2",
        "mp4",
        "mpe",
        "mpeg",
        "mpg",
        "mpv",
        "mxf",
        "nsv",
        "ogg",
        "ogv",
        "qt",
        "rm",
        "rmvb",
        "roq",
        "svi",
        "vob",
        "webm",
        "wmv",
        "yuv"
    ];

    var isImageFileByExtension = function (fileExtension) {
        return imageExtensions.indexOf(fileExtension.toLowerCase()) > -1;
    };

    var isVideoFileByExtension = function (fileExtension) {
        return videoExtensions.indexOf(fileExtension.toLowerCase()) > -1;
    };

    var isAudioFileByExtension = function (fileExtension) {
        return audioExtensions.indexOf(fileExtension) > -1;
    };

    var getFileTypeFromExtension = function (fileExtension) {
        if (isImageFileByExtension(fileExtension)) {
            return 'image';
        }

        if (isVideoFileByExtension(fileExtension)) {
            return 'video';
        }

        if (isAudioFileByExtension(fileExtension)) {
            return 'audio';
        }

        return 'file';
    };

    var hideModal = function () {
        $mediaModal.modal('hide');
    };

    var addMediaEntry = function (entry, position) {
        var getAvatarIcon = function (type) {
            if(type == 'video') {
                return 'iwan-video';
            }

            if(type == 'audio') {
                return 'iwan-music';

            }

            if(type == 'image') {
                return 'iwan-images';

            }

            return 'iwan-file-text';
        };

        var file = $('<div class="file">' +
                '<div class="inner-details">' +
                    '<i class="iwan-check"></i>' +
                    '<div class="avatar">' +
                        '<i class="'+getAvatarIcon(entry.type)+'"></i>' +
                    '</div>' +
                    '<div class="file-name"></div>' +
                '</div>' +
            '</div>');
        file.data("entry", entry);
        file.find('.inner-details .file-name').text(entry.original_name);

        if (entry.thumbnail_url && entry.thumbnail_url.trim() != "") {
            var fileExtension = entry.thumbnail_url.split('.').pop();
            var fileType = getFileTypeFromExtension(fileExtension);

            if(fileType == "image") {
                file.find('.inner-details .avatar').css('background-image', 'url("' + entry.thumbnail_url + '")');
                file.find('.inner-details .avatar').addClass('has-thumbnail');
            }
        }

        if (position == 'start') {
            $localFilesEl.find('.files').prepend(file);
        } else {
            file.insertBefore($localFilesEl.find('.files .is-loading'));
        }

        return file;
    }

    var getFiles = function (types, hosted) {
        var page = localFilesPage;
        page = page ? parseInt(page) : 1;

        $.ajax({
            type: 'GET',
            url: settings.listFilesUrl,
            dataType: "json",
            timeout: 60000,
            data: {
                page: page,
                types: types.join(','),
                hosted: hosted,
                /*
                            name: '32187384_10215160837789723_7121839324831678464_n (1).jpg'
                */
            },
            beforeSend: function (xhr) {
                isLoadingLocalFiles = true;
                $localFilesEl.find('.files .is-loading').show();
            },
            error: function (x, t, m) {

            },
            success: function (response) {
                response.data.forEach(function (entry) {
                    addMediaEntry(entry);
                });

                if (!response.data || !response.data.length || response.data.length < response.per_page) {
                    canLoadMoreLocalFiles = false;
                }

                localFilesPage += 1;
                isLoadingLocalFiles = false;
                $localFilesEl.find('.files .is-loading').fadeOut('fast');
            }
        });
    }

    var selectFile = function (fileEl) {
        $localFilesEl.find('.file').removeClass('is-selected');
        fileEl.toggleClass('is-selected');
    };

    var uploadMediaFile = function (event) {
        var file = event.target.files[0];

        event.stopPropagation(); // Stop stuff happening
        event.preventDefault(); // Totally stop stuff happening

        // START A LOADING SPINNER HERE
        var fileExtension = file.name.split('.').pop();
        var fileType = getFileTypeFromExtension(fileExtension);
        var types = settings.type;

        if (types.indexOf(fileType) === -1) {
            $.confirm({
                type: 'red',
                title: 'Wrong Files',
                content: 'Please chose a valid file type (' + types.join(', ') + ') to upload.',
                rtl: true,
                closeIcon: true,
                draggable: false,
                buttons: {
                    confirm: {
                        text: 'Close',
                        btnClass: 'btn-blue',
                        action: function () {

                        }
                    }
                }
            });

            return;
        }

        // Create a formdata object and add the files
        var data = new FormData();
        data.append('file', file);
        data.append('resource', settings.resource);

        var fileEl = $('<div class="file">' +
            '<div class="inner-details">' +
                '<i class="iwan-check"></i>' +
                '<div class="upload-progress">' +
                    '<div class="upload-progress">' +
                        '<div class="complete"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="file-name"></div>' +
            '</div>' +
            '</div>');
        $localFilesEl.find('.files').prepend(fileEl);
        selectFile(fileEl);

        $.ajax({
            url: settings.uploadUrl,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function (data, textStatus, jqXHR) {
                var newFileEl = addMediaEntry(data, 'start');
                fileEl.remove();
                selectFile(newFileEl);
                $mediaModal.find('.modal-footer .btn-select').prop('disabled', false);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('ERRORS: ' + textStatus);
            },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();

                //Do something with upload progress here
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        fileEl.find('.upload-progress .complete').css('width', percentComplete * 100 + '%');
                    }
                }, false);

                //Do something with download progress
                xhr.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                    }
                }, false);

                return xhr;
            },
        });
    }

    var init = function () {

        $mediaModal.on('hidden.bs.modal', function () {
            $mediaModal.remove();
        });

        $mediaModal.find('.close, .BtnCancel').click(function () {
            hideModal();
        });

        $localFilesEl.on('click', '.file', function () {
            selectFile($(this));
            $mediaModal.find('.modal-footer .btn-select').prop('disabled', false);
        });

        $localFilesEl.find('.files').scroll(function () {
            var $this = $(this);
            var $results = $this;

            if (!isLoadingLocalFiles && canLoadMoreLocalFiles) {
                if ($this.scrollTop() + $this.height() > $results.height() - 50) {
                    getFiles(settings.type, settings.hosted);
                }
            }
        });

        $mediaModal.find('.btn-select-file').click(function () {
            $mediaModal.find('input[name="hidden_file"]').click();
        });

        $mediaModal.find('.BtnCloudMedia').click(function () {
            showCloudMediaModal({
                resource: settings.resource,
                onMediaCreated: function (entry) {
                    var newFileEl = addMediaEntry(entry, 'start');
                    selectFile(newFileEl);
                    $mediaModal.find('.modal-footer .btn-select').prop('disabled', false);
                }
            });
        });

        $mediaModal.find('input[name=hidden_file]').on('change', uploadMediaFile);

        $('body').append($mediaModal);
        $mediaModal.modal({
            backdrop: 'static'
        });

        getFiles(settings.type, settings.hosted);
    };

    init();
};

module.exports = showMediaModal;
