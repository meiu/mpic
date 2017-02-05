/*!
 * MeiuPic common js v2.1
 * http://meiu.cn/
 *
 * Copyright 2011, Lingter
 */
 
/*drag and drop start*/
(function($){
$.fn.jqDrag=function(h){return i(this,h,'d');};
$.fn.jqResize=function(h){return i(this,h,'r');};
$.jqDnR={dnr:{},e:0,
drag:function(v){
 if(M.k == 'd')E.css({left:M.X+v.pageX-M.pX,top:M.Y+v.pageY-M.pY});
 else E.css({width:Math.max(v.pageX-M.pX+M.W,0),height:Math.max(v.pageY-M.pY+M.H,0)});
  return false;},
stop:function(){if(!jQuery.browser.msie){E.css('opacity',M.o);}$(document).unbind('mousemove',J.drag).unbind('mouseup',J.stop);}
};
var J=$.jqDnR,M=J.dnr,E=J.e,
i=function(e,h,k){return e.each(function(){h=(h)?$(h,e):e;
 h.bind('mousedown',{e:e,k:k},function(v){var d=v.data,p={};E=d.e;
 // attempt utilization of dimensions plugin to fix IE issues
 if(E.css('position') != 'relative'){try{E.position(p);}catch(e){}}
 M={X:p.left||f('left')||0,Y:p.top||f('top')||0,W:f('width')||E[0].scrollWidth||0,H:f('height')||E[0].scrollHeight||0,pX:v.pageX,pY:v.pageY,k:d.k,o:E.css('opacity')};
 if(!jQuery.browser.msie){E.css({opacity:0.8});}$(document).mousemove($.jqDnR.drag).mouseup($.jqDnR.stop);
 return false;
 });
});},
f=function(k){return parseInt(E.css(k))||false;};
})(jQuery);
/*drag and drop end*/
/*jquery plugin addOption*/
jQuery.fn.addOption = function(text,value){jQuery(this).get(0).options.add(new Option(text,value));}

/*jquery plugin cookie*/
jQuery.cookie = function (key, value, options) {
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);
        if (value === null || value === undefined) {
            options.expires = -1;
        }
        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }
        value = String(value);
        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};
/**
 * rotate plugin
 * ok!: MSIE 6, 7, 8, Firefox 3.6, chrome 4, Safari 4, Opera 10
 * @gbook: http://byzuo.com/blog/html5-canvas-rotate
 * @demo:  http://byzuo.com/demo/jq/rotate
 *
 * @example $imgID.rotate('cw')、$imgID.rotate('ccw')
 * @p = rotate direction clockwise(cw)、anticlockwise(ccw)
 */
(function($){
	$.fn.extend({
		"rotate":function(p){
			var $img = $(this);
			var n = $img.attr('step');
			if(n== null) n=0;
			if(p== 'cw'){
				(n==3)? n=0:n++;
			}else if(p== 'ccw'){
				(n==0)? n=3:n--;
			}
			$img.attr('step',n);
			//MSIE
			if($.browser.msie) {
				$img.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation='+ n +')');
				//MSIE 8.0
				if($.browser.version == 8.0){
					if(!$img.parent('div').hasClass('wrapImg')){
						$img.wrap('<div class="wrapImg"></div>');	
					}
					$img.parent('div.wrapImg').height($img.height());
				}
			//DOM
			}else{
				if(!$img.siblings('canvas').hasClass('imgCanvas')){
					$img.css({'position':'absolute','visibility':'hidden'})
						.after('<canvas class="imgCanvas"></canvas>');
				}
				var c = $img.siblings('canvas.imgCanvas')[0], img = $img[0];
				var canvasContext = c.getContext('2d');
				switch(n) {
					default :
					case 0 :
						c.setAttribute('width', img.width);
						c.setAttribute('height', img.height);
						canvasContext.rotate(0 * Math.PI / 180);
						canvasContext.drawImage(img, 0, 0);
						break;
					case 1 :
						c.setAttribute('width', img.height);
						c.setAttribute('height', img.width);
						canvasContext.rotate(90 * Math.PI / 180);
						canvasContext.drawImage(img, 0, -img.height);
						break;
					case 2 :
						c.setAttribute('width', img.width);
						c.setAttribute('height', img.height);
						canvasContext.rotate(180 * Math.PI / 180);
						canvasContext.drawImage(img, -img.width, -img.height);
						break;
					case 3 :
						c.setAttribute('width', img.height);
						c.setAttribute('height', img.width);
						canvasContext.rotate(270 * Math.PI / 180);
						canvasContext.drawImage(img, -img.width, 0);
						break;
				};
			}
			return n;
		}
	});
})(jQuery);
//Placeholder plugin
(function(a){a.Placeholder={settings:{color:"rgb(169,169,169)",dataName:"original-font-color"},init:function(b){if('placeholder' in document.createElement('input')){return;}if(b){a.extend(a.Placeholder.settings,b)}var c=function(b){return a(b).val()};var d=function(b,c){a(b).val(c)};var e=function(b){return a(b).attr("placeholder")};var f=function(a){var b=c(a);return b.length===0||b==e(a)};var g=function(b){a(b).data(a.Placeholder.settings.dataName,a(b).css("color"));a(b).css("color",a.Placeholder.settings.color)};var h=function(b){a(b).css("color",a(b).data(a.Placeholder.settings.dataName));a(b).removeData(a.Placeholder.settings.dataName)};var i=function(a){d(a,e(a));g(a)};var j=function(b){if(a(b).data(a.Placeholder.settings.dataName)){d(b,"");h(b)}};var k=function(){if(f(this)){j(this)}};var l=function(){if(f(this)){i(this)}};var m=function(){if(f(this)){j(this)}};a("textarea, input[type='text']").each(function(b,c){if(a(c).attr("placeholder")){a(c).focus(k);a(c).blur(l);a(c).bind("parentformsubmitted",m);a(c).trigger("blur");a(c).parents("form").submit(function(){a(c).trigger("parentformsubmitted")})}});return this},cleanBeforeSubmit:function(b){if(!b){b=a("form")}a(b).find("textarea, input[type='text']").trigger("parentformsubmitted");return b}}})(jQuery)

var Mui = {
    centerMe : function(jel){
        var w_w = $(jel).outerWidth();
        var w_h = $(jel).outerHeight();
        var left = $(window).scrollLeft() + (($(window).width()-w_w)/2);
        if($(jel).css('position') == 'fixed'){
            var top = ((document.documentElement.clientHeight-w_h)/2) - 50;
        }else{
            var top = $(window).scrollTop() + ((document.documentElement.clientHeight-w_h)/2) - 50;
        }
        if( top < 20 ) top = 20;
        $(jel).css({'left':left});
        $(jel).css({'top':top});
    },
    moveToBeside: function(obj,jel){
        var pos = $(obj).offset();
        var width = $(obj).width();
        var height = $(obj).height();
        var rightp = pos.left+$(jel).outerWidth();
        
        if(rightp > $(document.body).width()){
            $(jel).css({'right':10,'left':''});
        }else{
            $(jel).css({'left':pos.left,'right':''});
        }
        $(jel).css({'top':pos.top+height+3});
    }
};

Mui.box = {
    callback : null,
    onClose : null,
    show : function(url,modal){
        Mui.bubble.close();
        
        if( $('#meiu_float_box').length == 0 ){
            $('body').prepend('<div id="meiu_float_box"></div>');
        }
        if(jQuery.browser.msie && jQuery.browser.version < 7){
            if($('iframe.bg_iframe').length == 0){
                $('body').append('<iframe class="bg_iframe" scrolling="no" frameborder="0" style="position: absolute;"></iframe>');
            }
        }
        if(modal && $('div.modaldiv').length == 0){
            var h = $(document).height();
            $('body').append('<div class="modaldiv" style="height:'+h+'px"></div>');
        }
        if(url){
            $.get(url,{ajax:'true','_t':Math.random()}, function(data) {
                $('#meiu_float_box').html(data);
                Mui.centerMe('#meiu_float_box');
                $('#meiu_float_box').jqDrag('.box_title');
            });
            $('#meiu_float_box').html('<div class="loading">Loading...</div>');
            $('#meiu_float_box').show();
            Mui.centerMe('#meiu_float_box');
        }else{
            $('#meiu_float_box').show();
            Mui.centerMe('#meiu_float_box');
            $('#meiu_float_box').jqDrag('.box_title');
        }
    },
    setData : function(data,modal){
        if( $('#meiu_float_box').length == 0 ){
            $('body').prepend('<div id="meiu_float_box"></div>');
        }
        $('#meiu_float_box').html(data);
        this.show(false,modal);
    },
    resize: function(w,h){
        $('#meiu_float_box').width(w);
        if(h){
            $('#meiu_float_box').height(h);
        }
    },
    close : function(){
        $('#meiu_float_box').hide();
        if(jQuery.browser.msie && jQuery.browser.version < 7){
            $('iframe').remove('.bg_iframe');
        }
        $('div').remove('.modaldiv');
        this.callback = null;
        if(this.onClose){
            this.onClose()
            this.onClose = null;
        }
    },
    alert: function(title,content,btn_name){
        Mui.box.setData('<div class="box_title titbg">'+
            '<div class="closer sprite i_close" onclick="Mui.box.close()"></div>'+
            title+'</div><div class="box_container">'+'<div>'+content+'</div>'+
            '<div class="b_btns"><input type="button" value="'+btn_name+'" class="ml10 ylbtn f_left" name="cancel" onclick="Mui.box.close()"></div></div>',true);
        $('#meiu_float_box').jqDrag('.box_title');
    }
};

Mui.bubble = {
    callback : null,
    onClose: null,
    show : function(obj,url,modal){
        if( $('#meiu_float_bubble').length == 0 ){
            $('body').prepend('<div id="meiu_float_bubble"><div class="arrow"></div><div class="bubble_container"></div></div>');
        }
        if(jQuery.browser.msie && jQuery.browser.version < 7){
            if($('iframe.bg_iframe').length == 0){
                $('body').append('<iframe class="bg_iframe" scrolling="no" frameborder="0" style="position: absolute;"></iframe>');
            }
        }
        if(modal && $('div.modaldiv').length == 0){
            var h = $(document).height();
            $('body').append('<div class="modaldiv" style="height:'+h+'px"></div>');
        }
        if(url){
            $.get(url,{ajax:'true','_t':Math.random()}, function(data) {
                $('#meiu_float_bubble div.bubble_container').html(data);
                Mui.moveToBeside(obj,'#meiu_float_bubble');
            });
            $('#meiu_float_bubble div.bubble_container').html('<div class="loading">Loading...</div>');
            $('#meiu_float_bubble').show();
            Mui.moveToBeside(obj,'#meiu_float_bubble');
        }else{
            $('#meiu_float_bubble').show();
            Mui.moveToBeside(obj,'#meiu_float_bubble');
        }
    },
    resize: function(w,h){
        $('#meiu_float_bubble').width(w);
        if(h){
            $('#meiu_float_bubble').height(h);
        }
    },
    setData : function(obj,data,modal){
        if( $('#meiu_float_bubble').length == 0 ){
            $('body').prepend('<div id="meiu_float_bubble"><div class="arrow"></div><div class="bubble_container"></div></div>');
        }
        $('#meiu_float_bubble div.bubble_container').html(data);
        this.show(false,modal);
    },
    close : function(){
        $('#meiu_float_bubble').hide();
        if(jQuery.browser.msie && jQuery.browser.version < 7){
            $('iframe').remove('.bg_iframe');
        }
        $('div').remove('.modaldiv');
        this.callback = null;
        if(this.onClose){
            this.onClose()
            this.onClose = null;
        }
    }
};

Mui.form = {
    send : function(formid){
        $('#'+formid).unbind('submit').submit(function(){
            var subbtn = $('#'+formid).find('input[type=submit]');
            subbtn.attr('disabled','disabled').addClass('btnloading');
            $.post($('#'+formid).attr('action'),$('#'+formid).serializeArray(),function(data) {
                if(data.ret){
                    resulthtml = '<div class="success">'+data.html+'</div>';
                }else{
                    resulthtml = '<div class="failed">'+data.html+'</div>';
                }
                Mui.form.showResult(resulthtml,formid);
                subbtn.removeAttr('disabled').removeClass('btnloading');
            },'json');
        });
    },
    sendPop : function(formid){
        $('#'+formid).unbind('submit').submit(function(){
            var subbtn = $('#'+formid).find('input[type=submit]');
            subbtn.attr('disabled','disabled').addClass('btnloading');
            $.post($('#'+formid).attr('action'),$('#'+formid).serializeArray(),function(data) {
                Mui.box.setData(data.html);
                if(data.ret){
                    $('#meiu_float_box .box_container').addClass('success');
                }else{
                    $('#meiu_float_box .box_container').addClass('failed');
                }
                subbtn.removeAttr('disabled').removeClass('btnloading');
            },'json');
        });
    },
    sendAuto : function(formid){
        $('#'+formid).unbind('submit').submit(function(){
            var subbtn = $('#'+formid).find('input[type=submit]');
            subbtn.attr('disabled','disabled').addClass('btnloading');
            $.post($('#'+formid).attr('action'),$('#'+formid).serializeArray(),function(data) {
                if(data.ret){
                    $('#'+formid).parent().find('.meiu_notice_div').remove();
                    if(Mui.box.callback){
                        Mui.box.setData(data.html.replace(/<script(.|\s)*?\/script(\s)*>/gi,""));
                        Mui.box.callback();
                    }else{
                        Mui.box.setData(data.html);
                    }
                    $('#meiu_float_box .box_container').addClass('success');
                }else{
                    Mui.form.showResult('<div class="failed">'+data.html+'</div>',formid);
                }
                subbtn.removeAttr('disabled').removeClass('btnloading');
            },'json');
        });
    },
    showResult : function(ret,formid){
        var m_notice = $('#'+formid).parent().find('.meiu_notice_div');
        if( ret != '' ){
            if( m_notice.length == 0 && formid != '' ){
                $('#'+formid).before('<div class="meiu_notice_div">'+ret+'</div>');
            }else{
                m_notice.html(ret);
            }
            //m_notice.css({display:'block'});
            $('#'+formid).parent().find('.meiu_notice_div').hide().fadeIn();
        }else{
            if( m_notice.length > 0 ){
                m_notice.css({display:'none'});
            }
        }
    }
};

function drop_select(je){
    $(je).find('li').each(function(i){
        if($(this).hasClass('current')){
            $(je).find('.selected').append($(this).html());
            $(je).find('.selected').prepend('<div class="arrow sprite"></div>');
        }
    });
    var optlist = $(je).find('.optlist');
    optlist.show();
    var width = optlist.width();
    optlist.hide();
    $(je).find('.selectlist').width(width);
    optlist.width(width);

    $(je).hover(function(){
        $(je).find('.optlist').show();
        $(je).css('zIndex','200');
        },function(){
        $(je).find('.optlist').hide();
        $(je).css('zIndex','100');
    });
}

function setMask(id,state){
    var oldEl = $('#'+id);
    if(oldEl.length == 0){
        return;
    }
    var val=oldEl.val();
    var cla=oldEl.attr('class');
    var name=oldEl.attr('name');
    var sibling = oldEl.next();
    var newInput = document.createElement('input');
    
    $(newInput).val(val);
    $(newInput).attr('id',id);
    $(newInput).attr('class',cla);
    $(newInput).attr('name',name);
    if (state == true)
        $(newInput).attr('type','text');
    else
        $(newInput).attr('type','password');
    
    oldEl.remove();
    sibling.before($(newInput));
}

function page_setting(t,num){
    var cookie_name = 'Mpic_pageset_'+t;
    $.cookie(cookie_name,num,{expires: 7, path: '/'});
    window.location.reload();
}

function sort_setting(t,sort){
    var cookie_name = 'Mpic_sortset_'+t;
    $.cookie(cookie_name,sort,{expires: 7, path: '/'});
    window.location.reload();
}

function reply_comment(je,url){
    var btn = $(je);
    var parent = $(je).parent();
    if(parent.find('form').length == 0){
        $.get(url,{ajax:'true','_t':Math.random()}, function(data) {
            parent.append(data);
            parent.find('input[name=cancel]').click(function(){
                parent.find('form').hide();
            });
            parent.find('form').submit(function(){
                var postform = $(this);
                $.post(postform.attr('action'),postform.serializeArray(),function(data) {
                    if(data.ret){
                        var reply_p = postform.parent().parent().parent();
                        if(reply_p.hasClass('sub')){
                            reply_p.after(data.html);
                        }else{
                            postform.parent().after(data.html);
                        }
                    
                        postform.remove();
                    }else{
                        notice_div = postform.find('.form_notice_div');
                        if( notice_div.length == 0 ){
                            postform.prepend('<div class="form_notice_div">'+data.html+'</div>');
                        }else{
                            notice_div.html(data.html);
                        }
                        postform.find('.form_notice_div').css({display:'block'});
                    }
                },'json');
            });
        },'html');
    }else{
        parent.find('form').show();
    }
}

function reload_comments(url){
    $.get(url,{ajax:'true','_t':Math.random()}, function(data) {
        if(data){
            setTimeout(function(){
                Mui.box.close();
            },500);
            $('.comment_list').html(data);
        }
    },'html');
}

function load_comments(url){
    $('.more_comments').html('Loading...');
    
    $.get(url,{ajax:'true','_t':Math.random()}, function(data) {
        if(data){
            $('.more_comments').remove();
            $('.comment_list').append(data);
        }
    },'html');
}

function switch_div(o,d){
    if(o.checked){
        $("#"+d).show();
    }else{
        $("#"+d).hide();
    }
}

function toggle_tree(o){
    var li=$(o).parent();
    var deep=li.attr('deep');
    var nextObjs = li.nextUntil('li[deep='+deep+']');
    if($(o).hasClass('closed')){
        nextObjs.each(function(i){
            if( $(this).attr('deep') == parseInt(deep)+1 ){
                $(this).show();
            }
        });
        $(o).removeClass('closed').addClass('opened');
    }else{
        nextObjs.each(function(i){
            if( $(this).attr('deep') > deep ){
                $(this).hide();
                $(this).find('span').removeClass('opened').addClass('closed');
            }
        });
        $(o).removeClass('opened').addClass('closed');
    }
}

function reload_captcha(o){
	var url=$(o).attr('src');
	if(url.indexOf("?")>=0){
		url = url+'&_='+Math.random();
	}else{
		url = url+'?_='+Math.random();
	}
	$(o).attr('src',url);
}

$(function(){
    //press esc to close float div
    $(document).bind('keypress',
        function(e){
            if(e.keyCode == 27){
                Mui.box.close();
                Mui.bubble.close();
            }
        }
    );
});