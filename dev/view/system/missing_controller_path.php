<h1>Oops! Nenašiel som súbor s controllerom</h1>

<p>Nenašiel som súbor s controllerom</p>

<pre><?php echo $controllerPath; ?></pre>

<p>Keď ho nájdem, tak v ňom budem hľadať triedu <code><?php echo $controllerName; ?></code>.</p>

<p>Nech to v ňom vyzerá asi takto:</p>

<pre>
class <?php echo $controllerName; ?> extends AppController {
  function index() {
    ... 
  }
}
</pre>




