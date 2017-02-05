<?php 
/**
 * $Id: rss.class.php 161 2011-05-16 14:35:41Z lingter $
 *
 * Rss writer
 *
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class rss_cla
{
    var $title;
    var $link;
    var $description;
    var $language = "zh-cn";
    var $pubDate;
    var $items;
    var $tags;
 
    function rss_cla() {
        $this->items = array();
        $this->tags  = array();
    }
 
    function add_item($item) {
        $this->items[] = $item;
    }
 
    function set_pubdate($when) {
        if(is_numeric($when))
            $this->pubDate = date("D, d M Y H:i:s O", $when) ;
        else
            $this->pubDate = date("D, d M Y H:i:s O", strtotime($when));
    }
 
    function get_pubdate() {
        if(empty($this->pubDate))
            return date("D, d M Y H:i:s O");
        else
            return $this->pubDate;
    }
 
    function add_tag($tag, $value) {
        $this->tags[$tag] = $value;
    }
 
    function out() {
        $out  = $this->header();
        $out .= "<channel>\n";
        $out .= "<title>" . $this->title . "</title>\n";
        $out .= "<link>" . $this->link . "</link>\n";
        $out .= "<description>" . $this->description . "</description>\n";
        $out .= "<language>" . $this->language . "</language>\n";
        $out .= "<pubDate>" . $this->get_pubdate() . "</pubDate>\n";
 
        foreach($this->tags as $key => $val)
            $out .= "<$key>$val</$key>\n";
        foreach($this->items as $item)
            $out .= $item->out();
 
        $out .= "</channel>\n";
        $out .= $this->footer();
 
        $out = str_replace("&", "&amp;", $out);
        return $out;
    }
    
    function serve($contentType = "application/xml") {
        $xml = $this->out();
        header("Content-type: $contentType");
        echo $xml;
    }
 
    function header() {
        $out  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $out .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
        return $out;
    }
 
    function footer() {
        return '</rss>';
    }
}
 
class RSSItem
{
    var $title;
    var $link;
    var $description;
    var $pubDate;
    var $guid;
    var $tags;
    var $attachment;
    var $length;
    var $mimetype;
 
    function RSSItem() {
        $this->tags = array();
    }
 
    function set_pubdate($when) {
        if(is_numeric($when))
            $this->pubDate = date("D, d M Y H:i:s O", $when);
        else
            $this->pubDate = date("D, d M Y H:i:s O", strtotime($when));
    }
 
    function get_pubdate() {
        if(empty($this->pubDate))
            return date("D, d M Y H:i:s O");
        else
            return $this->pubDate;
    }
 
    function add_tag($tag, $value) {
        $this->tags[$tag] = $value;
    }
 
    function out() {
        $out = "<item>\n";
        $out .= "<title>" . $this->title . "</title>\n";
        $out .= "<link>" . $this->link . "</link>\n";
        $out .= "<description>" . $this->description . "</description>\n";
        $out .= "<pubDate>" . $this->get_pubdate() . "</pubDate>\n";
 
        if($this->attachment != "")
            $out .= "<enclosure url='{$this->attachment}' length='{$this->length}' type='{$this->mimetype}' />";
 
        if(empty($this->guid)) $this->guid = $this->link;
        $out .= "<guid>" . $this->guid . "</guid>\n";
 
        foreach($this->tags as $key => $val) $out .= "<$key>$val</$key\n>";
        $out .= "</item>\n";
        return $out;
    }
 
    function enclosure($url, $mimetype, $length) {
        $this->attachment = $url;
        $this->mimetype   = $mimetype;
        $this->length     = $length;
    }
}