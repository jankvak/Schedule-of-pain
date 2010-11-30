<h2>Pridanie príspevku do skupiny <?php echo $collaboration_info['code'] . ' ' . $collaboration_info['name']; ?></h2>

<p><a href="all/collaboration/collaboration/<?php echo $collaboration_id; ?>/">Naspäť</a><br/>
    <?php
    $action = "all/collaboration/saveMessage/{$collaboration_id}/";
    ?>
<form method="post" action="<?php echo $action; ?>">
    <p>
        <label for="text">Text:</label>
        <textarea id="text" name="message" cols="65" rows="15"></textarea>
    </p>
    <p>
        <input type="submit" value="Ulož"/>
    </p>
</form>