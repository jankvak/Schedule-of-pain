<h2>Správa členov skupiny <?php echo $collaboration_info['code'] . ' ' . $collaboration_info['name']; ?></h2>
<form class="collaboration_collaboration" method="post" action="all/collaboration/editCollaboration/<?php echo $collaboration_id; ?>">
    <p><a href="all/collaboration/collaboration/<?php echo $collaboration_id; ?>/">Naspäť</a>
    <p><input type="submit" value=">> Uložiť všetky zmeny <<" /></p><br/>
    <h6>Upraviť členov skupiny:</h6>
    <table class="sorted-table {sortlist: [[1,0]]}">
        <thead>
            <tr>
                <th width="140">Login</th>
                <th>Meno</th>
                <th width="110">Rola</th>
                <th width="75">Odobrať</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $i=0;
            foreach ($collaboration_users as $collaboration_user) {
                $i++;
                if ($current_user_id != $collaboration_user['id']) {
                    echo '<tr><td>' . $collaboration_user['login'] . '</td>';
                    echo '<td>' . $collaboration_user['meno'] . '</td>';
                    echo '<td align="center"><input type="hidden" name="existing_user[' . $i . '][id_person]" value="' . $collaboration_user['id']. '"/>';
                    echo '<select name="existing_user[' . $i . '][id_role]">';
                    foreach ($roles as $role) {
                        $name = $role['role'];
                        echo '<option value="' . $role['id'] . '"';
                        if ($collaboration_roles[$collaboration_user['id']] == $role['id'])
                            echo ' selected="selected"';
                        echo '>' . $name . '</option>';
                    }
                    echo '</select></td>';
                    echo '<td align="center"><input type="checkbox" name="existing_user[' . $i . '][action][remove]"/></td></tr>';
                }
            }
            ?>
        </tbody>
    </table>

    <h6>Pridať nových členov:</h6>

    <table class="sorted-table {sortlist: [[1,0]]}">
        <thead>
            <tr>
                <th width="140">Login</th>
                <th>Meno</th>
                <th width="110">Rola</th>
                <th width="75">Pridať</th>
            </tr>
        </thead>
        <tbody>
            <?php

            foreach ($users as $user) {
                $i++;
                echo '<tr><td>' . $user['login'] . '</td>';
                echo '<td>' . $user['meno'] . '</td>';
                echo '<td align="center"><input type="hidden" name="new_user[' . $i . '][id_person]" value="' . $user['id'] . '"/>';
                echo '<select name="new_user[' . $i . '][id_role]">';
                foreach ($roles as $role) {
                    $name = $role['role'];
                    echo '<option value="' . $role['id'] . '"';
                    if ($role['id'] == 2) 
                        echo ' selected="selected"';
                    echo '>' . $name . '</option>';
                }
                echo '</select></td>';
                echo '<td align="center"><input type="checkbox" name="new_user[' . $i . '][action][add]"/></td></tr>';
            }
            ?>
        </tbody>
    </table>
</form>
