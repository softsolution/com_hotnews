<?php
/******************************************************************************/
//                       soft-solution.ru team                                //
/******************************************************************************/
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

class cms_model_otvety{

    function __construct(){
        $this->inDB        = cmsDatabase::getInstance();
        $this->inCore      = cmsCore::getInstance();
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function install(){

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'otvety': $item = $this->inDB->get_fields('cms_otvety_quests', "id={$target_id}", 'title');
                        if (!$item) { return false; }
                        $result['link']     = '/otvety/read'.$target_id.'.html';
                        $result['title']    = (strlen($item['title'])<100 ? $item['title'] : substr($item['title'], 0, 100).'...');
                        break;

        }

        return ($result ? $result : false);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getQuestion($question_id){

        $sql = "SELECT q.*,
                cat.title as cat_title, cat.seolink as cat_seolink, u.login, u.nickname, u.is_deleted, p.imageurl
                FROM cms_otvety_quests q
                LEFT JOIN cms_otvety_cats cat ON cat.id = q.category_id
                LEFT JOIN cms_users u ON u.id = q.user_id
                LEFT JOIN cms_user_profiles p ON p.id = q.user_id
                WHERE q.id = '$question_id' LIMIT 1 ";

	$result = $this->inDB->query($sql);
	$question  = $this->inDB->num_rows($result) ? $this->inDB->fetch_assoc($result) : false;

        if ($question){

            if (strlen($question['title']) > 40) {
                $question['short_title'] = substr($question['title'], 0, 40) . '...';
            } else {
                $question['short_title'] = $question['title'];
            }

            if($question['moderator_id']>0) {
                $sql = "SELECT  u.login as moder_login,
                                u.nickname as moder_nickname,
                                u.is_deleted as moder_is_deleted, p.imageurl as moder_imageurl
                        FROM cms_users u
                        LEFT JOIN cms_user_profiles p ON p.user_id = u.id
                        WHERE u.id = '{$question['moderator_id']}' LIMIT 1";
                $result = $this->inDB->query($sql);
                $moder  = $this->inDB->num_rows($result) ? $this->inDB->fetch_assoc($result) : false;

                $question['moder_login']     = $moder['moder_login'];
                $question['moder_nickname']   = $moder['moder_nickname'];
                $question['moder_is_deleted'] = $moder['moder_is_deleted'];
                $question['moder_imageurl'] = $moder['moder_imageurl'];
            }

            $question['fpubdate'] = $this->inCore->dateFormat($question['pubdate']);

            $this->inDB->query("UPDATE cms_otvety_quests SET hits = hits + 1 WHERE id = '$question_id'");
            $question = cmsCore::callEvent('GET_QUESTION', $question);
       }

        return $question;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteQuestion($id){

        cmsCore::callEvent('DELETE_QUESTION', $id);

        $inCore = cmsCore::getInstance();
        $inCore->loadLib('tags');
        $inCore->loadLib('karma');

        $this->inDB->query("DELETE FROM cms_otvety_quests WHERE id='$id'");
        $this->inDB->query("DELETE FROM cms_tags WHERE target='otvety' AND item_id = '$id'");

	$inCore->deleteRatings('otvet', $id);
        $inCore->deleteComments('otvety', $id);

        cmsClearTags('otvety', $id);

        $inCore->deleteUploadImages($id, 'otvety');
        cmsActions::removeObjectLog('add_question', $id);

        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteQuestions($id_list){
        foreach($id_list as $key=>$id){
            $this->deleteQuestion($id);
        }
        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function deleteCatQuestions($id){

        //DELETE CATEGORY
        $sql = "DELETE FROM cms_otvety_cats WHERE id = $id LIMIT 1";
        dbQuery($sql);

        $sql = "SELECT id FROM cms_otvety_quests WHERE category_id = '$id'";
        $result = $this->inDB->query($sql);

	$item = $this->inDB->fetch_assoc($result);

        foreach($item as $key=>$id){
            $this->deleteQuestion($id);
        }

        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function where($condition){
        $this->where .= ' AND ('.$condition.')' . "\n";

    }

    public function whereCatIs($cat_id){
        $this->where("i.category_id = '$cat_id'");
        return;
    }

    public function whereThisAndNestedCats($left_key, $right_key) {
        $this->where("cat.NSLeft >= $left_key AND cat.NSRight <= $right_key AND cat.parent_id > 0");
    }

    public function whereVip($flag) {
        $this->where("i.is_vip = $flag");
    }

    public function whereUserIs($user_id) {
        $this->where("i.user_id = '$user_id'");
    }

    public function groupBy($field){
        $this->group_by = 'GROUP BY '.$field;
    }

    public function orderBy($field, $direction='ASC'){
        $this->order_by = 'ORDER BY '.$field.' '.$direction;
    }

    public function limit($howmany) {
        $this->limitIs(0, $howmany);
    }

    public function limitIs($from, $howmany='') {
        $this->limit = (int)$from;
        if ($howmany){
            $this->limit .= ', '.$howmany;
        }
    }

    public function limitPage($page, $perpage) {
        $this->limitIs(($page-1)*$perpage, $perpage);
    }

// ============================================================================ //
// ============================================================================ //

    private function resetConditions(){

        $this->where        = '';
        $this->group_by     = '';
        $this->order_by     = '';
        $this->limit        = '';

    }

// ============================================================================ //
// ============================================================================ //
    public function getCategoryCount($show_all = false){

        $pub_where = ($show_all ? '1=1' : 'cat.published = 1');

        $sql = "SELECT 1 FROM cms_otvety_cats cat
                WHERE {$pub_where} {$this->where}
                {$this->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCategories($show_all = false) {

        $pub_where = ($show_all ? '1=1' : 'cat.published = 1');

        $sql = "SELECT cat.*
                FROM cms_otvety_cats cat
                WHERE {$pub_where} {$this->where} {$this->group_by} {$this->order_by}\n";

        if ($this->limit) {
            $sql .= "LIMIT {$this->limit}";
        }

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) {
            return false;
        }

        $records = array();

        while ($item = $this->inDB->fetch_assoc($result)) {
            $item['count_question'] = $this->inDB->rows_count('cms_otvety_quests', 'category_id='.$item['id'].' AND published = 1');
            $item['count_question_case'] = $this->declineCases($item['count_question']);

            $sql2 = "SELECT 1 FROM cms_comments c LEFT JOIN cms_otvety_quests q ON q.id = c.target_id WHERE c.target = 'otvety' AND c.published = 1 AND q.published = 1 AND q.category_id = {$item['id']}";
            $result2 = $this->inDB->query($sql2);
            $item['answers_count'] = $this->inDB->num_rows($result2);
            $item['answer_case'] = $this->declineCases($item['answers_count']);

            $sql3 = "SELECT SUM(t.total_rating) as sum_rating  FROM cms_ratings_total t LEFT JOIN cms_otvety_quests q ON q.id = t.item_id WHERE t.target = 'otvet' AND q.published = 1 AND q.category_id = {$item['id']}";
            $result3 = $this->inDB->query($sql3);
            $sum = $this->inDB->fetch_assoc($result3);
            $item['sum_rating'] = $sum['sum_rating'] ? $sum['sum_rating'] : '0';
            $item['rating_case'] = $this->declineCases($item['sum_rating']);
            $item['file'] = $this->ImageCat($item['file']);
            $records[] = $item;
        }

        $this->resetConditions();

        return $records;
    }
/* ==================================================================================================== */
/* ==================================================================================================== */
//считает количество вопросов удовлетворяющих определнным условиям
    public function getQuestionCount($show_all = false){

        //подготовим условия
        $pub_where = ($show_all ? '1=1' : 'i.published = 1');

        $sql = "SELECT 1 FROM cms_otvety_quests i
                INNER JOIN cms_otvety_cats cat ON cat.id = i.category_id
                WHERE {$pub_where} {$this->where}
                {$this->group_by}\n";

		$result = $this->inDB->query($sql);

		return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getQuestions($show_all = false, $is_users = false, $is_coments = false, $is_cats = false, $rss=false) {

        //подготовим условия
        $pub_where = ($show_all ? '1=1' : 'i.published = 1');
        $r_join = $is_users ? " LEFT JOIN cms_users u ON u.id = i.user_id \n" : '';
        $r_join .= $is_cats ? " INNER JOIN cms_otvety_cats cat ON cat.id = i.category_id " : '';

        $r_select = $is_users ? ', u.login, u.nickname' : '';
        $r_select .= $is_cats ? ', cat.title as cat_title, cat.seolink ' : '';

        $sql = "SELECT i.*, IFNULL(r.total_rating,0) as rating{$r_select}
                FROM cms_otvety_quests i {$r_join}
                LEFT JOIN cms_ratings_total r ON (i.id = r.item_id AND r.target='otvet')
                WHERE {$pub_where} {$this->where} {$this->group_by} {$this->order_by}\n";

        if ($this->limit) {
            $sql .= "LIMIT {$this->limit}";
        }

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) {
            return false;
        }

        $records = array();

        while ($item = $this->inDB->fetch_assoc($result)) {
            if(!$rss){
                if ($is_coments) {
                    $item['answers_count'] = $this->inCore->getCommentsCount('otvety', $item['id']);
                    if($item['answers_count']!=0) {
                        $sql2 = "SELECT MAX(pubdate) as pubdate FROM cms_comments WHERE published = 1 AND target='otvety' AND target_id='{$item['id']}'";
                        $result2 = $this->inDB->query($sql2);
                        $comment = $this->inDB->fetch_assoc($result2);
                    }
                    //если значение счетчика отличается от значения в поле answers обновляем его
                    if($item['answers_count']!=$item['answers']){
                        $this->inDB->query("UPDATE cms_otvety_quests SET answers = {$item['answers_count']}, last_answer='{$comment['pubdate']}' WHERE id = {$item['id']} LIMIT 1");
                    }
                    $item['answer_case'] = $this->declineCases($item['answers_count']);
                }

                $item['rating_case'] = $this->declineCases($item['rating']);
                $item['content']    = nl2br($item['question']);
                $item['fpubdate']   = $this->inCore->dateFormat($item['pubdate']);
                $item['fpubdate']   = $this->inCore->dateDiffNow($item['pubdate']).' назад ('.$item['fpubdate'].')';
                $item['tagline']    = cmsTagLine('otvety', $item['id']);
                $item['hits_case']  = $this->declineCases($item['hits']);
            }
            $records[] = $item;
        }

        $this->resetConditions();

        return $records;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
//используется для RSS, для модуля с полной статистикой
    public function getCategory($category_id) {

        $sql = "SELECT * FROM cms_otvety_cats WHERE published = 1 AND id={$category_id} LIMIT 1";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) {
            return false;
        }

        $category = $this->inDB->fetch_assoc($result);

        return $category;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function addQuestion($item){

        //парсим bb-код перед записью в базу
        $item['question_html']   = $this->inCore->parseSmiles($item['question'], true);
        $item['question']        = $this->inDB->escape_string($item['question']);
        $item['question_html']   = $this->inDB->escape_string($item['question_html']);

        $sql = "INSERT INTO cms_otvety_quests (category_id, pubdate, user_id, title, question, question_html, hits, answers, last_answer, anonimname, published)
                VALUES ('{$item['category_id']}', NOW(), '{$item['user_id']}', '{$item['title']}', '{$item['question']}', '{$item['question_html']}', 0, 0, '', '{$item['anonimname']}', {$item['published']})";

        $result = $this->inDB->query($sql);

        $question_id = $this->inDB->get_last_id('cms_otvety_quests');

        cmsInsertTags($item['tags'], 'otvety', $question_id);

        return $question_id ? $question_id : false;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function updateQuestion($item, $question_id){

        //парсим bb-код перед записью в базу
        $item['question_html']   = $this->inCore->parseSmiles($item['question'], true);
        $item['question']        = $this->inDB->escape_string($item['question']);
        $item['question_html']   = $this->inDB->escape_string($item['question_html']);

        $sql = "UPDATE cms_otvety_quests
        SET category_id={$item['category_id']},
            title='{$item['title']}',
            question='{$item['question']}',
            question_html='{$item['question_html']}'
        WHERE id = $question_id";

        $result = $this->inDB->query($sql);

        cmsInsertTags($item['tags'], 'otvety', $question_id);

        return true;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function ImageCat($imageurl){

	if ($imageurl){
		if ($imageurl && @file_exists($_SERVER['DOCUMENT_ROOT'].'/images/photos/small/'.$imageurl)){

                    return $imageurl;

                } else {
                    return 'otvety_default.png';
                }

	} else {
            return 'otvety_default.png';
	}
}

/* ==================================================================================================== */
/* =================================== Склоняем по падежам ============================================ */
/* ==================================================================================================== */
/* возвращает падеж числительного
 * one - именительный,
 * two - родительный ед. число,
 * many - родительный множественное число
 */

    public function declineCases($num){
        $num = abs($num);
        if ($num % 10 == 1 && $num % 100 != 11) {
            $case = 'one';
        } elseif ($num % 10 >= 2 && $num % 10 <= 4 && ($num % 100 < 10 || $num % 100 >= 20)) {
            $case = 'two';
        } else {
            $case = 'many';
        }
        return $case;
}

/* ==================================================================================================== */
/* ==================================================================================================== */
}
?>