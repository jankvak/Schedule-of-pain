<h2>Zoznam osôb, ktoré zadali časové priority</h2>

<table>
    <?php foreach($users as $user): ?>
        <tr>
            <td><a href="scheduler/priorities/show/<?php echo $user['id'] ?>"><?php echo $user["meno"] ?></a></td>
        </tr>
    <?php endforeach; ?>
</table>

