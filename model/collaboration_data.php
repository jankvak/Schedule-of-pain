<?php

if(!defined('IN_CMS')) {
    exit();
}

class CollaborationData extends Model {

    function getCollaborationPosts($collaboration_id) {
        $query =
                "SELECT w2.message, w2.timestamp, w1.name, w1.last_name
                FROM person w1, collaboration_data w2, collaboration w3
                WHERE w2.id_collaboration = $1
                  AND w3.id = $1
                  AND w1.id = w2.id_person";
        $this->dbh->Query($query, array($collaboration_id));
        $collaboration_posts = $this->dbh->fetchall_assoc();
        return $collaboration_posts;
    }

    function getPostCount($collaboration_id) {
        $query =
                "SELECT COUNT (*)
                FROM collaboration_data
                WHERE id_collaboration = $1";
        $this->dbh->Query($query, array($collaboration_id));
        $post_count = $this->dbh->fetch_assoc();
        return $post_count['count'];
    }

    function getPostLastDate($collaboration_id) {
        $query =
                "SELECT max(timestamp)
                FROM collaboration_data
                WHERE id_collaboration = $1";
        $this->dbh->Query($query, array($collaboration_id));
        $last_date = $this->dbh->fetch_assoc();
        return $last_date['max'];
    }

    function getPostPerson($collaboration_id, $post_id) {
        $query =
                "SELECT w1.name, w1.last_name
                FROM person w1, collaboration_data w2
                WHERE w2.id_collaboration = $1
                  AND w1.id = w2.id_person
                  AND w2.id = $2";
        $this->dbh->Query($query, array($collaboration_id, $post_id));
        $post_person = $this->dbh->fetchall_assoc();
        return $post_person;
    }

    function saveMessage($collaboration_id, $person_id) {
        $this->timestamp = time();

        $query =
                "INSERT INTO collaboration_data(id_collaboration, id_person, message, timestamp)
                VALUES ($1, $2, $3, $4)";
        $this->dbh->query($query, array(
                $collaboration_id, $person_id, $_POST['message'], $this->timestamp
        ));
    }
}
?>
