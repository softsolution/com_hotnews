<?php

// ========================================================================== //

    function info_component_otvety(){

        //�������� ����������

        $_component['title']        = '������';                                 //��������
        $_component['description']  = '������ ��� InstantCMS';                  //��������
        $_component['link']         = 'otvety';                                 //������ (�������������)
        $_component['author']       = 'Soft-Solution.ru';                       //�����
        $_component['internal']     = '0';                                      //���������� (������ ��� �������)? 1-��, 0-���
        $_component['version']      = '1.05';                                   //������� ������

        //��������� ��-���������
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

        $inCore     = cmsCore::getInstance();       //���������� ����
        $inDB       = cmsDatabase::getInstance();   //���������� ���� ������
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