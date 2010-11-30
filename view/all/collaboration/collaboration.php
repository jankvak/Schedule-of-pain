<h2><?php echo $collaboration_info['code'] . ' ' . $collaboration_info['name']; ?></h2>
<p><a href="all/collaboration/index/">Naspäť na zoznam skupín</a><br/><br/>
<h6>Členovia:</h6>
<?php
foreach ($collaboration_menu as $collaboration_menu_item) {
    echo '<p><a href="' . $collaboration_menu_item['action'] . '">' . $collaboration_menu_item['text'] . '</a>';
}
?>
<br/><br/>
<h6>Príspevky:</h6>
<p><a href="all/collaboration/addMessage/<?php echo $collaboration_id; ?>/">Nový príspevok</a></p><br/>
<table class="sorted-table paged-table filtered {sortlist: [[1,1]], pagesizes:[10,25,50], selpagesize: 0}">
    <thead>
        <tr>
            <th width="130">Meno</th>
            <th width="120">Dátum</th>
            <th>Správa</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($collaboration_posts as $collaboration_post) {
            echo '<tr>';
            echo '<td>' . $collaboration_post['name'] . ' ' . $collaboration_post['last_name'] . '</td>';
            echo '<td align="center">' . date('d.m.Y H:i', $collaboration_post['timestamp']) . '</td>';
            echo '<td>' . $collaboration_post['message'] . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
