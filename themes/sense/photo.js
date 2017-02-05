function show_exif(o){
  var pos = $(o).offset();
  $('#meta_detail').css({'left':pos.left,'top':pos.top+$(o).height()});
  $('#meta_detail').show();
  $(document).bind("mousedown",function(e){
    var popup = $('#meta_detail').get(0);
    var e = e || window.event;
    var target = e.target || e.srcElement;
    while (target != document && target != popup) {
    target = target.parentNode;
    }
    if (target == document) {
    close_exif();
    }
  });
}

function close_exif(){
  $('#meta_detail').hide();
  $(document).unbind("mousedown");
}

var ImgContol = {
  lang : {
      next_photo:'Next',
      prev_photo:'Previous'
  },
  photos : new Array(),
  ajax : null,
  last_rank: 0,
  current_rank: 0,
  album_id : null,
  prev : null,
  next : null,
  init : function(){
    var imgareaObj = $('#imgarea');
    
    imgareaObj.bind('mousemove mouseover',function(e){
        var ps = imgareaObj.offset();
        var ps_width = imgareaObj.width();
        var nxt = e.clientX > (ps.left+ps_width/2);
        var curClass = nxt ? 'next_cur' : 'pre_cur';
        imgareaObj.attr('class',curClass);
        $('#imgarea img').attr('title',(nxt ? ImgContol.lang.next_photo : ImgContol.lang.prev_photo));
    });
    
    imgareaObj.click(function(e){
        var ps = imgareaObj.offset();
        var ps_width = imgareaObj.width();
        var nxt = e.clientX > (ps.left+ps_width/2);
        if(nxt){
          if(ImgContol.next)
            window.location.href = ImgContol.next+'#pic_block';
        }else{
          if(ImgContol.prev)
            window.location.href = ImgContol.prev+'#pic_block';
        }
    });

   $('#photo_body img').load(function(){
       $('#photo_body').css('background','none');
   });

    $(document).bind('keydown',
         function(e){
             if (e.altKey) return true;
             var target = e.target;
             if (target && target.type) return true;
             switch(e.keyCode) {
                 case 63235: case 39: 
                  if(ImgContol.next)
                    window.location.href = ImgContol.next+'#pic_block';
                  break;
                 case 63234: case 37:
                  if(ImgContol.prev)
                    window.location.href = ImgContol.prev+'#pic_block';
                  break;
             }
         }
     );
  },
  resize_img: function(w,h,x,y){
    if(isNaN(w) || w == 0 || isNaN(h) || h == 0){
        return ; //如果丢失图片尺寸的话，直接返回
    }
    var w_original=w, h_original=h;
    if (w > x) {
      h = h * (x / w);
      w = x;
    }
    if (h > y) {
        w = w_original * (y / h_original);
        h = y;
    }
    $('#photo_body img.photo').attr({width:w,height:h});
  },
  nav_prev: function(){
    if(!this.prev){
      return ;
    }
    if(this.current_rank == 1){
      var prev_rank = this.last_rank;
      this.current_rank = 0;
    }else if(this.current_rank == 0){
      var prev_rank = this.last_rank-1;
      this.current_rank = this.last_rank;
    }else{
      var prev_rank = this.current_rank-2;
      this.current_rank = this.current_rank-1;
    }
    
    if(this.photos[prev_rank]){
      this.prev_append(this.photos[prev_rank]);
    }else{
      $.post(this.ajax,{'rank':prev_rank,'album_id':this.album_id},function(data){
          ImgContol.photos[prev_rank] = data;
          ImgContol.prev_append(ImgContol.photos[prev_rank]);
      },'json');
    }
  },
  prev_append: function(d){
      $('.pic_nav .pic_nav_body ul').prepend(this.get_html(d));
      $('.pic_nav .pic_nav_body ul li:last').remove();
  },
  get_html: function(d){
      return '<li><a href="'+d.url+'#pic_block'+'">\
                <span class="thumbnail_container">\
                  <img style="'+d.style+'" src="'+d.thumb+'">\
                </span>\
              </a>\
              </li>';
  },
  nav_next: function(){
    if(!this.next){
      return ;
    }
    if(this.current_rank == this.last_rank-1){
      var next_rank = 0;
      this.current_rank = this.last_rank;
    }else if(this.current_rank == this.last_rank){
      var next_rank = 1;
      this.current_rank = 0;
    }else{
      var next_rank = this.current_rank+2;
      this.current_rank = this.current_rank+1;
    }
    
    if(this.photos[next_rank]){
      this.next_append(this.photos[next_rank]);
    }else{
      $.post(this.ajax,{'rank':next_rank,'album_id':this.album_id},function(data){
          ImgContol.photos[next_rank] = data;
          ImgContol.next_append(ImgContol.photos[next_rank]);
      },'json');
    }
  },
  next_append: function(d){
    $('.pic_nav .pic_nav_body ul').append(this.get_html(d));
    $('.pic_nav .pic_nav_body ul li:first').remove();
  }
};