<?php
/******************************************************************************/
//                       soft-solution.ru team                                //
/******************************************************************************/

    function routes_otvety(){

        $routes[] = array(
                            '_uri'  => '/^otvety\/read([0-9]+).html$/i',
                            'do'    => 'read',
                            1       => 'id'
                         );

	$routes[] = array(
                            '_uri'  => '/^otvety\/edit([0-9]+).html$/i',
                            'do'    => 'edit',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/delete([0-9]+).html$/i',
                            'do'    => 'delete',
                            1       => 'id'
                         );

	$routes[] = array(
                            '_uri'  => '/^otvety\/add([0-9]+).html$/i',
                            'do'    => 'add',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/add.html$/i',
                            'do'    => 'add'
                         );


        $routes[] = array(
                            '_uri'  => '/^otvety\/tags$/i',
                            'do'    => 'tags',
                            'target'=> 'tags'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/categories$/i',
                            'do'    => 'categories',
                            'target'=> 'categories'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/categories\/page-([0-9]+)$/i',
                            'do'    => 'categories',
                            'target'=> 'categories',
                            1       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/unanswered$/i',
                            'do'    => 'view',
                            'target'=> 'unanswered'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/unanswered\/page-([0-9]+)$/i',
                            'do'    => 'view',
                            'target'=> 'unanswered',
                            1       => 'page'
                         );


        $routes[] = array(
                            '_uri'  => '/^otvety\/(.*)\/hot\/page-([0-9]+)$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            2       => 'page',
                            'filter'=>'hot'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/(.*)\/hot$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            'filter'=>'hot'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/(.*)\/votes\/page-([0-9]+)$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            2       => 'page',
                            'filter'=>'votes'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/(.*)\/votes$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            'filter'=>'votes'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/(.+)\/hits\/page\-([0-9]+)$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            2       => 'page',
                            'filter'=>'hits'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/(.+)\/hits$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            'filter'=>'hits'
                         );


        $routes[] = array(
                            '_uri'  => '/^otvety\/page-([0-9]+)$/i',
                            'do'    => 'categories',
                            'target'=> 'categories',
                            1       => 'page'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/(.+)\/page-([0-9]+)$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            2       => 'page',
                            'filter'=>'new'
                         );

        $routes[] = array(
                            '_uri'  => '/^otvety\/(.+)$/i',
                            'do'    => 'view',
                            'target'=> 'category',
                            1       => 'seolink',
                            'filter'=>'new'
                         );
        //роутер переписан
        $routes[] = array(
                            '_uri'  => '/^otvety$/i',
                            'do'    => 'categories',
                            'target'=> 'categories'
                         );

        return $routes;

    }

?>
