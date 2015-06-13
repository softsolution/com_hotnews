<?php
function info_module_mod_hotnews(){
        $_module['title']         = 'Важная новость';
        $_module['name']          = 'Важная новость';
        $_module['description']   = 'Модуль отображает Важную новость и может управляться пользователем';
        $_module['link']          = 'mod_hotnews';
        $_module['position']      = 'maintop';
        $_module['author']        = 'soft-solution.ru';
        $_module['version']       = '1';

        $_module['config'] = array();

        return $_module;

    }

    function install_module_mod_hotnews(){

        return true;

    }

    function upgrade_module_mod_hotnews(){

        return true;

    }

?>