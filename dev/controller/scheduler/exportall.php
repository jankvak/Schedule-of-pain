<?php
class ExportallController extends AppController {

	var $access = array('Scheduler', 'Admin');
	
	private $prequirements;
	private $crequirements;
	
	function __construct() {
        parent::__construct();
       	$this->prequirements = new TeacherRequirements();
		$this->crequirements = new PractRequirements();
    }
	
	function index() {
		$semesterID = $this->session->read("semester");
		$this->set("poziadavky", $this->prequirements->getAllCoursesLastRequests($semesterID));
	}
	
	
	//vytvori strukturu word dokumentu konkretnemu predmetu podla id a vlozi donho zadane poziadavky
	private function __createdocstruct($id_predmet){
		
		//vytiahni data o prednaskach a cvikach predmetu
		$metaPoz = $this->prequirements->getLastRequest($id_predmet);
		$metaPoz2 = $this->crequirements->getLastRequest($id_predmet);
		if (!empty($metaPoz)) $req = $this->prequirements->load($metaPoz["id"]);
		if (!empty($metaPoz2)) $req2 = $this->crequirements->load($metaPoz2["id"]);
		
		$subjects = new Subjects();
        $roomz = new Rooms();
		
		$subject = $subjects->getSubject($id_predmet);
		$student_count = $subjects->getStudentCount($id_predmet);
		$student_count_info = $subjects->getStudentCountInfo($id_predmet);
		$rooms=$roomz->getAll();
		$types=$roomz->getTypes();
		
		$rooms_nazvy = array();
		foreach ($rooms as $room)
		{
			$rooms_nazvy[$room["id"]] = $room["nazov"]; 
		}
		$rooms_types = array();
		foreach ($types as $type)
		{
			$rooms_types[$type["id"]] = $type["nazov"]; 
		}
		
		
		//nahadz vytiahnute data do struktury dokumentu
		
		//////////////////////////////////////////////////////////////////////
		///////////////////////////////prednasky
		//////////////////////////////////////////////////////////////////////
		$i=0;
		$doc.='<p class="normalText" style="text-align: center;font-weight: bold;font-size: 24pt;">'.$subject["nazov"].'</p>
		<br>
		<p class="normalText" style="font-weight: bold;font-size: 20pt;">Prednášky</p>
		<table class="normalTable" style="" border="0" cellspacing="0" cellpadding="0">
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="120" align="left" valign="top" style=""><b>Čas zadania:</b></td>
		<td width="130" align="left" valign="top" style="">'.$metaPoz['cas_pridania'].'</td>
		<td width="80" align="left" valign="top" style=""><b>Zadal:</b></td>
		<td width="270" align="left" valign="top" style="">'.$metaPoz['pedagog'].'</td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="250" align="left" valign="top" style="" colspan="2"><b>Odbor</b></td>
		<td width="80" align="left" valign="top" style="" colspan="1"><b>Ročník</b></td>
		<td width="270" align="left" valign="top" style="" colspan="1"><b>Počet študentov</b></td>
		</tr>';
		
		for ($j=0;$j<count($student_count_info);$j++)
		{
			$doc.='<tr style="mso-yfti-irow: '.$i++.'">
			<td width="250" align="left" valign="top" style="" colspan="2">'.$student_count_info[$j]['nazov'].'</td>
			<td width="80" align="left" valign="top" style="" colspan="1">'.$student_count_info[$j]['rocnik'].'.</td>
			<td width="270" align="left" valign="top" style="" colspan="1">'.$student_count_info[$j]['student_count'].'</td>
			</tr>';
		}
		
		$doc.='<tr style="mso-yfti-irow: '.$i++.'">
		<td width="330" align="left" valign="top" style="" colspan="3"><b>Celkový počet študentov</b></td>
		<td width="270" align="left" valign="top" style="" colspan="1"><b>'.$student_count['count'].'</b></td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="600" align="left" valign="top" style="" colspan="4"><b>Poznámka k požiadavke</b></td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="600" align="left" valign="top" style="" colspan="4">'.($req["requirement"]["komentare"]["vseobecne"]?$req["requirement"]["komentare"]["vseobecne"]:"&nbsp").'</td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="600" align="left" valign="top" style="" colspan="4"><b>Požiadavka na softvér</b></td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="600" align="left" valign="top" style="" colspan="4">'.($req["requirement"]["komentare"]["sw"]?$req["requirement"]["komentare"]["sw"]:"&nbsp").'</td>
		</tr>
		</table><br>'	;
		if(sizeof($req["requirement"]) < 1) $doc.= '<p class="normalText">Požiadavky na prednášky neboli zadané.</p>';
		else {
			$layind="a";
			for ($j=1;$j<=sizeof($req["requirement"]["layouts"]);$j++){
				$i=0;
				$doc.='<table class="normalTable" style="" border="0" cellspacing="0" cellpadding="0">
				<tr style="mso-yfti-irow: '.$i++.'">
					<td width="600px" align="left" valign="top" style="" colspan="6"><b><big>Rozloženie '.$j.'</big></b></td>
				</tr>
				<tr style="mso-yfti-irow: '.$i++.'">
					<td width="210px" align="left" valign="top" style="" colspan="2"><b>Počet prednášok v týždni: </b>'.$req["requirement"]["layouts"][$layind]["lecture_count"].'</td>
					<td width="50px" align="left" valign="top" style="" colspan="1"><b>Týždne</b></td>
					<td width="340px" align="left" valign="top" style="" colspan="3">';
					for ($k=0;$k<13;$k++) if($req["requirement"]["layouts"][$layind]["weeks"][$k]) $doc.=($k+1).', ';
					$doc.='</td>
				</tr>';
				for ($k=1;$k<=$req["requirement"]["layouts"][$layind]["lecture_count"];$k++){
					$doc.='<tr style="mso-yfti-irow: '.$i++.'">
						<td width="600px" align="left" valign="top" style="" colspan="6"><b><i>Prednáška '.$k.'</i></b></td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="160px" align="left" valign="top" style="" colspan="1"><b>Rozsah:</b></td>
						<td width="50px" align="left" valign="top" style="" colspan="1">'.$req["requirement"]["layouts"][$layind]["requirement"][$k]["lecture_hours"].'</td>
						<td width="120px" align="left" valign="top" style="" colspan="1"><b>Študentov:</b></td>
						<td width="50px" align="left" valign="top" style="" colspan="1">'.$req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["students_count"].'</td>
						<td width="170px" align="left" valign="top" style="" colspan="1"><b>Stoličky naviac:</b></td>
						<td width="50px" align="left" valign="top" style="" colspan="1">'.$req["requirement"]["layouts"][$layind]["requirement"][$k]["equipment"]["chair_count"].'</td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="160px" align="left" valign="top" style="" colspan="1"><b>Kapacita miestnosti:</b></td>
						<td width="50px" align="left" valign="top" style="" colspan="1">'.$req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["capacity"].'</td>
						<td width="170px" align="left" valign="top" style="" colspan="2"><b>Vyhovujúce miestnosti:</b></td>
						<td width="220px" align="left" valign="top" style="" colspan="2">';
						$sel_rooms = array();
						foreach ($req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["selected"] as $sel_room) $sel_rooms[] = $rooms_nazvy[$sel_room];					
						$doc.=($sel_rooms?implode(", ", $sel_rooms):"&nbsp");
						$doc.='</td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="210px" align="left" valign="top" style="" colspan="2">Notebook:</td>
						<td width="120px" align="left" valign="top" style="" colspan="1">'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["equipment"]["notebook"]?"Áno":"Nie").'</td>
						<td width="220px" align="left" valign="top" style="" colspan="2">Projektor:</td>
						<td width="50px" align="left" valign="top" style="" colspan="1">'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["equipment"]["beamer"]?"Áno":"Nie").'</td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="210px" align="left" valign="top" style="" colspan="2">Cvičenie hneď po prednáške:</td>
						<td width="120px" align="left" valign="top" style="" colspan="1">'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["after_lecture"]?"Áno":"Nie").'</td>
						<td width="220px" align="left" valign="top" style="" colspan="2">Cvičenie nie skôr ako prednáška:</td>
						<td width="50px" align="left" valign="top" style="" colspan="1">'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["before_lecture"]?"Áno":"Nie").'</td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="600px" align="left" valign="top" style="" colspan="6">Poznámka k prednáške</td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="600px" align="left" valign="top" style="" colspan="6">'.
						($req["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]?$req["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]:"&nbsp")
						.'</td>
					</tr>';
				}
				$doc.='</table><br>';
				$layind = chr(ord($layind)+1);
			}
		}
		
		//////////////////////////////////////////////////////////////////////
		///////////////////////////////cvicenia
		//////////////////////////////////////////////////////////////////////
		
		$doc.='<br clear="all" style="page-break-before: always;">
		<p class="normalText" style="text-align: center;font-weight: bold;font-size: 24pt;">'.$subject["nazov"].'</p>
		<br>
		<p class="normalText" style="font-weight: bold;font-size: 20pt;">Cvičenia</p>
		<table class="normalTable" style="" border="0" cellspacing="0" cellpadding="0">
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="148" align="left" valign="top" style=""><b>Čas zadania:</b></td>
		<td width="148" align="left" valign="top" style="">'.$metaPoz2['cas_pridania'].'</td>
		<td width="148" align="left" valign="top" style=""><b>Zadal:</b></td>
		<td width="148" align="left" valign="top" style="">'.$metaPoz2['pedagog'].'</td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="296" align="left" valign="top" style="" colspan="2"><b>Odbor</b></td>
		<td width="148" align="left" valign="top" style="" colspan="1"><b>Ročník</b></td>
		<td width="148" align="left" valign="top" style="" colspan="1"><b>Počet študentov</b></td>
		</tr>';
		
		for ($j=0;$j<count($student_count_info);$j++)
		{
			$doc.='<tr style="mso-yfti-irow: '.$i++.'">
			<td width="296" align="left" valign="top" style="" colspan="2">'.$student_count_info[$j]['nazov'].'</td>
			<td width="148" align="left" valign="top" style="" colspan="1">'.$student_count_info[$j]['rocnik'].'</td>
			<td width="148" align="left" valign="top" style="" colspan="1">'.$student_count_info[$j]['student_count'].'</td>
			</tr>';
		}
		
		$doc.='<tr style="mso-yfti-irow: '.$i++.'">
		<td width="447" align="left" valign="top" style="" colspan="3"><b>Celkový počet študentov</b></td>
		<td width="148" align="left" valign="top" style="" colspan="1"><b>'.$student_count['count'].'</b></td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="595" align="left" valign="top" style="" colspan="4"><b>Poznámka k požiadavke</b></td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="595" align="left" valign="top" style="" colspan="4">'.($req2["requirement"]["komentare"]["vseobecne"]?$req2["requirement"]["komentare"]["vseobecne"]:"&nbsp").'</td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="595" align="left" valign="top" style="" colspan="4"><b>Požiadavka na softvér</b></td>
		</tr>
		<tr style="mso-yfti-irow: '.$i++.'">
		<td width="595" align="left" valign="top" style="" colspan="4">'.($req2["requirement"]["komentare"]["sw"]?$req2["requirement"]["komentare"]["sw"]:"&nbsp").'</td>
		</tr>
		</table><br>';
		if(sizeof($req2["requirement"]) < 1) $doc.= '<p class="normalText">Požiadavky na cvičenia neboli zadané.</p>';
		else {
			$layind="a";
			for ($j=1;$j<=sizeof($req2["requirement"]["layouts"]);$j++){
				$i=0;
				$doc.='<table class="normalTable" style="" border="0" cellspacing="0" cellpadding="0">
				<tr style="mso-yfti-irow: '.$i++.'">
					<td width="600px" align="left" valign="top" style="" colspan="6"><b><big>Rozloženie '.$j.'</big></b></td>
				</tr>
				<tr style="mso-yfti-irow: '.$i++.'">
					<td width="210px" align="left" valign="top" style="" colspan="2"><b>Počet cvičení v týždni: </b>'.$req2["requirement"]["layouts"][$layind]["pract_count"].'</td>
					<td width="65px" align="left" valign="top" style="" colspan="1"><b>Týždne</b></td>
					<td width="325px" align="left" valign="top" style="" colspan="3">';
					for ($k=0;$k<13;$k++) if($req2["requirement"]["layouts"][$layind]["weeks"][$k]) $doc.=($k+1).', ';
					$doc.='</td>
				</tr>';
				for ($k=1;$k<=$req2["requirement"]["layouts"][$layind]["pract_count"];$k++){
					$doc.='<tr style="mso-yfti-irow: '.$i++.'">
						<td width="600px" align="left" valign="top" style="" colspan="6"><b><i>Cvičenie '.$k.'</i></b></td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="135px" align="left" valign="top" style="" colspan="1"><b>Rozsah:</b></td>
						<td width="75px" align="left" valign="top" style="" colspan="1">'.$req2["requirement"]["layouts"][$layind]["requirement"][$k]["pract_hours"].'</td>
						<td width="300px" align="left" valign="top" style="" colspan="3"><b>Maximálny počet cvičení súčasne:</b></td>
						<td width="90px" align="left" valign="top" style="" colspan="1">'.$req2["requirement"]["layouts"][$layind]["requirement"][$k]["pract_paralell"].'</td>
					</tr>';
					for ($l=1;$l<=count($req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]);$l++){
						$doc.='<tr style="mso-yfti-irow: '.$i++.'">
							<td width="600px" align="left" valign="top" style="" colspan="6"><i>Typ skupiny '.$l.'</i></td>
						</tr>
						<tr style="mso-yfti-irow: '.$i++.'">
							<td width="210px" align="left" valign="top" style="" colspan="2"><b>Študentov v skupine:</b></td>
							<td width="65px" align="left" valign="top" style="" colspan="1">'.$req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["students_count"].'</td>
							<td width="235px" align="left" valign="top" style="" colspan="2"><b>Typ miestnosti:</b></td>
							<td width="90px" align="left" valign="top" style="" colspan="1">'.$rooms_types[$req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["type"]].'</td>
						</tr>
						<tr style="mso-yfti-irow: '.$i++.'">
							<td width="135px" align="left" valign="top" style="" colspan="1"><b>Kapacita miestnosti:</b></td>
							<td width="75px" align="left" valign="top" style="" colspan="1">'.$req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["capacity"].'</td>
							<td width="135px" align="left" valign="top" style="" colspan="2"><b>Vyhovujúce miestnosti:</b></td>
							<td width="260px" align="left" valign="top" style="" colspan="2">';
							$sel_rooms2 = array();
							foreach ($req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["selected"] as $sel_room) $sel_rooms2[] = $rooms_nazvy[$sel_room];					
							$doc.=($sel_rooms2?implode(", ", $sel_rooms2):"&nbsp");
							$doc.='</td>';
					}
					$doc.='<tr style="mso-yfti-irow: '.$i++.'">
						<td width="600px" align="left" valign="top" style="" colspan="6">Poznámka k cvičeniu</td>
					</tr>
					<tr style="mso-yfti-irow: '.$i++.'">
						<td width="600px" align="left" valign="top" style="" colspan="6">'.
						($req2["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]?$req2["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]:"&nbsp")
						.'</td>
					</tr>';
				}
				$doc.='</table><br>';
				$layind = chr(ord($layind)+1);
			}
		}
		return $doc;
	}
	
	function createdoc($id_predmet){
		//vytvor hlavicku dokumentu a vloz poziadavky konkretneho predmetu podla id
		$doc='<html xmlns:o="urn:schemas-microsoft-com:office:office"
		   xmlns:w="urn:schemas-microsoft-com:office:word"
		   xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="ProgId" content="Word.Document">
		<meta name="Generator" content="Systém pre podporu tvorby rozvrhov">
		<meta name="Originator" content="Systém pre podporu tvorby rozvrhov">
		<style>
		@page Section1
		   {size: 595.35pt 841.995pt;
		   mso-page-orientation: portrait;
		   margin: 3cm 2.5cm 3cm 2.5cm;
		   mso-header-margin: 36pt;
		   mso-footer-margin: 36pt;
		   mso-paper-source: 0;}
		div.Section1
		  {page: Section1;}

		p.normalText, li.normalText, div.normalText{
		   mso-style-parent: "";
		   margin: 0cm;
		   margin-bottom: 6pt;
		   mso-pagination: widow-orphan;
		   font-size: 12pt;
		   font-family: "Times New Roman";
		   mso-fareast-font-family: "Times New Roman";
		}

		table.normalTable{
		   mso-style-name: "Tabela com grade";
		   mso-tstyle-rowband-size: 0;
		   mso-tstyle-colband-size: 0;
		   border-collapse: collapse;
		   mso-border-alt: solid windowtext 0.5pt;
		   mso-yfti-tbllook: 480;
		   mso-padding-alt: 0cm 5px 0cm 5px;
		   mso-border-insideh: 1px solid windowtext;
		   mso-border-insidev: 1px solid windowtext;
		   mso-para-margin: 0cm;
		   mso-para-margin-bottom: 0pt;
		   mso-pagination: widow-orphan;
		   font-size: 12pt;
		   font-family: "Times New Roman";
		   width: 600px;
		}
		table.normalTable td{
		   border: solid windowtext 1px;
		   border-left: none;
		   mso-border-left-alt: solid windowtext .5pt;
		   mso-border-alt: solid windowtext .5pt;
		   padding: 0cm 5px 0cm 5px;
		}
		</style>
		</head>
		<body lang="SK" style="tab-interval: 35.4pt">
		<div class="Section1">';
		$doc.=$this->__createdocstruct($id_predmet);
		$doc.='</div>
		</body>
		</html>';
		$subjects = new Subjects();
		$subject = $subjects->getSubject($id_predmet);
		$filename='"export-'.$subject["kod"].'.doc"';
		header('Content-Type: application/msword; charset="utf-8"');
		header('Content-Disposition: attachment; filename='.$filename);
		echo $doc;
		die();
	}
	
	function createdocall(){
		//vytvor hlavicku dokumentu a vloz poziadavky vsetkych predmetov
		
		$doc='<html xmlns:o="urn:schemas-microsoft-com:office:office"
		   xmlns:w="urn:schemas-microsoft-com:office:word"
		   xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="ProgId" content="Word.Document">
		<meta name="Generator" content="Systém pre podporu tvorby rozvrhov">
		<meta name="Originator" content="Systém pre podporu tvorby rozvrhov">
		<style>
		@page Section1
		   {size: 595.35pt 841.995pt;
		   mso-page-orientation: portrait;
		   margin: 3cm 2.5cm 3cm 2.5cm;
		   mso-header-margin: 36pt;
		   mso-footer-margin: 36pt;
		   mso-paper-source: 0;}
		div.Section1
		  {page: Section1;}

		p.normalText, li.normalText, div.normalText{
		   mso-style-parent: "";
		   margin: 0cm;
		   margin-bottom: 6pt;
		   mso-pagination: widow-orphan;
		   font-size: 12pt;
		   font-family: "Times New Roman";
		   mso-fareast-font-family: "Times New Roman";
		}

		table.normalTable{
		   mso-style-name: "Tabela com grade";
		   mso-tstyle-rowband-size: 0;
		   mso-tstyle-colband-size: 0;
		   border-collapse: collapse;
		   mso-border-alt: solid windowtext 0.5pt;
		   mso-yfti-tbllook: 480;
		   mso-padding-alt: 0cm 5px 0cm 5px;
		   mso-border-insideh: 1px solid windowtext;
		   mso-border-insidev: 1px solid windowtext;
		   mso-para-margin: 0cm;
		   mso-para-margin-bottom: 0pt;
		   mso-pagination: widow-orphan;
		   font-size: 12pt;
		   font-family: "Times New Roman";
		   width: 600px;
		}
		table.normalTable td{
		   border: solid windowtext 1px;
		   border-left: none;
		   mso-border-left-alt: solid windowtext .5pt;
		   mso-border-alt: solid windowtext .5pt;
		   padding: 0cm 5px 0cm 5px;
		}
		</style>
		</head>
		<body lang="SK" style="tab-interval: 35.4pt">
		<div class="Section1">';
		$semesterID = $this->session->read("semester");
		$requirements=$this->prequirements->getAllCoursesLastRequests($semesterID);
		foreach ($requirements as $req)
		{
			$doc.=$this->__createdocstruct($req['id']);
			$doc.='<br clear="all" style="page-break-before: always;">';
		}
		$doc.='</div>
		</body>
		</html>';
		header('Content-Type: application/msword; charset="utf-8"');
		header('Content-Disposition: attachment; filename="export-all.doc"');
		echo $doc;
		die();
	}
	
	
	//vytvori jeden riadok csv obsahujuci poziadavky na konkretny predmet podla id
	//struktura csv riadku:
	//nazovpredm,pocstudentov,poznamkapred,softpred,rozlozeniepred_x(prednasoktyzdenne,pretyzdne,prednaska_x(rozsah,
	//pocstudentov,stolickynaviac,kapacita,vyhovujucemiestn,notebook,projektor,poprednaske,nieskorakopredn,pozn)),
	//poznamkacv,softcv,rozlozeniecv_x(cvicenityzdenne,pretyzdne,cvicenie_x(rozsah,maxsucasne,skupina_y(studentov,
	//typmiestnosti,kapacita,vyhovujucemiestn,pozn)))
	//kde _x=1..3, _y=1..2
	//pokial nie je niektora cela poziadavka, niektore rozlozenie alebo poznamka zadana, vklada sa na prislusne 
	//miesto NULL resp. false (iba pri notebook,projektor,poprednaske,nieskorakopredn)
	private function __createcsvrow($id_predmet){
	
		//vytiahni data o prednaskach a cvikach predmetu
		$metaPoz = $this->prequirements->getLastRequest($id_predmet);
		$metaPoz2 = $this->crequirements->getLastRequest($id_predmet);
		if (!empty($metaPoz)) $req = $this->prequirements->load($metaPoz["id"]);
		if (!empty($metaPoz2)) $req2 = $this->crequirements->load($metaPoz2["id"]);
		
		$subjects = new Subjects();
        $roomz = new Rooms();
		
		$subject = $subjects->getSubject($id_predmet);
		$student_count = $subjects->getStudentCount($id_predmet);
		$student_count_info = $subjects->getStudentCountInfo($id_predmet);
		$rooms=$roomz->getAll();
		$types=$roomz->getTypes();
		
		$rooms_nazvy = array();
		foreach ($rooms as $room)
		{
			$rooms_nazvy[$room["id"]] = $room["nazov"]; 
		}
		$rooms_types = array();
		foreach ($types as $type)
		{
			$rooms_types[$type["id"]] = $type["nazov"]; 
		}
		

		//nahadz vytiahnute data do struktury csv
		
		$csvrow.='"'.$subject["nazov"].'"';
		$csvrow.=';"'.$student_count['count'].'"';
		$csvrow.=';"'.($req["requirement"]["komentare"]["vseobecne"]?str_replace(array("\n","\r")," ",$req["requirement"]["komentare"]["vseobecne"]):"NULL").'"';
		$csvrow.=';"'.($req["requirement"]["komentare"]["sw"]?str_replace(array("\n","\r")," ",$req["requirement"]["komentare"]["sw"]):"NULL").'"';
		$layind="a";
		for ($j=1;$j<=3;$j++){
			$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["lecture_count"]?$req["requirement"]["layouts"][$layind]["lecture_count"]:"NULL").'"';
			$tmp='';
			for ($k=0;$k<13;$k++){
				if($req["requirement"]["layouts"][$layind]["weeks"][$k]) $tmp.=($k+1).' ';
				
			}
			$csvrow.=';"'.($tmp?$tmp:"NULL").'"';
			for ($k=1;$k<=3;$k++){
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["lecture_hours"]?$req["requirement"]["layouts"][$layind]["requirement"][$k]["lecture_hours"]:"NULL").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["students_count"]?$req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["students_count"]:"NULL").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["equipment"]["chair_count"]?$req["requirement"]["layouts"][$layind]["requirement"][$k]["equipment"]["chair_count"]:"NULL").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["capacity"]?$req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["capacity"]:"NULL").'"';
				$sel_rooms = array();
				foreach ($req["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"]["selected"] as $sel_room) $sel_rooms[] = $rooms_nazvy[$sel_room];					
				$csvrow.=';"'.($sel_rooms?implode(" ", $sel_rooms):"NULL").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["equipment"]["notebook"]?"true":"false").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["equipment"]["beamer"]?"true":"false").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["after_lecture"]?"true":"false").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["before_lecture"]?"true":"false").'"';
				$csvrow.=';"'.($req["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]?str_replace(array("\n","\r")," ",$req["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]):"NULL").'"';
				
			}
			$layind = chr(ord($layind)+1);
		}
		
		$csvrow.=';"'.($req2["requirement"]["komentare"]["vseobecne"]?str_replace(array("\n","\r")," ",$req2["requirement"]["komentare"]["vseobecne"]):"NULL").'"';
		$csvrow.=';"'.($req2["requirement"]["komentare"]["sw"]?str_replace(array("\n","\r")," ",$req2["requirement"]["komentare"]["sw"]):"NULL").'"';
		$layind="a";
		for ($j=1;$j<=3;$j++){
			$csvrow.=';"'.($req2["requirement"]["layouts"][$layind]["pract_count"]?$req2["requirement"]["layouts"][$layind]["pract_count"]:"NULL").'"';
			$tmp='';
			for ($k=0;$k<13;$k++) if($req2["requirement"]["layouts"][$layind]["weeks"][$k]) $tmp.=($k+1).' ';
			$csvrow.=';"'.($tmp?$tmp:"NULL").'"';
			for ($k=1;$k<=3;$k++){
				$csvrow.=';"'.($req2["requirement"]["layouts"][$layind]["requirement"][$k]["pract_hours"]?$req2["requirement"]["layouts"][$layind]["requirement"][$k]["pract_hours"]:"NULL").'"';
				$csvrow.=';"'.($req2["requirement"]["layouts"][$layind]["requirement"][$k]["pract_paralell"]?$req2["requirement"]["layouts"][$layind]["requirement"][$k]["pract_paralell"]:"NULL").'"';
				for ($l=1;$l<=2;$l++){
					$csvrow.=';"'.($req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["students_count"]?$req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["students_count"]:"NULL").'"';
					$csvrow.=';"'.($req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["type"]?$rooms_types[$req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["type"]]:"NULL").'"';
					$csvrow.=';"'.($req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["capacity"]?$req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["capacity"]:"NULL").'"';
					$sel_rooms2 = array();
					foreach ($req2["requirement"]["layouts"][$layind]["requirement"][$k]["rooms"][$l]["selected"] as $sel_room) $sel_rooms2[] = $rooms_nazvy[$sel_room];					
					$csvrow.=';"'.($sel_rooms2?implode(" ", $sel_rooms2):"NULL").'"';
					$csvrow.=';"'.($req2["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]?str_replace(array("\n","\r")," ",$req2["requirement"]["layouts"][$layind]["requirement"][$k]["comment"]):"NULL").'"';
				}
			}
			$layind = chr(ord($layind)+1);
		}
		$csvrow.="\n";
		return $csvrow;	
	}
	
	//vytvori csv subor s poziadavkami vsetkych predmetov
	function createcsv(){
		$csv='';
		$semesterID = $this->session->read("semester");
		$requirements=$this->prequirements->getAllCoursesLastRequests($semesterID);
		foreach ($requirements as $req)
		{
			$csv.=$this->__createcsvrow($req['id']);
		}
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-type: text/comma-separated-values");
		header('Content-Disposition: attachment; filename="export-all.csv"');
		echo $csv;
		die();
	}
}
?>