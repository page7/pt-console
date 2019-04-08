// rewrite window function

function alert(msg, type, callback, container){

    if (type === undefined) type = 'error';
    var cla = 'alert-danger';
    var stxt = '错误! ';
    switch(type){
        case 'error': cla = 'alert-danger'; stxt = '错误! '; break;
        case 'warning': cla = 'alert-warning'; stxt = '警告! '; break;
        case 'success': cla = 'alert-success'; stxt = '成功! '; break;
    }

    var di = $("<div class=\"alert "+cla+"\" role=\"alert\" style=\"display:none\"></div>");

    di.html("<strong>" + stxt + "</strong>" + msg);
    di.children("a").addClass("alert-link");
    if (container === undefined){
        $("h1.page-header").after(di);
        var top = $("h1.page-header").offset().top;
    }else{
        $(container).prepend(di);
        var top = $(container).offset().top;
    }


    var pageWidth = $('.page-header').width();
    if($(window).scrollTop() > top){
        // $(window).scrollTop(top);
        di.css({'position':'fixed','z-index':'99','top':'70px','width':pageWidth});
    }
    di.slideDown(300).delay(3000).slideUp(300, function(){
        if (callback !== undefined && callback) callback();
        $(this).remove();
    });
}

/*
function history(a){
    var a = $(a), d = a.data(), li = a.parent();
    if (d.status == "loading") return;
    var tmp = $("<li class=\"list-group-item\"><span class=\"log\"><b></b></span><span class=\"time\"></span></li>");

    a.text("正在读取..").data("status", "loading");
    $.get(d.url, {page:d.page}, function(data){
        if (data.s == 0){
            for (x in data.rs){
                var t = data.rs[x],
                    c = tmp.clone();
                c.children(".time").text(t.time).prev(".log").text(t.intro).prepend("<b></b>").children("b").text(t.username+" ").data("history", t.id);
                li.before(c);
            }
            if (data.rs.length == 10)
                a.text("查看更多记录").data({status:"loaded", page:parseInt(d.page,10)+1});
            else
                a.after("<span class=\"c9\">无更多记录</span>").remove();
        }else{
            a.text("读取失败，请重试").data("status", "loaded");
        }
    }, "json");
}
*/

$(function(){

    var win = $(window),
        doc = $(document),
        sidebar = $("#sidebar"),
        sidebar_toggle = $("#header .sidebar-toggle");

    win.on("scroll", function(){
    });

    sidebar.click(function(e) {
        if (sidebar.is(".active")) {
            if($(e.target).is("#sidebar")) {
                sidebar.removeClass("active");
                sidebar_toggle.removeClass("active");
                sidebar.delay(500).animate({"left":"-100%"}, 1, function(){ $(this).removeAttr("style"); });
            }
        }
    });

    sidebar.on("click", "a", function(e) {
        sidebar.find(".active").removeClass("active");
        $(this).parents("li").addClass("active");
    });

    sidebar_toggle.on("click", function(e) {
        e.preventDefault();
        if(sidebar.is(".active")){
            sidebar.removeClass("active");
            sidebar_toggle.removeClass("active");
            sidebar.delay(500).animate({"left":"-100%"}, 1, function(){ $(this).removeAttr("style"); });
        }else{
            sidebar.css("left", "0%");
            sidebar.addClass("active");
            sidebar_toggle.addClass("active");
        }
    });

    $("#header a").click(function(){ sidebar.find("li.active").removeClass("active"); });

    $(".table .checked-all").click(function(){
        var _c = $(this);
        var table = _c.parents(".table").eq(0);
        var checked = _c.prop("checked");
        table.find("input.checkbox").prop("checked", checked);
    });

    $(".pagination input").keyup(function(e){
        if (e.which == 13){
            var url = $(this).data("url");
            var page = parseInt($(this).val(), 10);
            if (isNaN(page)) return;
            location.href = url + page;
        }
    });

    doc.on('keyup', '[data-enter]', function(e){
        var func = $(this).data("enter");
        if (e.which != 13) return;
        try{
            eval(func);
        }catch(e){
            console.err(e);
        }
    });

    if ($.fn.highlightRegex !== undefined) {
        doc.on('keyup', '[data-search]', function(e){
            var target  = $(this).data("search");
            var val = $.trim(this.value);
            var regex   = new RegExp(val, "ig");
            $(target).highlightRegex({tagType:"mark"});

            if (val == "") return;
            if (regex !== undefined){
                $(target).highlightRegex(regex, {tagType:"mark"});
            }
        });

        var _searchInput = $('input[data-search]');
        if(_searchInput.length) {
            _searchInput.each(function(){
                var _this = $(this);
                var val = $.trim(this.value);
                if (val != "") {
                    var regex = new RegExp(val, "ig");
                    $(_this.data("search")).highlightRegex(regex, {tagType:"mark"});
                }
            });
        }
    }

    window.tinymces = [];

    if ($.support.pjax) {

        doc.on('click', 'a[data-pjax-container]', function(event) {
            var _this = $(this),
                container = $(_this.data('pjax-container'));
            $.pjax.click(event, {container: container});
        });

        doc.on('pjax:beforeSend', function(event, xhr) {

            if (window.tinymces.length) {
                $(window.tinymces).each(function(i){
                    $(this).tinymce().editorManager.remove();
                    window.tinymces.shift();
                });
            }

            var container = $(event.target),
                xmlHttpReq = $.ajaxSettings.xhr();

            container.loadbar({"query":true});
            container.loadbar("start");
            container.addClass("anim-hide").removeClass("anim-show");

            xmlHttpReq.addEventListener('progress', function(e) {
                if (e.total){
                    var pers = e.loaded / e.total;
                } else {
                    var total = xmlHttpReq.getResponseHeader('PJAX-content-length'),
                        pers = e.loaded / total;
                }
                container.loadbar(pers);
            }, false);
        }).on('pjax:success', function(event) {
            var container = $(event.target);
            container.loadbar("hide");
            container.addClass("anim-show").removeClass("anim-hide");
        });

    }

    doc.on("keyup focus blur", "input[maxlength], textarea[maxlength]", function(e){
        var i = $(this), max = i.attr("maxlength");
        if (i.is(".ui-datepicker")) return;
        switch (e.type) {

            case 'focusout':
                $(i.data('_tc')).remove();
                i.data('_tc', null);
                break;

            case 'focusin':
                var g = i.parents('.input-group'), c = $('<i class="txt-count" style="display:none"></i>');
                if (g.length) {
                    g.after(c);
                } else {
                    i.after(c);
                }
                i.data('_tc', c.get(0));

            default:
                var len = i.val().length, c = $(i.data('_tc'));
                c.show();

                if (len == 0)
                    c.text('');
                else
                    c.text(len + '/' + max + '字');

                if (len >= max)
                    c.addClass('over');
                else
                    c.removeClass('over');
        }
    });

    // Operate
    doc.on("click", ".btn-operate", function(e){
        var b = $(this),
            data = b.data(),
            operate = data.operate,
            url = location.search;

        if (operate === undefined) return;

        if (data.url !== undefined) {
            url = data.url;
        } else {
            var regexp = /module=([a-zA-Z_]+)?/;
            module = url.match(regexp);
            url = location.pathname + '?' + module[0];
        }

        if (!b.is(".btn-remove") || window.confirm("此操作将使数据无法恢复，是否继续？")) {
            b.prop("disabled", true);
            $.post(url+'&operate=' + operate, data, function(data){
                b.prop("disabled", false);
                if (data.s == 0){
                    if ($.support.pjax) {
                        $.pjax({ url: location.pathname + location.search + "&_=" + new Date().getTime(), container: "#main" });
                    } else {
                        location.href = location.pathname + location.search + "&_=" + new Date().getTime();
                    }
                }else{
                    alert(data.err, "error");
                }
            }, "json");
        }
    });

    // List append
    window.list_append = function(ids) {
        var btns = $(ids);
        btns.each(function(){
            var btn = $(this),
                tmpl = btn.data("tmpl") ? btn.data("tmpl") : btn.prev(".form-control, .input-group").clone();

            if (!btn.data("tmpl")) {
                tmpl.find("input").addBack("input").val('');
                btn.data("tmpl", tmpl);
            }

            btn.click(function() {
                var b = $(this), max = parseInt(b.data("max"), 10), len = btn.prevAll(".form-control, .input-group").length;
                b.before(b.data("tmpl").clone());
                if (len + 1 >= max)
                    b.hide();
            });
        });
    };

    // Image Upload
    window.image_uploader = function(module, id, multiple, tmpl) {
        var updom = $("#"+id),
            multiple = multiple === undefined ? false : multiple,
            uploader = new plupload.Uploader({
                runtimes : 'html5,flash,html4',
                browse_button : id,
                url : "?module=file&operate=upload",
                multi_selection: false,
                multipart_params: {
                    'type': module
                },
                filters : {
                    max_file_size : '10mb',
                    mime_types: [{title : "Image files", extensions : "jpg,jpeg,png"}]
                },
                flash_swf_url : '/js/plupload/Moxie.swf',
                init: {
                    Init: function(up) {
                        return true;
                    },

                    FilesAdded: function(up, files) {
                        updom.before('<div id="'+files[0].id+'" class="image image-loading">上传中..</div>');
                        updom.hide();
                        up.start();
                    },

                    UploadProgress: function(up, file) {
                        $("#" + file.id).text(file.percent+"%");
                    },

                    FileUploaded: function(up, file, res) {
                        if (res.status != 200) {
                            $("#" + file.id).remove();
                            updom.show().addClass("image-error").text("网络异常");
                            alert(data.err, 'error');
                        } else {
                            var data = $.parseJSON(res.response);
                            if(!data.s){
                                $("#" + file.id)
                                    .removeClass("image-loading")
                                    .css({"background-image":"url(/upload/"+data.rs+")"})
                                    .html('<input type="hidden" name="'+id+(multiple ? '[]' : '')+'" value="" />' + tmpl)
                                    .find("input").eq(0).val(data.rs);

                                if (multiple)
                                    updom.show();
                                else
                                    updom.children("input").remove();

                                up.refresh();
                                updom.trigger("uploader:complete");

                            }else{
                                $("#" + file.id).remove();
                                updom.show().addClass("image-error").text("上传失败");
                                alert("上传失败，错误：["+data.s+"]"+data.err, 'error');
                                updom.trigger("uploader:error");
                            }
                        }
                    }
                }
            });

        uploader.init();

        updom.parents(".image-upload").on("click", ".image .rm", function(){
            var that = $(this).parents(".image");
            updom.removeClass("image-error");
            if (!multiple)
                updom.show().html('<input type="hidden" name="'+id+'" value="" />选择图片');

            that.remove();
            uploader.refresh();
        });
    };


    // Modal
    window.modal = function(id) {
        var modal = $("#modal-"+id),
            body = modal.children('.modal-dialog').children('.modal-content');

        modal.on("show.bs.modal", function(e) {
            var src = $(e.relatedTarget),
                url = src.data("url");

            body.addClass('modal-loading');

            $.ajax({
                url: url,
                data: {modal:1},
                success: function(data){
                    // include js
                    var regex = /(?:src)="(.*?[js])"/ig,
                        form, btn, i, scripts, loaded = 0;

                    scripts = data.match(regex);
                    data = data.replace(regex, '');

                    var lazyDo = function() {
                        loaded ++;

                        if (scripts && loaded < scripts.length) return;

                        body.removeClass('modal-loading').html(data);

                        form = body.find("form");
                        btn = body.find(".btn-save");

                        btn.click(function(){

                            var btn = $(this);

                            if (btn.is(".btn-save")) {

                                btn.prop("disabled", true);
                                if (btn.is(".btn-save")) btn.text("保存中..");

                                $.post(form.attr("action"), form.serialize(), function(data){
                                    btn.prop("disabled", false)
                                    btn.text("保存");

                                    if (data.s == 0){
                                        modal.on('hidden.bs.modal', function (e) {
                                            if ($.support.pjax) {
                                                $.pjax({ url: location.pathname + location.search + (btn.is(".btn-save") ? "#success" : ''), container: "#main" });
                                            } else {
                                                location.href = location.pathname + location.search + (btn.is(".btn-save") ? "#success" : '');
                                            }
                                        }).modal("hide");
                                    } else {
                                        alert(data.err, 'error', null, body.children(".modal-body"));
                                    }
                                }, "json");

                            }

                        });
                    };

                    if (!scripts) lazyDo();

                    for (i in scripts) {
                        $.getScript(scripts[i].substr(5, scripts[i].length-6))
                            .done(function(){ lazyDo(); })
                            .fail(function(){
                                body.modal("hide").removeClass('modal-loading');
                                alert("页面加载失败，请重试", "error");
                            });
                    }
                },
                dataType: "html"
            });

        });
    };

    // Runing all exts
    doc.on('pjax:success ready', function(event) {
        if ($.fn.chosen) {
            $(".ui-select").chosen({width:"100%", disable_search_threshold:10});
        }
    });




});


// Loadbar
;(function ($) {

$.fn.loadbar = function(options) {
    if (typeof(options) == "string") {
        var loader = this.data("loadbar");
        switch (options) {
            case 'start':
                loader.start();
            case 'hide':
                loader.end();
            default:
                if (isNaN(options)) return;
                loader.progress(options);
        }

    }else if (typeof(options) == "number") {
        var loader = this.data("loadbar");
        loader.progress(options);

    }else{
        var opts = $.extend({}, $.fn.loadbar.defaults, options);

        function loaderbar(container, opts){

            var _con = $(container),
                loadbar = _con.data("loadbar");

            this.opts = opts;

            this.bar = loadbar ? loadbar.bar : $('<div class="jq-loadbar" style="display:none"><span class="jq-loadbar-progress" style="width:0px;"></span></div>');

            this.handle = this.bar.children("span");

            if (this.opts.query) {
                this.bar.addClass("jq-loadbar-query");
            }

            if (this.opts.progress) {
                this.handle.css("width", this.opts.progress+"%");
            }

            if (!loadbar) _con.before(this.bar);

            this.start = function() {
                this.bar.stop(true).fadeIn(200).removeClass("jq-loadbar-query");
                this.handle.css("width", "0%");
            }

            this.end = function() {
                this.bar.delay(500).fadeOut(200);
            }

            this.progress = function(progress){
                if (progress > 1) progress = 1;
                this.handle.css("width", parseInt(progress * 100, 10)+"%");
            }

            return this;
        }

        return this.each(function(){
            $(this).data("loadbar", loaderbar(this, opts));
        });
    }
};


$.fn.loadbar.defaults = {query:false, indeterminate:false, preload:false, progress:0};

})(jQuery);