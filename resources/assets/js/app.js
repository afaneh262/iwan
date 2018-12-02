window.jQuery = window.$ = $ = require('jquery');
window.Vue = require('vue');
window.perfectScrollbar = require('perfect-scrollbar/jquery')($);
window.Cropper = require('cropperjs');
window.toastr = require('../libs/toastr/toastr');
require('jquery-confirm');
require('bootstrap');
require('bootstrap-switch');
window.DataTable = require('../libs/bootstrap-datatables/bootstrap-datatables');
require('bootstrap-datetimepicker/src/js/bootstrap-datetimepicker');
window.tooltip = require('../libs/bootstrap-tooltip/bootstrap-tooltip');
window.SimpleMDE = require('simplemde');

require('dropzone');
window.TinyMCE = window.tinymce = require('../libs/tinymce/tinymce');
require('../libs/readmore/readmore');
require('../libs/jquery-match-height/jquery-match-height');
require('../libs/bootstrap-toggle/bootstrap-toggle');
require('../libs/jquery-cookie/jquery-cookie');
require('../libs/jquery-nestable/jquery-nestable');

require('select2');
var brace = require('brace');

require('brace/mode/json');
require('brace/theme/github');

window.MediaManager = require('./media');
require('./slugify');
require('./multilingual');
require('./iwan_tinymce');
require('./iwan_ace_editor');
window.showMediaModal =  require('./media_modal');
window.helpers = require('./helpers.js');

$(document).ready(function () {

    var appContainer = $(".app-container");
    var fadedOverlay = $('.fadetoblack');
    var hamburger = $('.hamburger');

    $('.side-menu').perfectScrollbar();

    $('#iwan-loader').fadeOut();
    $('.readmore').readmore({
        collapsedHeight: 60,
        embedCSS: true,
        lessLink: '<a href="#" class="readm-link">Read Less</a>',
        moreLink: '<a href="#" class="readm-link">Read More</a>',
    });

    $(".hamburger, .navbar-expand-toggle").on('click', function () {
        appContainer.toggleClass("expanded");
        $(this).toggleClass('is-active');
        if ($(this).hasClass('is-active')) {
            window.localStorage.setItem('iwan.stickySidebar', true);
        } else {
            window.localStorage.setItem('iwan.stickySidebar', false);
        }
    });

    $('select.select2').select2({width: '100%'});
    $('select.select2-taggable').select2({
        width: '100%',
        tags: true,
        createTag: function (params) {
            var term = $.trim(params.term);

            if (term === '') {
                return null;
            }

            return {
                id: term,
                text: term,
                newTag: true
            }
        }
    }).on('select2:selecting', function (e) {
        var $el = $(this);
        var route = $el.data('route');
        var label = $el.data('label');
        var errorMessage = $el.data('error-message');
        var newTag = e.params.args.data.newTag;

        if (!newTag) return;

        $el.select2('close');

        $.post(route, {
            [label]: e.params.args.data.text,
        }).done(function (data) {
            var newOption = new Option(e.params.args.data.text, data.data.id, false, true);
            $el.append(newOption).trigger('change');
        }).fail(function (error) {
            toastr.error(errorMessage);
        });

        return false;
    });

    $('.match-height').matchHeight();

    $('.datatable').DataTable({
        "dom": '<"top"fl<"clear">>rt<"bottom"ip<"clear">>'
    });

    $(".side-menu .nav .dropdown").on('show.bs.collapse', function () {
        return $(".side-menu .nav .dropdown .collapse").collapse('hide');
    });

    $(document).on('click', '.panel-heading a.panel-action[data-toggle="panel-collapse"]', function (e) {
        e.preventDefault();
        var $this = $(this);

        // Toggle Collapse
        if (!$this.hasClass('panel-collapsed')) {
            $this.parents('.panel').find('.panel-body').slideUp();
            $this.addClass('panel-collapsed');
            $this.removeClass('iwan-angle-up').addClass('iwan-angle-down');
        } else {
            $this.parents('.panel').find('.panel-body').slideDown();
            $this.removeClass('panel-collapsed');
            $this.removeClass('iwan-angle-down').addClass('iwan-angle-up');
        }
    });

    //Toggle fullscreen
    $(document).on('click', '.panel-heading a.panel-action[data-toggle="panel-fullscreen"]', function (e) {
        e.preventDefault();
        var $this = $(this);
        if (!$this.hasClass('iwan-resize-full')) {
            $this.removeClass('iwan-resize-small').addClass('iwan-resize-full');
        } else {
            $this.removeClass('iwan-resize-full').addClass('iwan-resize-small');
        }
        $this.closest('.panel').toggleClass('is-fullscreen');
    });

    $('.datepicker').datetimepicker();

    // Save shortcut
    $(document).keydown(function (e) {
        if ((e.metaKey || e.ctrlKey) && e.keyCode == 83) { /*ctrl+s or command+s*/
            $(".btn.save").click();
            e.preventDefault();
            return false;
        }
    });

    /********** MARKDOWN EDITOR **********/

    $('textarea.simplemde').each(function () {
        var simplemde = new SimpleMDE({
            element: this,
        });
        simplemde.render();
    });

    /********** END MARKDOWN EDITOR **********/

});


$(document).ready(function () {
    $(".form-edit-add").submit(function (e) {
        e.preventDefault();

        var url = $(this).attr('action');
        var form = $(this);
        var data = new FormData();

        // Safari 11.1 Bug
        // Filter out empty file just before the Ajax request
        // https://stackoverflow.com/questions/49672992/ajax-request-fails-when-sending-formdata-including-empty-file-input-in-safari
        for (i = 0; i < this.elements.length; i++) {
            if (this.elements[i].type == 'file') {
                if (this.elements[i].value == '') {
                    continue;
                }
            }
            data.append(this.elements[i].name, this.elements[i].value)
        }

        data.set('_validate', '1');

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,

            beforeSend: function () {
                $("body").css("cursor", "progress");
                $(".has-error").removeClass("has-error");
                $(".help-block").remove();
            },

            success: function (d) {
                $("body").css("cursor", "auto");
                $.each(d.errors, function (inputName, errorMessage) {

                    // This will work also for fields with brackets in the name, ie. name="image[]
                    var $inputElement = $("[name='" + inputName + "']"),
                        inputElementPosition = $inputElement.first().parent().offset().top,
                        navbarHeight = $('nav.navbar').height();

                    // Scroll to first error
                    if (Object.keys(d.errors).indexOf(inputName) === 0) {
                        $('html, body').animate({
                            scrollTop: inputElementPosition - navbarHeight + 'px'
                        }, 'fast');
                    }

                    // Hightlight and show the error message
                    $inputElement.parent()
                        .addClass("has-error")
                        .append("<span class='help-block' style='color:#f96868'>" + errorMessage + "</span>")

                });
            },

            error: function () {
                $(form).unbind("submit").submit();
            }
        });
    });
});