<h1>Oops! Nenašiel som metódu</h1>

<p>
Nenašiel som metódu s menom <code><?php echo $controllerMethod; ?></code>. Očakávam, že ju nájdem v súbore
</p>

<pre><?php echo $controllerPath; ?></pre>

<p>
Tento súbor som <strong>našiel</strong>, a <strong>našiel</strong> som aj triedu <code><?php echo $controllerName; ?></code>.
</p>

<p>
Aby to zafungovalo, potrebujem aspoň takéto niečo
</p>

<pre>
class <?php echo $controllerName; ?> extends AppController {
  function <?php echo $controllerMethod ?>() {
    ... 
  }
}
</pre>
