<h2>Nastavenia</h2>
<form action="all/profile/save" method="post" >
    <p>
	   <label for="mail">Notifikačný mail:</label>
        <input type="text" name="mail" size="50" value="<?php 
if(empty($usr['mail']))
    echo '@';
else
    echo $usr['mail'] ;

?>" />
    </p>
    <p>
        <label for="notifyMyActions">Notifikovať ma pri mnou vykonaných zmenách</label>
        <input type="checkbox" name="notifyMyActions" <?php if ($usr['posielat_moje_zmeny']) echo " checked='checked'";?>/>
    </p>
    <br />
    <br />
    <p>        
        <input type="submit" value="Zmeň" />
    </p>
</form>
