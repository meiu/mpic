$(function(){
    $('.gallary_item:not(.idx)').hover(function(){
        $('.gallary_wrap .gallary_item').removeClass('sel_on');
        $(this).addClass('sel_on');
        var obj = this;
        $(document).unbind('mousedown').bind("mousedown",function(e){
            var popup = obj;
            var e = e || window.event;
            var target = e.target || e.srcElement;
            while (target != document && target != popup 
                && target != $('#meiu_float_bubble').get(0) 
                && target != $('#meiu_float_box').get(0) 
                && target != $('.modaldiv').get(0)
                && target != $('.clipboard').get(0)) {
                target = target.parentNode;
            }
            if (target == document) {
                $('.gallary_wrap .gallary_item').removeClass('sel_on');
            }
        });
    });
    
    $('.inline_edit').hover(function(){
        $(this).addClass('editbg');
    },function(){
        $(this).removeClass('editbg');
    })
});

Madmin={};
Madmin.check_all = function(je,check){
    if(check){
        $(je).attr('checked','checked');
    }else{
        $(je).removeAttr('checked');
    }
}
Madmin.checked_action = function(je,action_url){
    var check_vals = $(je+':checked');
    $.post(action_url,check_vals.serializeArray(),function(data) {
        Mui.box.setData(data,true);
    },'html');
}
Madmin.rename = function(obj,url){
    var info = $(obj).parent();
    var id = $(obj).attr('nid');
    
    var info_txt = info.text();
    info.html('<input id="input_id_'+id+'" type="text" value="'+info_txt.replace(/\"/g, '&#34;')+'" class="inputstyle" />');
    var input = $('#input_id_'+id);
    input.focus();
    input.select();
    input.blur(
        function(){
            if(this.value != info_txt && this.value!=''){
                $.post(url,
                   {name:this.value},
                   function(data){
                        if(data.ret){
                            $(obj).html(data.html);
                            info.empty().append(obj);
                        }else{
                            info.empty().append(obj);
                        }
                    },
                'json');
            }else{
                $(obj).html(info_txt);
                info.empty().append(obj);
            }
        }
    );
    input.unbind('keypress').bind('keypress',
        function(e){
            if(e.keyCode == 13){
                input.blur();
            }
        }
    );
}

Madmin.inline_edit = function(je,url){
    var info = $(je);
    var parent = $(je).parent();
    $.get(url,{ajax:'true','_t':Math.random()}, function(data) {
        info.hide();
        if(parent.find('form').length == 0){
            parent.append(data);
        }
        $(parent).find('input[name=cancel]').click(function(){
            $(parent).find('form').remove();
            info.show();
        });
        $(parent).find('form').submit(function(){
            var postform = $(this);
            $.post(postform.attr('action'),postform.serializeArray(),function(data) {
                if(data.ret){
                    info.html(data.html+' <span class="i_editinfo sprite"></span>');
                    $(parent).find('form').remove();
                    info.show();
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
}

Madmin.addEditNav = function(o){
    o.before('<tr class="hover">\
        <td></td>\
        <td><input type="text" class="inputstyle iptw0" name="sortnew[]" value="" /></td>\
        <td><input type="text" class="inputstyle iptw1" name="namenew[]" value="" /></td>\
        <td><input type="text" class="inputstyle iptw2" name="urlnew[]" value="" /></td>\
        <td colspan="2"></td></tr>');
}

function admin_reply_comment(je,url){
    var btn = $(je);
    var parent = $(je).parent().parent();
    if(parent.next('tr.form').length == 0){
        $.get(url,{ajax:'true','_t':Math.random()}, function(data) {
            parent.after('<tr class="form"><td colspan="5">'+data+'</td></tr>');
            parent.next('tr.form').find('input[name=cancel]').click(function(){
                parent.next('tr.form').hide();
            });
            parent.next('tr.form').find('form').submit(function(){
                var postform = $(this);
                $.post(postform.attr('action'),postform.serializeArray(),function(data) {
                    if(data.ret){
                        window.location.reload();
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
        parent.next('tr.form').show();
    }
}
