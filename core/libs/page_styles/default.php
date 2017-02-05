<?php

$pagination['start'] = '';
$pagination['end'] = ' <span class="count">('.lang('pageset_total','{total_page}').')</span>';

$pagination['first'] = '';
$pagination['pre'] = ' <span class="prev"><a href="{url}">&lt;'.lang('pageset_prev').'</a></span> ';
$pagination['next'] = ' <span class="next"><a href="{url}">'.lang('pageset_next').'&gt;</a></span> ';
$pagination['last'] = '';

$pagination['shownum'] = true;
$pagination['num'] = ' <a href="{url}">{num}</a> ';
$pagination['current'] = ' <span class="thispage">{num}</span> ';
$pagination['ellipsis'] = ' ... ';

$pagination['none'] = '';