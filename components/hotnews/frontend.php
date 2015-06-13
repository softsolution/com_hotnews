<?php

/* * *************************************************************************** */
//                       soft-solution.ru team                                //
/* * *************************************************************************** */
if (!defined('VALID_CMS')) {
    die('ACCESS DENIED');
}

function otvety() {

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();

    $inCore->loadModel('otvety');
    $model = new cms_model_otvety();

    global $_LANG;

    $cfg = $inCore->loadComponentConfig('otvety');

    $inCore->loadLib('karma');
    $inCore->loadLib("tags");

    //Проверяем включени ли компонент
    if(!$cfg['component_enabled']) { cmsCore::error404(); }

    if (!isset($cfg['guest_enabled'])) { $cfg['guest_enabled'] = 1; }
    if (!isset($cfg['user_link'])) { $cfg['user_link'] = 1; }
    if (!isset($cfg['publish'])) { $cfg['publish'] = 0; }

    $id = $inCore->request('id', 'int', 0);

    $user_id = $inUser->id;
    $is_admin = $inCore->userIsAdmin($inUser->id);

    //настройки по умолчанию
    $do = $inCore->request('do', 'str', 'categories');
    $target = $inCore->request('target', 'str', 'categories');
    $seolink = $inCore->request('seolink', 'str', '');
    $filter = $inCore->request('filter', 'str', 'new');
    $perpage = $cfg['perpage'] ? $cfg['perpage'] : 10;
    $page = $inCore->request('page', 'int', 1);

// ===================================================================================================== //
// ============ главная страница компонента, просмотр вопросов + сортировка ============================ //
// ===================================================================================================== //
    if ($do == 'view') {

        $model->orderBy('i.pubdate', 'DESC');
        $model->limitPage($page, $perpage);

        $model->where('cat.published = 1');
        $is_cats = true;// TODO сделать в зависимости от админа для фронта

        if($target=='unanswered'){
            $page_title = $_LANG['ALL_UNANSWERED'];
            $model->where('i.answers=0');
            $category = $target.'/';
            $rss_target = $target;
            }

        if ($target=='category') {
            $cat = $inDB->get_fields('cms_otvety_cats', "seolink='$seolink'", '*');
            if(!$cat){cmsCore::error404();}

            $model->where("cat.id = '{$cat['id']}'");
            $page_title = $cat['title'];
            $category = $seolink.'/';
            $rss_target = $cat['id'];

                //обрабатываем фильтры
                if($filter=='new'){}
                if($filter=='hot')   $model->orderBy('i.last_answer', 'DESC');
                if($filter=='votes') $model->orderBy('rating', 'DESC');
                if($filter=='hits')  $model->orderBy('i.hits', 'DESC');
                if($filter!='new')   $category .=$filter.'/';
            }

        $is_moder = false;// TODO для админов надо считать по другому
        //количество записей с указанными условиями
        $records = $model->getQuestionCount($is_moder);

        $questions = $model->getQuestions(false, true, true, $is_cats);

        if ($questions != '') {
            $is_questions = true;
        }

        $page_title = $page_title ? $page_title : $_LANG['ALL_QUESTION'];
        $category   = $category ? $category : '';
        $rss_target = $rss_target ? $rss_target : 'all';

        $inPage->setTitle($page_title);
        $inPage->setDescription($page_title);
        $inPage->addPathway($page_title, '/otvety/'.$category);

        $smarty = $inCore->initSmarty('components', 'com_otvety_view.tpl');
        $smarty->assign('page_title', $page_title);
        $smarty->assign('target', $target);
        $smarty->assign('seolink', $seolink);
        $smarty->assign('filter', $filter);
        $smarty->assign('rss_target', $rss_target);
        $smarty->assign('questions', $questions);
        $smarty->assign('cfg', $cfg);
        $smarty->assign('is_questions', $is_questions);
        $smarty->assign('is_user', $inUser->id);
        $smarty->assign('pagebar', cmsPage::getPagebar($records, $page, $perpage, '/otvety/'.$category.'page-%page%', array('id' => $id)));
        $smarty->display('com_otvety_view.tpl');
    }

/* ==================================================================================================== */
/* ========================== КАТЕГОРИИ ВОПРОСОВ ====================================================== */
/* ==================================================================================================== */

    if ($do == 'categories') {

        $perpage_cat = $cfg['perpage_cat'] ? $cfg['perpage_cat'] : 15;
        $model->limitPage($page, $perpage_cat);

        $is_moder = false;// TODO для админов надо считать по другому
        //количество записей с указанными условиями
        $records = $model->getCategoryCount($is_moder);

        $categories = $model->getCategories(false);

        if ($categories != '') {
            $is_category =true;
        }

        $page_title = $_LANG['QUESTION_CATEGORY'];
        $rss_target = 'all';

        $inPage->setTitle($page_title);
        $inPage->setDescription($page_title);
        $inPage->addPathway($page_title, '/otvety/categories/');

        $smarty = $inCore->initSmarty('components', 'com_otvety_view.tpl');
        $smarty->assign('page_title', $page_title);
        $smarty->assign('target', $target);
        $smarty->assign('rss_target', $rss_target);
        $smarty->assign('categories', $categories);
        $smarty->assign('cfg', $cfg);
        $smarty->assign('is_category', $is_category);
        $smarty->assign('is_user', $inUser->id);
        $smarty->assign('pagebar', cmsPage::getPagebar($records, $page, $perpage_cat, '/otvety/categories/page-%page%', array('id' => $id)));
        $smarty->display('com_otvety_view.tpl');

    }

/* ==================================================================================================== */
/* ========================== МЕТКИ ВОПРОСОВ ========================================================== */
/* ==================================================================================================== */

    if ($do == 'tags') {

        if(!isset($cfg['sorttagby'])) { $cfg['sorttagby']='tag'; }
        if(!isset($cfg['minfreq'])) { $cfg['minfreq']=0; }
        if(!isset($cfg['minlen'])) { $cfg['minlen'] = 3; }
        if(!isset($cfg['maxtags'])) { $cfg['maxtags'] = 20; }
        if(!isset($cfg['tagcol'])) { $cfg['tagcol'] = 3; }

        $sql = "SELECT t.*, COUNT(t.tag) as num FROM cms_tags t WHERE target ='otvety' GROUP BY t.tag ";
	if ($cfg['sorttagby'] == 'tag') { $sql .= "\n"." ORDER BY tag ASC"; } else { $sql .= "\n"." ORDER BY num DESC"; }
        $sql .= " LIMIT ".$cfg['maxtags'];
        $result = $inDB->query($sql);

        $is_tags = false;

        if ($inDB->num_rows($result)) {
            $is_tags = true;

            $tags = array();
            $summary = 0;
            while ($tag = $inDB->fetch_assoc($result)) {
                if (strlen($tag['tag']) >= $cfg['minlen']) {
                    $next = sizeof($tags);
                    $tags[$next]['title'] = $tag['tag'];
                    $tags[$next]['num'] = $tag['num'];
                    $summary += $tag['num'];
                }
            }

            $tags_sel = array();

            foreach ($tags as $key => $value) {

                $tag = $tags[$key]['title'];
                $num = $tags[$key]['num'];

                if ($num >= $cfg['minfreq']) {

                    $next = sizeof($tags_sel);
                    $tags_sel[$next]['title'] = $tag;
                    $tags_sel[$next]['num'] = $num;

                }
            }
        }

        $page_title = $_LANG['QUESTION_TAGS'];
        $rss_target = 'all';

        $inPage->setTitle($page_title);
        $inPage->setDescription($page_title);
        $inPage->addPathway($page_title, '/otvety/tags');

        $smarty = $inCore->initSmarty('components', 'com_otvety_view.tpl');
        $smarty->assign('page_title', $page_title);
        $smarty->assign('target', $target);
        $smarty->assign('rss_target', $rss_target);
        $smarty->assign('tags', $tags_sel);
        $smarty->assign('cfg', $cfg);
        $smarty->assign('is_tags', $is_tags);
        $smarty->assign('is_user', $inUser->id);
        $smarty->display('com_otvety_view.tpl');

    }

/* ==================================================================================================== */
/* ========================== ПРОСМОТР ВОПРОСА ======================================================== */
/* ==================================================================================================== */

    if ($do == 'read') {

        if ($id) { $question = $model->getQuestion($id); }
        if (!$question){ cmsCore::error404(); }

        $inPage->setTitle($question['short_title']);
        $inPage->setDescription($question['short_title']);

        $inPage->addPathway($question['cat_title'], '/otvety/'.$question['cat_seolink']);
        $inPage->addPathway($question['short_title']);

        $is_author = ($user_id == $question['user_id']);

        if ($cfg['user_avatar']) {
            $question['avatar'] = usrLink2(usrImageNOdb2($question['user_id'], 'meddium', $question['imageurl'], $question['is_deleted']), $question['login'], $menuid);
        }

        if($question['moderator_id']>1) {
            $question['moderator_avatar'] = usrLink2(usrImageNOdb2($question['moderator_id'], 'meddium', $question['moder_imageurl'], $question['moder_is_deleted']), $question['moder_login'], $menuid);
        }

        $smarty = $inCore->initSmarty('components', 'com_otvety_read.tpl');
        $smarty->assign('question', $question);
        $smarty->assign('cfg', $cfg);
        $smarty->assign('labels', array('comments' => $_LANG['OTVETY'], 'add' => $_LANG['REPLY'], 'rss' => $_LANG['RSS_FEED'], 'not_comments' => $_LANG['NOT_ANSWERS']));
        $smarty->assign('is_admin', $inUser->is_admin);
        $smarty->assign('user_id', $user_id);
        $smarty->assign('karma_form', cmsKarmaForm('otvet', $question['id'], 0, $is_author));
        $smarty->display('com_otvety_read.tpl');
    }

/* ==================================================================================================== */
/* ========================== РЕДАКТИРОВАНИЕ ВОПРОСА ================================================== */
/* ==================================================================================================== */
    if ($do == 'edit') {

        if (!$inUser->id){
                cmsUser::goToLogin();
        }

        $question_user_id = $inDB->get_field('cms_otvety_quests', "id='{$id}'", 'user_id');

       //проверяем возможность редатирования
       if ($is_admin == '' && $question_user_id != $user_id) {

            $inPage->setTitle($_LANG['NO_RIGHTS']);
            $inPage->printHeading($_LANG['NO_RIGHTS']);
            echo '<div class=clear></div><p style="color:red">' . $_LANG['NO_PERMISSION_TOEDIT'] . '</p>';
            return;

        }

        $is_submit = $inCore->inRequest('question');

        if (!$is_submit) {

            //вытаскиваем переменную сессии
            $item = cmsUser::sessionGet('item');
            if ($item) { cmsUser::sessionDel('item'); }

            $validation = cmsUser::sessionGet('valid_question');
            if ($validation) { cmsUser::sessionDel('valid_question'); }

            //если значение $item пустое вытаскиваем вопрос
            if(!$item) {
                $item = $model->getQuestion($id);
                if(!$item) {cmsCore::error404();}
            }

            $inPage->setTitle($_LANG['EDIT_QUEST']);
            $inPage->addPathway($_LANG['EDIT_QUEST']);

            //получаем код панелей bbcode и смайлов
            $bb_toolbar = cmsPage::getBBCodeToolbar('question', $cfg['img_on']);
            $smilies = cmsPage::getSmilesPanel('question');

            $inCore->initAutoGrowText('#question'); //библиотека для автоматического увеличения textarea
            $autocomplete_js = $inPage->getAutocompleteJS('tagsearch', 'tags');

            //загружаем теги вопроса
            $item['tags'] = isset($item['id']) ? cmsTagLine('otvety', $item['id'], false) : $item['tags'];

            $inPage->initAutocomplete();

            //Удаляем промежуточные данные о загруженных изображениях
            $inCore->flushUpload();

            $inPage->addHeadJS('components/otvety/js/common.js');

            $smarty = $inCore->initSmarty('components', 'com_otvety_add.tpl');
            $smarty->assign('do', $do);
            $smarty->assign('catslist', $inCore->getListItems('cms_otvety_cats', $item['category_id'], 'id', 'ASC', 'published=1'));
            $smarty->assign('item', $item);
            $smarty->assign('user_id', $inUser->id);
            $smarty->assign('bb_toolbar', $bb_toolbar);
            $smarty->assign('smilies', $smilies);
            $smarty->assign('autogrow', $autogrow);
            $smarty->assign('autocomplete_js', $autocomplete_js);
            $smarty->display('com_otvety_add.tpl');
        }

        if ($is_submit) {

            $item = array();

            $item['title'] = $inCore->request('title', 'str', '');
            $item['question'] = $inCore->request('question', 'html');
            $item['tags'] = $inCore->request('tags', 'str', '');
            $item['category_id'] = $inCore->request('category_id', 'int', '');

            //переменные получили выше теперь проверяем их
            if (strlen($item['title']) < 10) {
                cmsCore::addSessionMessage($_LANG['TITLE_SHOT'], 'error');
                $item['title'] = stripslashes($_REQUEST['title']);
                $errors = true;
            }
            if (strlen($item['question']) < 10) {
                cmsCore::addSessionMessage($_LANG['MESS_SHOT'], 'error');
                $errors = true;
            }

            //Если есть ошибки, возвращаемся назад
            if ($errors) {
                cmsUser::sessionPut('item', $item);
                $inCore->redirectBack();
            }

            //Если нет ошибок
            if (!$errors) {
            $item['title'] = stripslashes($_REQUEST['title']);

            $question_id = $model->updateQuestion($item, $id);
            $inCore->redirect('/otvety/read'.$id.'.html');

            }
        }
    }

/* ==================================================================================================== */
/* ========================== ДОБАВЛЕНИЕ ВОПРОСА ====================================================== */
/* ==================================================================================================== */
    if ($do == 'add') {

        if (!$user_id && !$cfg['guest_enabled']) {
            cmsUser::goToLogin();
        }

        $error = '';
        if ($inCore->inRequest('submit') && !$user_id && !$inCore->checkCaptchaCode($inCore->request('code', 'str'))) {
            $error = $_LANG['ERR_CAPTCHA'];
        }

        $is_submit = $inCore->inRequest('question');

        if (!$is_submit || $error) {

            $inPage->setTitle($_LANG['ASK_QUES']);
            $inPage->addPathway($_LANG['ASK_QUES']);
            $inPage->backButton(false);

            //получаем код панелей bbcode и смайлов
            $bb_toolbar = cmsPage::getBBCodeToolbar('question', $cfg['img_on'], 'otvety');
            $smilies = cmsPage::getSmilesPanel('question');

            $inCore->initAutoGrowText('#question'); //библиотека для автоматического увеличения textarea

            $autocomplete_js = $inPage->getAutocompleteJS('tagsearch', 'tags');

            $inPage->initAutocomplete();

            //Удаляем промежуточные данные о загруженных изображениях
            $inCore->flushUpload();

            $item = cmsUser::sessionGet('item');
            if ($item) {
                cmsUser::sessionDel('item');
            }

            $item['category_id'] = $id;

            $inPage->addHeadJS('components/otvety/js/common.js');

            //FORM
            $smarty = $inCore->initSmarty('components', 'com_otvety_add.tpl');
            $smarty->assign('item', $item);
            $smarty->assign('catslist', $inCore->getListItems('cms_otvety_cats', $item['category_id'], 'id', 'ASC', 'published=1'));
            $smarty->assign('user_id', $inUser->id);

            $smarty->assign('bb_toolbar', $bb_toolbar);
            $smarty->assign('smilies', $smilies);
            $smarty->assign('autogrow', $autogrow);
            $smarty->assign('autocomplete_js', $autocomplete_js);

            $smarty->assign('error', $error);
            $smarty->display('com_otvety_add.tpl');
        }

        if ($is_submit) {

            $item = array();

            $item['published'] = ($inUser->is_admin || $cfg['publish']) ? 1 : 0;
            $item['title'] = $inCore->request('title', 'str', '');
            $item['user_id'] = $user_id;
            $item['question'] = $inCore->request('question', 'html');
            $item['anonimname'] = $inCore->request('anonimname', 'str', '');
            $item['tags'] = $inCore->request('tags', 'str', '');
            $item['category_id'] = $inCore->request('category_id', 'int', '');

            //переменные получили выше теперь проверяем их
            if (strlen($item['title']) < 10) {
                cmsCore::addSessionMessage($_LANG['TITLE_SHOT'], 'error');
                $item['title'] = stripslashes($_REQUEST['title']);
                $errors = true;
            }
            if (strlen($item['question']) < 10) {
                cmsCore::addSessionMessage($_LANG['MESS_SHOT'], 'error');
                $errors = true;
            }

            //Если есть ошибки, возвращаемся назад
            if ($errors) {
                cmsUser::sessionPut('item', $item);
                $inCore->redirectBack();
            }

            //Если нет ошибок
            if (!$errors) {
                //добавляем новый вопрос...
                if ($do == 'add') {
                    $question_id = $model->addQuestion($item);
                    $inCore->registerUploadImages(session_id(), $question_id, 'otvety');

                    //проверяем настройки категории вопросов и ответов, есть ли у нее модератор и его контактные данные
                    $sql    = "SELECT cat.title, cat.moderator_id, u.email, p.email_newmsg
                               FROM cms_otvety_cats cat
                               LEFT JOIN cms_users u ON u.id = cat.moderator_id
                               LEFT JOIN cms_user_profiles p ON p.user_id = cat.moderator_id
                               WHERE cat.id='{$item['category_id']}' LIMIT 1";
                    $result = $inDB->query($sql);

                    $cat = $inDB->fetch_assoc($result);

                    //отправляем уведомление о добавлении вопроса модератору категории
                    if($cat['moderator_id'] && $cat['moderator_id']!=0 && $cfg['send_notice']){

                        //формируем письмо
                        $inConf = cmsConfig::getInstance();
			$host = $inCore->getHost();
			$question_link = '<a target=_blank href="http://'.$host.'/otvety/read'.$question_id.'.html">'.$item['title'].'</a>';

                        $letter_path    = PATH.'/components/otvety/newquestion.txt';
                        $letter         = file_get_contents($letter_path);

                        $letter = str_replace('{category}', $cat['title'], $letter);
                        $letter = str_replace('{sitename}', $inConf->sitename, $letter);
                        $letter = str_replace('{link}', $question_link, $letter);

			//если хозяин вакансии онлайн, отправляем ему личное сообщение
			$moderator_online = $inDB->get_field('cms_online', "user_id=".$cat['moderator_id'], 'user_id');

                        //если модератор онлайн или у него нету email
                        if ($moderator_online || !$cat['email'] || !$cat['email_newmsg']) {

                            $msg_id = cmsUser::sendMessage(-2, $cat['moderator_id'], $letter);

                            if ($msg_id) {
                                //чтобы не мусорить
                                $sql = "UPDATE cms_user_msg SET from_del=1 WHERE id = $msg_id LIMIT 1";
                                $result = $inDB->query($sql);
                            }

                        } else {
                            //отправляем email
                            $subj       = 'Новый вопрос';
                            $to_email   = $cat['email'];
                            $inCore->mailText($to_email, $subj.'! - '.$inConf->sitename, $letter);

                        }
                    }

                    if ($item['published'] != 0) {

                        cmsActions::log('add_question', array(
                            'object' => $item['title'],
                            'object_url' => '/otvety/read'.$question_id.'.html',
                            'object_id' => $question_id,
                            'target' => $cat['title'],
                            'target_url' => '/otvety/'.$item['category_id'],
                            'target_url' => $item['category_id'],
                            'description' => ''
                        ));

                        cmsCore::addSessionMessage($_LANG['QUESTION_SENDED'], 'success');
                        $inCore->redirect('/otvety/read'.$question_id.'.html');
                    }

                    //следующая конструкция если необходима модерация вопроса
                    if ($item['published'] == 0) {

                        echo '<div class="con_heading">' . $_LANG['QUESTION_SEND'] . '</div><div class=clear></div>';
                        echo '<div style="margin-top:10px">' . $_LANG['QUESTION_PREMODER'] . '</div>';
                        echo '<div style="margin-top:10px"><a href="/otvety">'.$_LANG['CONTINUE'].'</a></div>';
                        return;
                    }
                }
            }
        }
    }

/* ==================================================================================================== */
/* ========================== УДАЛЕНИЕ ВОПРОСА ======================================================== */
/* ==================================================================================================== */

if ($do=='delete'){

        if (!$inUser->id){
                cmsUser::goToLogin();
        }

        $question_user_id = $inDB->get_field('cms_otvety_quests', "id='{$id}'", 'user_id');
        if (!$question_user_id){ cmsCore::error404(); }

       //проверяем возможность редатирования
       if ($is_admin == '' && $question_user_id != $user_id) {

            AccessDenied();
            return;

        }

	$model->deleteQuestion($id);
	cmsCore::addSessionMessage($_LANG['QUESTION_DELETE'], 'success');
	$inCore->redirect('/otvety/');

}

}
//добавленны функции для вывода картинки профиля рядом с вопросом
function usrLink2($title, $user_login){
	$inUser = cmsUser::getInstance();
    return '<a href="'.cmsUser::getProfileURL($user_login).'" title="'.strip_tags($title).'">'.$title.'</a>';
}

function usrImageNOdb2($user_id, $small='small', $usr_imageurl, $usr_is_deleted){
	if ($user_id == -1) {	return '<img border="0" width=45 class="usr_img_small" src="/images/messages/update.jpg" />';	}
	if ($user_id == -2) {	return '<img border="0" width=45 class="usr_img_small" src="/images/messages/massmail.jpg" />'; }

	if ($usr_imageurl){
		if($usr_is_deleted){
			if ($small=='small'){
				return '<img border="0" width=45 class="usr_img_small" src="/images/users/avatars/small/noprofile.jpg" />';
			} else {
				return '<img border="0" width=100 class="usr_img" src="/images/users/avatars/noprofile.jpg" />';
			}
		} else {
			if ($usr_imageurl && @file_exists($_SERVER['DOCUMENT_ROOT'].'/images/users/avatars/'.$usr_imageurl)){
				if ($small=='small'){
					return '<img border="0" width=45 class="usr_img_small" src="/images/users/avatars/small/'.$usr_imageurl.'" />';
				} else {
					return '<img border="0" width=100 class="usr_img" src="/images/users/avatars/'.$usr_imageurl.'" />';
				}
			} else {
				if ($small=='small'){ return '<img border="0" width=45 class="usr_img_small" src="/images/users/avatars/small/nopic.jpg" />';
				} else { return '<img border="0" width=100 class="usr_img" src="/images/users/avatars/nopic.jpg" />'; }
			}
		}
	} else {
			if ($small=='small'){ return '<img border="0" width=45  class="usr_img_small" src="/images/users/avatars/small/nopic.jpg" />';
			} else { return '<img border="0" width=100 class="usr_img" src="/images/users/avatars/nopic.jpg" />'; }
	}
}
?>