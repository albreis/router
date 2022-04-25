<?php
if(!function_exists('get_http_status_code')) {
  function get_http_status_code($url) {
    return substr(get_headers($url, true)[0], 9, 3);
  }
}