<h1>Oops! Nenašiel som controller</h1>

<p>
Nenašiel som triedu s menom <code><?php echo $controllerName; ?></code>. Očakávam, že ju nájdem v súbore
</p>

<pre><?php echo $controllerPath; ?></pre>

<p>
Tento súbor som <strong>našiel</strong>, ale trieda <code><?php echo $controllerName; ?></code> sa v ňom nenachádza.
</p>

<p>
Asi takto:
</p>

<pre>
class <?php echo $controllerName; ?> extends AppController {
  function index() {
    ... 
  }
}
</pre>
