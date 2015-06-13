<?php

// ========================================================================== //

    function info_component_otvety(){

        //Описание компонента

        $_component['title']        = 'Ответы';                                 //название
        $_component['description']  = 'Ответы для InstantCMS';                  //описание
        $_component['link']         = 'otvety';                                 //ссылка (идентификатор)
        $_component['author']       = 'Soft-Solution.ru';                       //автор
        $_component['internal']     = '0';                                      //внутренний (только для админки)? 1-Да, 0-Нет
        $_component['version']      = '1.05';                                   //текущая версия

        //Настройки по-умолчанию
        $_component['config'] = array(

            'guest_enabled' => '1',
            'user_link' => '1',
            'user_avatar' => '1',
            'showrss' => '1',
            'publish' => '0',
            'img_on' => '1',
            'perpage' => '15',
            'perpage_cat' => '15',
            'img_cat' => '1',
            'thumb1' => '64',
            'thumb2' => '200',
            'thumbsqr' => '1',
            'sorttagby' => 'tag',
            'minfreq' => '0',
            'minlen' => '3',
            'maxtags' => '100',
            'tagcol' => '3',
            'send_notice' => '1'
        );

        return $_component;

    }

// ========================================================================== //

    function install_component_otvety(){

        $inCore     = cmsCore::getInstance();       //подключаем ядро
        $inDB       = cmsDatabase::getInstance();   //подключаем базу данных
        $inConf     = cmsConfig::getInstance();

        include($_SERVER['DOCUMENT_ROOT'].'/includes/dbimport.inc.php');

        dbRunSQL($_SERVER['DOCUMENT_ROOT'].'/components/otvety/install.sql', $inConf->db_prefix);

        return true;

    }

// ========================================================================== //
    function upgrade_component_otvety(){


        return true;

    }

// ========================================================================== //

?>