<?php

class CollaborationController extends AppController {

    protected $access = array('All');

    function __construct() {
        parent::__construct();
        $this->collaboration_data = new CollaborationData();
        $this->collaboration = new Collaboration();
        $this->users = new Users();
    }

    function __destruct() {
    }

    function index() {
        $user_collaborations = $this->collaboration->getUserCollaborations($this->session->read('uid'));

        $collaborations_summary = array();
        foreach ($user_collaborations as $user_collaboration) {
            $collaborations_summary[$user_collaboration['id']]['id'] = $user_collaboration['id'];
            $collaborations_summary[$user_collaboration['id']]['code'] = $user_collaboration['code'];
            $collaborations_summary[$user_collaboration['id']]['name'] = $user_collaboration['name'];
            $collaborations_summary[$user_collaboration['id']]['user_role'] = $user_collaboration['role'];
            $collaborations_summary[$user_collaboration['id']]['post_count'] = $this->collaboration_data->getPostCount($user_collaboration['id']);
            $collaborations_summary[$user_collaboration['id']]['post_last_date'] = $this->collaboration_data->getPostLastDate($user_collaboration['id']);
        }

        $this->set('collaborations_summary', $collaborations_summary);
    }

    function collaboration($collaboration_id) {
        $collaboration_menu = $this->getCollaborationMenu($collaboration_id);
        $collaboration_posts = $this->collaboration_data->getCollaborationPosts($collaboration_id);
        $collaboration_info = $this->collaboration->getCollaborationInfo($collaboration_id);

        $this->set('collaboration_menu', $collaboration_menu);
        $this->set('collaboration_posts', $collaboration_posts);
        $this->set('collaboration_info', $collaboration_info);
        $this->set('collaboration_id', $collaboration_id);
    }

    function admin($collaboration_id) {
        if (!$this->isCollaborationAdmin($collaboration_id)) {
            $this->redirect("all/collaboration/collaboration/{$collaboration_id}");
        } else {
            $all = $this->collaboration->getUsersNotInCollaboration($collaboration_id);
            $collaboration_users = $this->collaboration->getCollaborationUsers($collaboration_id);
            $collaboration_info = $this->collaboration->getCollaborationInfo($collaboration_id);
            $roles = $this->collaboration->getRoles();
            $collaboration_roles = $this->getCollaborationRoles($collaboration_users);
            $this->set('collaboration_id', $collaboration_id);
            $this->set('collaboration_info', $collaboration_info);
            $this->set('users', $all);
            $this->set('collaboration_users', $collaboration_users);
            $this->set('collaboration_roles', $collaboration_roles);
            $this->set('roles', $roles);
            $this->set('current_user_id', $this->session->read("uid"));
        }
    }

    function members($collaboration_id) {
        $collaboration_users = $this->collaboration->getCollaborationUsers($collaboration_id);
        $collaboration_info = $this->collaboration->getCollaborationInfo($collaboration_id);
        $roles = $this->collaboration->getRoles();
        $collaboration_roles = $this->getCollaborationRoles($collaboration_users);
        $this->set('collaboration_id', $collaboration_id);
        $this->set('collaboration_info', $collaboration_info);
        $this->set('collaboration_users', $collaboration_users);
        $this->set('collaboration_roles', $collaboration_roles);
        $this->set('roles', $roles);
        $this->set('current_user_id', $this->session->read("uid"));
    }

    function message($collaboration_id) {
        $collaboration_info = $this->collaboration->getCollaborationInfo($collaboration_id);
        $this->set('collaboration_info', $collaboration_info);
        $this->set('collaboration_id', $collaboration_id);
    }

    function getCollaborationRoles($collaboration_users) {
        $collaboration_roles = array();
        foreach ($collaboration_users as $collaboration_user) {
            $collaboration_roles[$collaboration_user["id"]] = $collaboration_user["id_role"];
        }
        return $collaboration_roles;
    }

    function getCollaborationMenu($collaboration_id) {
        $collaboration_menu[0]['text'] = 'Zoznam členov';
        $collaboration_menu[0]['action'] = 'all/collaboration/members/' . $collaboration_id . '/';
        if ($this->isCollaborationAdmin($collaboration_id)) {
            $collaboration_menu[1]['text'] = 'Správa členov';
            $collaboration_menu[1]['action'] = 'all/collaboration/admin/' . $collaboration_id . '/';
        }
        return $collaboration_menu;
    }

    function isCollaborationAdmin($collaboration_id) {
        $actual_role = $this->collaboration->getUserRole($collaboration_id, $this->session->read("uid"));
        if ($actual_role['role'] != 'Moderátor') {
            return false;
        } else {
            return true;
        }
    }

    function editCollaboration($collaboration_id) {
        if (!$this->isCollaborationAdmin($collaboration_id)) {
            $this->redirect("all/collaboration/collaboration/{$collaboration_id}");
        } else {
            $existing_collaboration_users = $this->collaboration->getCollaborationUsers($collaboration_id);
            $modified_collaboration_users = $_POST['existing_user'];
            $new_collaboration_users = $_POST['new_user'];

            foreach ($modified_collaboration_users as $modified_collaboration_user) {
                if ($modified_collaboration_user['action']['remove'] == 'on') {
                    $this->collaboration->removeUserFromCollaboration($collaboration_id, $modified_collaboration_user['id_person']);
                } else {
                    $actual_role = $this->collaboration->getUserRole($collaboration_id, $modified_collaboration_user['id_person']);
                    if ($actual_role['id'] != $modified_collaboration_user['id_role']) {
                        $this->collaboration->updateUserRole($collaboration_id, $modified_collaboration_user['id_person'], $modified_collaboration_user['id_role']);
                    }
                }
            }

            foreach ($new_collaboration_users as $new_collaboration_user) {
                if ($new_collaboration_user['action']['add'] == 'on') {
                    $this->collaboration->addUser($collaboration_id, $new_collaboration_user['id_person'], $new_collaboration_user['id_role']);
                }
            }
            $this->redirect("all/collaboration/admin/{$collaboration_id}");
        }
    }

    function saveMessage($collaboration_id) {
        try {
            $checked = $this->bind($this->collaboration_data);

            $this->collaboration_data->saveMessage($collaboration_id, $this->session->read('uid'));
            //notifikujeme admina o pridani pripomienky
            //$this->notificator->sendSuggestionAddedMsg($this->suggestions);
            //zalogujeme a oznamime pouzivatelovi
            //$this->log("Vloženie pripomienky");
            //$this->flash('Pripomienka vložená', 'info');
            $this->redirect('all/collaboration/collaboration/' . $collaboration_id);
        }
        catch(dataValidationException $ex) {
            $this->_invalid_data($ex->checked);
        }
    }
}
?>
