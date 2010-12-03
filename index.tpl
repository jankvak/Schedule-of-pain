<!-- BEGIN:PAGE -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="3600" />
	<meta name="revisit-after" content="2 days" />
	<meta name="robots" content="noindex,nofollow" />
	<meta name="distribution" content="global" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<base href="{BASE_URL}/" />
	<title>Systém pre podporu tvorby rozvrhov</title>

	<link rel="stylesheet" type="text/css" href="css/dark.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/core.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/colors.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/layout.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/priority_layout.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="css/jquery.tablesorter.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="css/jquery.tablesorter.pager.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.4.custom.css">
        <link rel="stylesheet" type="text/css" href="css/ui.dropdownchecklist.themeroller.css">
        <link rel="stylesheet" type="text/css" href="css/fullcalendar.css" media="screen"/>
        <link rel='stylesheet' type='text/css' href='css/redmond/theme.css' media="screen"/>
  

	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.ui.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery-plugins/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-plugins/jquery-ui-1.8.4.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/autocomplete.js"></script>
	<script type="text/javascript" src="js/delete-guardian.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.tablesorter.filter.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.metadata.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.tablesorter.pager.js"> </script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.ui.timepicker.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.ui.datepicker-sk.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.scrollTo.js"></script>
	<script type="text/javascript" src="js/jquery-plugins/jquery.scrollTo-min.js"></script>
	<script type="text/javascript" src="js/tables.js"></script>
  <!--fullcalendar-->
  <script type='text/javascript' src='js/jquery-plugins/fullcalendar.js'></script>
  <script type='text/javascript' src='js/jquery-plugins/fullcalendar.gcal.js'></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$("input:first").focus();
		});
	</script>


    <!-- Include the DropDownCheckList supoprt -->
    <script type="text/javascript" src="js/jquery-plugins/ui.dropdownchecklist-1.1-min.js"></script>

    <!-- Apply dropdown check list to the selected items -->
    <script type="text/javascript">
        $(document).ready(function() {
            $("#s1").dropdownchecklist();

        });
    </script>

</head>
<body>

<div id="wrap">

	<div id="title">
		<h1><a href="./">Systém pre podporu tvorby rozvrhov</a></h1>
        <h3>SLOVENSKÁ TECHNICKÁ UNIVERZITA V BRATISLAVE</h3>
        <h4>Fakulta informatiky a informačných technológií</h4>
            <div id="help">
                {HELP}
            </div>
	</div>

  <div id="menu">
  	  {SEMESTER}
      <ul>
          {MENU}
      </ul>
  </div>
  
  <div id="content">
      {FLASH}
      {CONTENT}
  </div>
        
  <div id="footer">
        Vytvorili tímy: <a href="http://labss2.fiit.stuba.sk/TeamProject/2008/team20is-si/" target="_blank">#backspace</a> 2008-2009,
        <a href="http://labss2.fiit.stuba.sk/TeamProject/2009/team19is-si/" target="_blank">BugHunters</a> 2009-2010 {SVN_VERSION}
  </div>

</div>
</body>
</html>
<!-- END:PAGE -->
