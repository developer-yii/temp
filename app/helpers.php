<?php
if (!function_exists('pre')) {
    function pre($text)
    {
        print "<pre>";
        print_r($text);
        exit();
    }
}
?>