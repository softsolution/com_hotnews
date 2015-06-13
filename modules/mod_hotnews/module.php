<?php
/* soft-solution.ru created by AlexG */

function mod_hotnews($module_id){
        $inCore = cmsCore::getInstance();
        $inDB = cmsDatabase::getInstance();
		$cfg = $inCore->loadModuleConfig($module_id);

		//if (!isset($cfg['showtype'])) { $cfg['showtype'] = 'full'; }
		//if (!isset($cfg['showmore'])) { $cfg['showmore'] = 1; }

/*
        $sql = "SELECT f.*, a.id as album_id, a.title as album
                        FROM cms_photo_files f
                        LEFT JOIN cms_photo_albums a ON a.id = f.album_id
                        WHERE f.published = 1 ".$catsql."
                        ORDER BY f.id DESC
                        LIMIT ".$cfg['shownum'];

        $result = $inDB->query($sql);
        $is_photo = false;

        if ($inDB->num_rows($result)) {
            $photos = array();
            $is_photo = true;

            while ($con = $inDB->fetch_assoc($result)) {
                $photos[] = $con;
            }
        }
*/
        $smarty = $inCore->initSmarty('modules', 'mod_hotnews.tpl');
        $smarty->assign('item', $item);
        $smarty->assign('cfg', $cfg);
        $smarty->display('mod_hotnews.tpl');

        return true;

}
?>