<?php
class moderation extends cmsFrontend {

    protected $useOptions = true;

    /**
     * Ставит запись на модерацию и отправляет уведомления модератору
     *
     * @param string $target_name Имя цели модерации
     * @param array $item Массив модерируемой записи
     * @param boolean $is_new_item Новая запись или редактируемая
     * @return void
     */
    public function requestModeration($target_name, $item, $is_new_item = true){

        $moderator_id = $this->model->getNextModeratorId($target_name);

        $moderator = $this->model_users->getUser($moderator_id);
        if(!$moderator){ return; }

        $author = $this->model_users->getUser($item['user_id']);
        if(!$author){ return; }

        // добавляем задачу модератору
        $this->model->addModeratorTask($target_name, $moderator_id, $is_new_item, $item);

        // личное сообщение
        $this->controller_messages->addRecipient($moderator['id'])->sendNoticePM(array(
            'content' => LANG_MODERATION_NOTIFY,
            'actions' => array(
                'view' => array(
                    'title' => LANG_SHOW,
                    'href'  => $item['page_url']
                )
            )
        ));

        // EMAIL уведомление
        $to = array('email' => $moderator['email'], 'name' => $moderator['nickname']);

        $this->controller_messages->sendEmail($to, 'moderation', array(
            'moderator'  => $moderator['nickname'],
            'author'     => $author['nickname'],
            'author_url' => href_to_abs('users', $author['id']),
            'page_title' => $item['title'],
            'page_url'   => $item['page_url'],
            'date'       => html_date_time()
        ));

        cmsUser::addSessionMessage(sprintf(LANG_MODERATION_IDLE, $moderator['nickname']), 'info');

        return;

    }

    /**
     * Отправляет автору уведомление о модерации
     * успешной или неуспешной
     *
     * @param array $item Массив модерируемой записи
     * @param string $letter moderation_approved || moderation_refused || moderation_rework
     * @return $this
     */
    public function moderationNotifyAuthor($item, $letter){

        $author = $this->model_users->getUser($item['user_id']);
        if(!$author){ return $this; }

        // личное сообщение
        $this->controller_messages->addRecipient($author['id'])->sendNoticePM(array(
            'content' => sprintf(string_lang('PM_'.$letter), $item['title'], (isset($item['page_url']) ? $item['page_url'] : ''), (isset($item['reason']) ? $item['reason'] : ''))
        ));

        // EMAIL уведомление
        $to = array('email' => $author['email'], 'name' => $author['nickname']);

        $this->controller_messages->sendEmail($to, $letter, array(
            'nickname'   => $author['nickname'],
            'page_title' => $item['title'],
            'page_url'   => (isset($item['page_url']) ? $item['page_url'] : ''),
            'reason'     => (isset($item['reason']) ? $item['reason'] : ''),
            'date'       => html_date_time()
        ));

        return $this;

    }

    /**
     * Одобряет модерацию для цели
     *
     * @param string $target_name Имя цели модерации
     * @param array $item Массив модерируемой записи
     * @param string $ups_key Ключ UPS для записей просмотра модераций
     * @return boolean
     */
    public function approve($target_name, $item, $ups_key = false) {

        $task = $this->model->getModeratorTask($target_name, $item['id']);

        $this->model->closeModeratorTask($target_name, $item['id'], true, $this->cms_user->id);

        $after_action = $task['is_new_item'] ? 'add' : 'update';

        cmsEventsManager::hook("content_after_{$after_action}_approve", array('ctype_name'=>$target_name, 'item'=>$item));

        cmsEventsManager::hook("content_{$target_name}_after_{$after_action}_approve", $item);

        $this->moderationNotifyAuthor($item, 'moderation_approved');

        if($ups_key){
            cmsUser::deleteUPSlist($ups_key);
        }

        return true;

    }

    /**
     * Отзывает запись с модерации и отправляет уведомления модератору
     *
     * @param string $target_name Имя цели модерации
     * @param array $item Массив модерируемой записи
     * @param string $ups_key Ключ UPS для записей просмотра модераций
     * @return boolean
     */
    public function cancelModeratorTask($target_name, $item, $ups_key = false) {

        $task = $this->model->cancelModeratorTask($target_name, $item['id'], $this->cms_user->id);
        if(!$task){ return false; }

        $moderator = $this->model_users->getUser($task['moderator_id']);
        if(!$moderator){ return false; }

        $author = $this->model_users->getUser($item['user_id']);
        if(!$author){ return false; }

        // личное сообщение
        $this->controller_messages->addRecipient($moderator['id'])->sendNoticePM(array(
            'content' => LANG_MODERATION_RETURN_NOTIFY,
            'actions' => array(
                'view' => array(
                    'title' => LANG_SHOW,
                    'href'  => $item['page_url']
                )
            )
        ));

        // EMAIL уведомление
        $to = array('email' => $moderator['email'], 'name' => $moderator['nickname']);

        $this->controller_messages->sendEmail($to, 'moderation_return', array(
            'moderator'  => $moderator['nickname'],
            'author'     => $author['nickname'],
            'author_url' => href_to_abs('users', $author['id']),
            'page_title' => $item['title'],
            'page_url'   => $item['page_url']
        ));

        if($ups_key){
            cmsUser::deleteUPSlist($ups_key);
        }

        return true;

    }

    /**
     * Отправляет запись на доработку
     *
     * @param string $target_name Имя цели модерации
     * @param array $item Массив модерируемой записи
     * @param string $ups_key Ключ UPS для записей просмотра модераций
     * @return boolean
     */
    public function reworkModeratorTask($target_name, $item, $ups_key = false) {

        $task = $this->model->cancelModeratorTask($target_name, $item['id'], $this->cms_user->id);
        if(!$task){ return false; }

        $this->moderationNotifyAuthor($item, 'moderation_rework');

        if($ups_key){
            cmsUser::deleteUPSlist($ups_key);
        }

        return true;

    }

    public function getUserDraftCounts($user_id) {

        $listeners = cmsEventsManager::getEventListeners('moderation_list');

        $counts = array();

        foreach ($listeners as $controller_name) {

            if(!cmsController::enabled($controller_name)){
                continue;
            }

            $draft_counts = cmsCore::getModel($controller_name)->getDraftCounts($user_id);
            if (!$draft_counts) { continue; }

            if(is_numeric($draft_counts)){
                $counts[$controller_name] = $draft_counts;
            } else {
                $counts = array_merge($counts, $draft_counts);
            }

        }

        return $counts;

    }

}
