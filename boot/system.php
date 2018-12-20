<?php namespace Eckinox;

// Define max upload size
$_nex_maxsize = ini_get('upload_max_filesize');
$_nex_type = substr($_nex_maxsize, -1);
$_nex_value = substr($_nex_maxsize, 0, -1);

//Transform into bytes
switch (strtoupper($_nex_type)) {
    case 'P': $_nex_value *= 1024;
    case 'T': $_nex_value *= 1024;
    case 'G': $_nex_value *= 1024;
    case 'M': $_nex_value *= 1024;
    case 'K': $_nex_value *= 1024;
        break;
}

// Define constant max upload size constant
define("NEX_MAX_UPLOAD_SIZE", $_nex_value);
unset($_nex_maxsize, $_nex_type, $_nex_value);
