<?php

    require("frim/konfiguration/konfig.php");
	mysql_connect($server, $db_user, $db_pass);
	mysql_select_db($database);
	$Serverar		= date("Y");
	$Servermanad 	= date("m");
	$Serverdag 		= date("d");
	$Serveridag		= mktime(0,0,0,$Servermanad,$Serverdag,$Serverar);

    
    // Starta dokumentet.
    // Skriv ut sidans <HTML> och <HEAD> taggar.
    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
    echo "<html xmlns='http://www.w3.org/1999/xhtml'>";
	echo "<head>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
    echo "<title>Bibliotekssystemet.se</title>";
    echo "<link href='design.css' rel='stylesheet' type='text/css' />";
    echo "</head>";

    // Innehållet...
    echo "<body>";
            
        // Stopp och belägg!
//        echo "<div id='ALLT'><div class='Rubrik'>Bibliotekssystemet.se</div>";
//        echo "<div class='Text'><br />Tills vidare &auml;r den h&auml;r sidan st&auml;ngd av s&auml;kerhetssk&auml;l.<br /></div>";
//        echo "</div></body></html>";
//        exit();
        
    
        // Visa inmatningsrutan om den inte redan är ifylld.
        if (!isset($_POST['nummer'])) {
            echo "<div id='ALLT'><div class='Rubrik'>Bibliotekssystemet.se</div>";
            echo "<div class='Text'><br />Skriv in ditt medlemsnummer:<br /></div>";
    
        	// Öppna ett formulär för kortnumret.
        	echo "<form action='index.php' name='nummer' method='post' enctype='application/form-data'>";
    
        	// Skriv ut en inmatningsruta för medlemsnumret.
        	echo "<input name='nummer' type='text' class='Text' size='30' ><br /><br />";
    
        	// Skriv ut en knapp för att kontrollera lån.
        	echo "<input type='submit' value='Visa p&aring;g&aring;ende l&aring;n' class='Text' style='width:200px'>";
        
        	// Sätt fokus på inmatningsrutan så att markören alltid står där från början.
        	echo "<script type='text/javascript'>document.nummer.nummer.focus()</script>";		
        }
        
        // Visa pågående lån om det finns några.
        if (isset($_POST['nummer'])) {
            
            // Baka en kaka.
            $_SESSION['Visa'] = "Inte";
            
    		// Börja med att fixa till medlemsnumret.
    		$Medlemsnummer = str_replace("-","",$_POST['nummer']);
            
            // Kontrollera att strängen är numerisk och ladda om sidan om den inte är det.
            if (!is_numeric($Medlemsnummer)) header("location:index.php");
            
			// Kontrollera att medlemmen finns registrerad.
            $Hamtamedlem = "SELECT * FROM medlem WHERE MEDLEM = $Medlemsnummer";
            
            // Skicka begäran till databasen. Stoppa och visa felet om något går snett.
    		if (!$result = mysql_query($Hamtamedlem)) {
    			die('Invalid query: ' . mysql_error());
    		}

    		// Ladda om sidan om medlemmen inte finns registrerad.
    		if (!mysql_num_rows($result)) header("location:index.php");
            
            // Kontrollera om det finns några lån registrerade.
            $Kontrolleralan = "SELECT * FROM aktiva WHERE MEDLEM = $Medlemsnummer ORDER BY DATUM DESC";
            $result = mysql_query($Kontrolleralan);
            $Antallan = mysql_num_rows($result);
            
            // Kontrollera om det finns några speciallån registrerade.
            $Kontrolleraspecial = "SELECT * FROM special WHERE SMEDLEM = $Medlemsnummer ORDER BY SDATUM DESC";
            $resultspecial = mysql_query($Kontrolleraspecial);
            $Antalspecial = mysql_num_rows($resultspecial);
            
            // Skriv ut en rubrik och starta en tabell.
            if ($Antallan >= 1 || $Antalspecial >= 1) {
                echo "<div id='RESULTAT'><div class='Rubrik'>Dina p&aring;g&aring;ende l&aring;n:</div>";
                echo "<table cellpadding='5' align='center' border='1' >";
            }
            
            // Skriv ut något om det inte finns några pågående lån.
            $Totaltantal = $Antallan+$Antalspecial;
            if ($Totaltantal == 0) {
                echo "<div id='RESULTAT'><div class='Rubrik'>Inga p&aring;g&aring;ende l&aring;n.</div><br /><br />";
                echo "<table cellpadding='5' align='center' border='0' >";
            }
             
            // Lista eventuellt pågående lån.
            if ($Antallan >= 1) {

   			// Läs data om utlånade böcker i databasen aktiva.
        		while ($row = mysql_fetch_array($result)) {
    
                    // Hämta information om vanliga lån.
                    $LANID			= $row["LANID"];
        			$BOKID			= $row["BOKID"];
        			$TITELID		= $row["TITELID"];
        			$DATUM			= $row["DATUM"];
                
                    // Läs titeln som är kopplad till boken.
                    $Lastitel = "SELECT * FROM litteratur WHERE TITELID = '$TITELID'";
    
        			// Skicka en begäran till databasen, stoppa och visa felet om något går galet.
        			if (!$titelresult = mysql_query($Lastitel)) {
        				die('Invalid query: ' . mysql_error());
        			} 
    
        			// Läs information från databasen litteratur om aktuell titel.
        			while ($row = mysql_fetch_array($titelresult)) {
        				$TITELID		= $row["TITELID"];
        				$TITEL			= $row["TITEL"];
        				$FORFATTARE		= $row["FORFATTARE"];
        				$UTGIVNINGSAR	= $row["UTGIVNINGSAR"];
    
        				// Kontrollera om data saknas och hantera det i så fall.
        				if (empty($TITEL)) $TITEL = "**Titel saknas**";
        				if (empty($FORFATTARE)) $FORFATTARE = "**F&ouml;rfattare saknas**";
        				if (empty($UTGIVNINGSAR)) $UTGIVNINGSAR = "";
        				else $UTGIVNINGSAR = " ($UTGIVNINGSAR)";
    
        				// Läs streckkoden som är kopplad till boken.
        				$Lasstreckkod = "SELECT * FROM bocker WHERE BOKID = '$BOKID'";
    			
        				// Skicka en begäran till databasen, stoppa och visa felet om något går galet.
        				if (!$eanresult = mysql_query($Lasstreckkod)) {
        					die('Invalid query: ' . mysql_error());
        				}
        				
        				// Läs information från databasen bocker om aktuell titel.
        				while ($row = mysql_fetch_array($eanresult)) {
        					$EAN = $row["EAN"];
        				}
                        
                        // Läs data om det utlånande biblioteket.
                        $Visabibliotek = "SELECT * FROM bibliotek WHERE BIBLIOTEKNR=".substr($EAN,0,4)."";
                			
                        // Skicka begäran till databasen. Stoppa och visa felet om något går galet.
                        if (!$resultbibliotek = mysql_query($Visabibliotek)) {
                            die('Invalid query: ' . mysql_error());
                        }
                        
                    	while ($row = mysql_fetch_array($resultbibliotek)) {
                    		$BIBLIOTEK	= $row["BIBLIOTEKNAMN"];
                	    }
  
        				// Spara uppgiften/uppgifterna i en sträng.
               			$Registreradelan = $Registreradelan."<tr><td width='5%'><img src='http://barcode.tec-it.com/barcode.ashx?code=Code93&modulewidth=fit&data=$EAN&dpi=96&imagetype=png&rotation=0' alt='Barcode generated by TEC-IT'/></td>";

        				$Registreradelan = "$Registreradelan<td align='left' valign='top' class='Text'><b>&ldquo;$TITEL&rdquo;</b>$UTGIVNINGSAR<br />F&ouml;rfattare: $FORFATTARE<br /><br />Utl&aring;nad: ".date('Y-m-d',$DATUM)." av biblioteket i $BIBLIOTEK.";
    
        				$Tillbakadatum = $DATUM+2592000;
        				if ($Tillbakadatum >= $Serveridag) $Registreradelan = $Registreradelan."<br /><font color='green'><b>Skall vara tillbaka senast: ".date('Y-m-d', $Tillbakadatum)."</b></font></td></tr>";
        				else $Registreradelan = $Registreradelan."<br /><font color='red'><b>Skulle vara tillbakal&auml;mnad: ".date('Y-m-d', $Tillbakadatum)."</b></font></td></tr>";
                    }
                }
            }

            // Lista eventuellt pågående speciallån.
            if ($Antalspecial >= 1) {

    			// Läs data om utlånade böcker i databasen aktiva.
        		while ($row = mysql_fetch_array($resultspecial)) {
    
                    // Hämta information om vanliga lån.
                    $LANID			= $row["SLANID"];
        			$BOKID			= $row["SBOKID"];
        			$TITELID		= $row["STITELID"];
        			$DATUM			= $row["SDATUM"];
                
                    // Läs titeln som är kopplad till boken.
                    $Lastitel = "SELECT * FROM litteratur WHERE TITELID = '$TITELID'";
    
        			// Skicka en begäran till databasen, stoppa och visa felet om något går galet.
        			if (!$titelresult = mysql_query($Lastitel)) {
        				die('Invalid query: ' . mysql_error());
        			} 
    
        			// Läs information från databasen litteratur om aktuell titel.
        			while ($row = mysql_fetch_array($titelresult)) {
        				$TITELID		= $row["TITELID"];
        				$TITEL			= $row["TITEL"];
        				$FORFATTARE		= $row["FORFATTARE"];
        				$UTGIVNINGSAR	= $row["UTGIVNINGSAR"];
    
        				// Kontrollera om data saknas och hantera det i så fall.
        				if (empty($TITEL)) $TITEL = "**Titel saknas**";
        				if (empty($FORFATTARE)) $FORFATTARE = "**F&ouml;rfattare saknas**";
        				if (empty($UTGIVNINGSAR)) $UTGIVNINGSAR = "";
        				else $UTGIVNINGSAR = " ($UTGIVNINGSAR)";
    
        				// Läs streckkoden som är kopplad till boken.
        				$Lasstreckkod = "SELECT * FROM bocker WHERE BOKID = '$BOKID'";
    			
        				// Skicka en begäran till databasen, stoppa och visa felet om något går galet.
        				if (!$eanresult = mysql_query($Lasstreckkod)) {
        					die('Invalid query: ' . mysql_error());
        				}
        				
        				// Läs information från databasen bocker om aktuell titel.
        				while ($row = mysql_fetch_array($eanresult)) {
        					$EAN = $row["EAN"];
        				}
                        
                        // Läs data om det utlånande biblioteket.
                        $Visabibliotek = "SELECT * FROM bibliotek WHERE BIBLIOTEKNR=".substr($EAN,0,4)."";
                			
                        // Skicka begäran till databasen. Stoppa och visa felet om något går galet.
                        if (!$resultbibliotek = mysql_query($Visabibliotek)) {
                            die('Invalid query: ' . mysql_error());
                        }
                        
                    	while ($row = mysql_fetch_array($resultbibliotek)) {
                    		$BIBLIOTEK	= $row["BIBLIOTEKNAMN"];
                	    }
  
        				// Spara uppgiften/uppgifterna i en sträng.
               			$Registreradelan = $Registreradelan."<tr><td width='5%'><img src='http://barcode.tec-it.com/barcode.ashx?code=Code93&modulewidth=fit&data=$EAN&dpi=96&imagetype=png&rotation=0' alt='Barcode generated by TEC-IT'/></td>";

        				$Registreradelan = "$Registreradelan<td align='left' valign='top' class='Text'><b>&ldquo;$TITEL&rdquo;</b>$UTGIVNINGSAR<br />F&ouml;rfattare: $FORFATTARE<br /><br />Utl&aring;nad: ".date('Y-m-d',$DATUM)." av biblioteket i $BIBLIOTEK.";
    
        				$Registreradelan = $Registreradelan."<br /><font color='blue'><b>Speciall&aring;n</b></font></td></tr>";
                    }
                }
            }
            
        // Visa tidigare lån om det finns några.
        $Kontrolleragamla = "SELECT * FROM gamlalan WHERE MEDLEM = $Medlemsnummer ORDER BY BIBLIOTEK DESC";
        $result = mysql_query($Kontrolleragamla);
        $Antalgamla = mysql_num_rows($result);
            
        // Skriv ut en rubrik och starta en tabell.
        if ($Antalgamla >= 1) {
            $Gamlalan = "<br /><div class='Rubrik'>Dina tidigare l&aring;n:</div>";
            $Gamlalan = "$Gamlalan<table cellpadding='5' align='center' border='1' >";
            
   			// Läs data om utlånade böcker i databasen gamlalan.
        	while ($row = mysql_fetch_array($result)) {
    
                // Hämta information om vanliga lån.
        		$TITELID		= $row["TITELID"];
        		$DATUM			= $row["BIBLIOTEK"];
        		$BIBLIOTEK		= $row["DATUM"];
                
                // Läs titeln som är kopplad till boken.
                $Lastitel = "SELECT * FROM litteratur WHERE TITELID = '$TITELID'";
    
        		// Skicka en begäran till databasen, stoppa och visa felet om något går galet.
        		if (!$titelresult = mysql_query($Lastitel)) {
        			die('Invalid query: ' . mysql_error());
        		} 
    
        		// Läs information från databasen litteratur om aktuell titel.
        		while ($row = mysql_fetch_array($titelresult)) {
        			$TITELID		= $row["TITELID"];
        			$TITEL			= $row["TITEL"];
        			$FORFATTARE		= $row["FORFATTARE"];
        			$UTGIVNINGSAR	= $row["UTGIVNINGSAR"];
    
        			// Kontrollera om data saknas och hantera det i så fall.
        			if (empty($TITEL)) $TITEL = "**Titel saknas**";
        			if (empty($FORFATTARE)) $FORFATTARE = "**F&ouml;rfattare saknas**";
        			if (empty($UTGIVNINGSAR)) $UTGIVNINGSAR = "";
        			else $UTGIVNINGSAR = " ($UTGIVNINGSAR)";
                }
                    
                // Läs data om det utlånande biblioteket.
                $Visabibliotek = "SELECT * FROM bibliotek WHERE BIBLIOTEKNR=$BIBLIOTEK";
                			
                // Skicka begäran till databasen. Stoppa och visa felet om något går galet.
                if (!$resultbibliotek = mysql_query($Visabibliotek)) {
                    die('Invalid query: ' . mysql_error());
                }
                        
                while ($row = mysql_fetch_array($resultbibliotek)) {
                  	$BIBLIOTEK	= $row["BIBLIOTEKNAMN"];
                }
                    
                // Spara uppgiften/uppgifterna i en sträng.
       			$Gamlalan = "$Gamlalan<tr><td align='left' valign='top' class='Text'><b>&ldquo;$TITEL&rdquo;</b>$UTGIVNINGSAR<br />F&ouml;rfattare: $FORFATTARE<br /><br />Utl&aring;nad ".date('Y-m-d',$DATUM)." av biblioteket i $BIBLIOTEK.</td></tr>";
            }
            $Gamlalan = "$Gamlalan</table>";
        }
       
        // Stäng tabellen i strängen.
      	$Registreradelan = "$Registreradelan</table>";
        echo $Registreradelan;
        echo $Gamlalan;
        

       	// Öppna ett formulär för kortnumret.
       	echo "<form action='index.php' name='reset' method='post' enctype='application/form-data'>";
    
        // Skriv ut en knapp för att kontrollera lån.
        echo "<br /><input type='submit' value='&Aring;terg&aring;' class='Text' style='width:200px'>";
        
    } 

    // Kontrollera om det är läge att ladda om sidan.
    if(isset($_POST['reset'])) {
        header("location:" . $_SERVER['PHP_SELF']);
    }
            
    // Avsluta dokumentet.
    echo "</div></body></html>";
?>