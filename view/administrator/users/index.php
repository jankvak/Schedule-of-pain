<h2>Správa používateľov</h2>
<p><a href='administrator/users/add'>Pridať nového používateľa</a></p>

<table id="table" class="sorted-table paged-table filtered {sortlist: [[1,0]], pagesizes:[10,25,50], selpagesize: 0}">
    <thead>
        <tr>
            <th>Login</th>
            <th>Meno</th>
            <th>Hodiny</th>
            <th>Mail</th>
            <th>Skupina</th>
            <th>Akcia</th>
        </tr>
    </thead>
    <tbody>

        <?php

        foreach($users as $usr) {
            echo "<tr>";
            echo "<td>" . $usr["login"] . "</td>";
            echo "<td>" . $usr["meno"] . "</td>";
            echo "<td>" . $usr["pocet_hodin"] . "</td>";
            echo "<td>" . $usr["mail"] . "</td>";
            echo "<td>" . $usr["skupina"] . "</td>";
            echo "<td class='action'>";
            echo "<a href='administrator/users/edit/{$usr["id"]}'>Upraviť</a><br/> ";
            echo "<a href='administrator/users/delete/{$usr["login"]}'>Vymazať</a><br/> ";
            // len pre formu nech to tam nesvieti
            if ($usr["id"] != $current_user_id && !$disable_role_taking) {
                echo "<a href='administrator/users/actAs/{$usr["id"]}'>Prebrať práva</a>";
            }
            echo "</td>";
            echo "</tr>";
        }

        ?>

    </tbody>
</table>
