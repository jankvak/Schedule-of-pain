<h2>Zoznam členov skupiny <?php echo $collaboration_info['code'] . ' ' . $collaboration_info['name']; ?></h2>

<p><a href="all/collaboration/collaboration/<?php echo $collaboration_id; ?>/">Naspäť</a>

<table id="table1" class="sorted-table {sortlist: [[1,0]]}">
    <thead>
        <tr>
            <th width="140">Login</th>
            <th>Meno</th>
            <th width="110">Rola</th>
        </tr>
    </thead>

    <tbody>
        <?php
        foreach ($collaboration_users as $collaboration_user) {
            echo '<tr><td>' . $collaboration_user['login'] . '</td>';
            echo '<td>' . $collaboration_user['meno'] . '</td>';
            echo '<td>';
            foreach ($roles as $role) {
                if ($collaboration_roles[$collaboration_user['id']] == $role['id'])
                    echo $role['role'];
            }
            echo '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
