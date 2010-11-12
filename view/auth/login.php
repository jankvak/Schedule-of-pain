<script type="text/javascript" src="js/login.js"></script>


<div id="login_box">
    <form method="post" action="auth/dologin">
		<p>
			<label for="name" class="required">Meno</label>
			<input type="text" id="name" name="name" class="login"/>
		</p>
		<p> 
			<label for="passwd" class="required">Heslo</label>
			<input type="password" name="passwd" id="passwd" class="password"/>
		</p>
		<p class="center">
			<input type="submit" value="Prihlás"/> 
		</p>
    </form>
	<p class="hint hint-login">
		Zadajte Vaše meno do <abbr title="Akademický informačný systém">AIS</abbr>
	</p>
	<p class="hint hint-password">
		Zadajte Vaše heslo do <abbr title="Akademický informačný systém">AIS</abbr>. Heslo je použité
		len na overenie Vašej totožnosti a nezaznamenáva sa.
	</p>	
</div>
