CREATE TABLE meu_albums ( 
    id integer NOT NULL primary key, 
    name varchar(150) NOT NULL, 
    cate_id int(4) NOT NULL DEFAULT 0,
    cover_id int(11) NOT NULL DEFAULT 0, 
    cover_ext varchar(20) DEFAULT NULL, 
    comments_num int(11) NOT NULL DEFAULT 0, 
    photos_num int(11) NOT NULL DEFAULT 0, 
    create_time int(11) NOT NULL DEFAULT 0, 
    up_time int(11) NOT NULL DEFAULT 0, 
    tags varchar(255) DEFAULT NULL, 
    priv_type tinyint(1) NOT NULL DEFAULT 0, 
    priv_pass varchar(100) DEFAULT NULL, 
    priv_question varchar(255) DEFAULT NULL, 
    priv_answer varchar(255) DEFAULT NULL, 
    desc longtext, deleted tinyint(1) NOT NULL DEFAULT 0 , 
    enable_comment tinyint(1) default 1);
CREATE TABLE meu_photos ( 
    id integer NOT NULL primary key, 
    album_id int(11) NOT NULL, 
    cate_id int(4) NOT NULL DEFAULT 0,
    name varchar(100) NOT NULL, 
    thumb varchar(255) NOT NULL, 
    path varchar(255) NOT NULL, 
    width int(11) NOT NULL DEFAULT 0, 
    height int(11) NOT NULL DEFAULT 0, 
    hits int(11) NOT NULL DEFAULT 0, 
    comments_num int(11) NOT NULL DEFAULT 0, 
    create_time int(11) NOT NULL DEFAULT 0, 
    taken_time int(11) NOT NULL DEFAULT 0, 
    taken_y int(4) NOT NULL DEFAULT 0, 
    taken_m int(2) NOT NULL DEFAULT 0, 
    taken_d int(2) NOT NULL DEFAULT 0, 
    create_y int(4) NOT NULL DEFAULT 0, 
    create_m int(2) NOT NULL DEFAULT 0, 
    create_d int(2) NOT NULL DEFAULT 0, 
    desc longtext, exif text, 
    tags varchar(255) DEFAULT NULL, 
    is_cover tinyint(1) NOT NULL DEFAULT 0, 
    deleted tinyint(1) NOT NULL DEFAULT 0 , 
    type tinyint(1) default 0);
CREATE TABLE meu_users (
      id integer NOT NULL primary key,
      user_name varchar(50) NOT NULL,
      user_pass varchar(50) NOT NULL,
      user_nicename varchar(100) NOT NULL,
      create_time int(11) NOT NULL DEFAULT 0,
      user_status tinyint(1) NOT NULL DEFAULT 0
    );
CREATE TABLE meu_usermeta (
      umeta_id integer NOT NULL primary key,
      userid int(11) NOT NULL,
      meta_key varchar(255) NOT NULL,
      meta_value longtext
    );
CREATE TABLE meu_tag_rel (
      tag_id int(11) NOT NULL,
      rel_id int(11) NOT NULL
    );
CREATE TABLE meu_tags (
      id integer NOT NULL primary key,
      type tinyint(1) NOT NULL DEFAULT 1,
      name varchar(100) NOT NULL,
      count int(11) NOT NULL DEFAULT 0
    );
CREATE TABLE meu_setting (
      name varchar(50) NOT NULL,
      value longtext NOT NULL
    );
CREATE TABLE meu_plugins (
        plugin_id varchar(32) NOT NULL,
          plugin_name varchar(200) NOT NULL,
          description varchar(255) NOT NULL,
          plugin_config longtext,
          local_ver varchar(20) NOT NULL,
          remote_ver varchar(20) DEFAULT NULL,
          available varchar(255) NOT NULL DEFAULT 'false',
          author_name varchar(100) DEFAULT NULL,
          author_url varchar(100) DEFAULT NULL,
          author_email varchar(100) DEFAULT NULL
    );
CREATE TABLE meu_photometa (
        pmeta_id integer NOT NULL primary key,
        photo_id int(11) NOT NULL,
        meta_key varchar(255) NOT NULL,
        meta_value longtext
    );
CREATE TABLE meu_comments (
        id integer NOT NULL primary key,
        type int(11) NOT NULL DEFAULT 1,
        ref_id int(11) NOT NULL,
        quote_id int(11) NOT NULL DEFAULT 0,
        email varchar(200) NOT NULL,
        author varchar(100) NOT NULL,
        reply_author varchar(100) DEFAULT NULL,
        author_ip varchar(50) NOT NULL,
        content text NOT NULL,
        pid int(11) NOT NULL DEFAULT 0,
        post_time int(11) NOT NULL DEFAULT 0,
        status tinyint(1) NOT NULL DEFAULT 1
    );
CREATE TABLE meu_commentmeta (
        meta_id integer NOT NULL primary key,
        comment_id int(11) NOT NULL DEFAULT 0,
        meta_key varchar(255) DEFAULT NULL,
        meta_value longtext
    );
CREATE TABLE meu_albummeta (
        ameta_id integer NOT NULL primary key,
        album_id int(11) NOT NULL,
        meta_key varchar(255) NOT NULL,
        meta_value longtext
    );
CREATE TABLE meu_cate (
        id integer NOT NULL primary key,
        par_id int(4) NOT NULL DEFAULT 0,
        name varchar(100) NOT NULL,
        cate_path varchar(255) DEFAULT NULL,
        sort int(4) NOT NULL DEFAULT 0
    );
    
CREATE TABLE meu_nav (
        id integer NOT NULL primary key ,
        type tinyint(1) NOT NULL DEFAULT 1,
        name varchar(50) NOT NULL ,
        url varchar(200) NOT NULL ,
        sort smallint(4) NOT NULL  DEFAULT 100,
        enable tinyint(1) NOT NULL DEFAULT 1
    );

CREATE INDEX um_meta_key on meu_usermeta (meta_key);
CREATE INDEX um_userid on meu_usermeta (userid);
CREATE INDEX t_name on meu_tags (name);
CREATE INDEX t_type on meu_tags (type);
CREATE INDEX pm_meta_key on meu_photometa (meta_key);
CREATE INDEX pm_photo_id on meu_photometa (photo_id);
CREATE INDEX c_pid on meu_comments (pid);
CREATE INDEX c_ref_id on meu_comments (ref_id);
CREATE INDEX cm_meta_key on meu_commentmeta (meta_key);
CREATE INDEX cm_comment_id on meu_commentmeta (comment_id);
CREATE INDEX am_meta_key on meu_albummeta (meta_key);
CREATE INDEX am_album_id on meu_albummeta (album_id);
CREATE INDEX cg_par_id on meu_cate (par_id);
CREATE INDEX a_cate_id on meu_albums (cate_id);
CREATE INDEX p_album_id on meu_photos (album_id);
CREATE INDEX p_cate_id on meu_photos (cate_id);