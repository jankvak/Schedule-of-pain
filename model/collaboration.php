<?php

if(!defined('IN_CMS')) {
    exit();
}

class Collaboration extends Model {

    function getUserCollaborations($user_id) {
        $query =
                "SELECT w1.id, w1.name, w1.code, w3.role
                FROM collaboration w1, collaboration_person_role w2, collaboration_role w3
                WHERE w2.id_person = $1
                  AND w2.id_collaboration = w1.id
                  AND w2.id_role = w3.id";
        $this->dbh->Query($query, array($user_id));
        $user_collaborations = $this->dbh->fetchall_assoc();
        return $user_collaborations;
    }

    function getCollaborationUsers($collaboration_id) {
        $query =
                "SELECT p.id, p.login, " .
                Users::vyskladajMeno("p") .
                ", w2.id_role, w3.role
            FROM person p, collaboration_person_role w2, collaboration_role w3
                WHERE w2.id_collaboration = $1
                  AND w2.id_person = p.id
                  AND w2.id_role = w3.id";

        $this->dbh->Query($query, array($collaboration_id));
        $collaboration_users = $this->dbh->fetchall_assoc();
        return $collaboration_users;
    }

    function getUsersNotInCollaboration($collaboration_id) {
        $query =
                "SELECT p.id, p.login, " .
                Users::vyskladajMeno("p") .
                "FROM person p
                WHERE p.grade IS NULL
                  AND p.id <> ALL (select id_person from collaboration_person_role where id_collaboration = $1)";

        $this->dbh->Query($query, array($collaboration_id));
        $users_not_in_collab = $this->dbh->fetchall_assoc();
        return $users_not_in_collab;
    }

    function getCollaborationInfo($collaboration_id) {
        $query =
                "SELECT name, code
                FROM collaboration
                WHERE id = $1";
        $this->dbh->Query($query, array($collaboration_id));
        $collaboration_info = $this->dbh->fetch_assoc();
        return $collaboration_info;
    }

    function getRoles() {
        $query =
                "SELECT id, role
                FROM collaboration_role";
        $this->dbh->Query($query);
        $roles = $this->dbh->fetchall_assoc();
        return $roles;
    }

    function addUser($collaboration_id, $user_id, $role_id) {
        $query =
                "INSERT INTO collaboration_person_role (id_collaboration, id_person, id_role)
                VALUES ($1, $2, $3)";
        $this->dbh->Query($query, array($collaboration_id, $user_id, $role_id));
    }

    function getUserRole($collaboration_id, $user_id) {
        $query =
                "SELECT w2.id, w2.role
                FROM collaboration_person_role w1, collaboration_role w2
                WHERE w1.id_collaboration = $1
                  AND w1.id_person = $2
                  AND w1.id_role = w2.id";
        $this->dbh->Query($query, array($collaboration_id, $user_id));
        $user_role = $this->dbh->fetch_assoc();
        return $user_role;
    }

    function removeUserFromCollaboration($collaboration_id, $user_id) {
        $query =
                "DELETE
                FROM collaboration_person_role
                WHERE id_collaboration = $1
                  AND id_person = $2";
        $this->dbh->Query($query, array($collaboration_id, $user_id));
    }

    function updateUserRole($collaboration_id, $user_id, $role_id) {
        $query =
                "UPDATE collaboration_person_role
                SET id_role = $3
                WHERE id_collaboration = $1
                  AND id_person = $2";
        $this->dbh->Query($query, array($collaboration_id, $user_id, $role_id));
    }
}
?>
