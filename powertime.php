<?php  
            $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";  
	        //**********************************************************************************************************
            // V2.0 : Script de suivi du temps de fonctionnement d'un appareil, par relevé régulier de l'état (relevé non précis)
            //*************************************** ******************************************************************
            // recuperation des infos depuis la requete
            $api_periph = getArg("api", $mandatory = true, $default = 'undefined');
            $valeurON = getArg("val", $mandatory = true, $default = 100);
            $capteur = getArg("capteur", $mandatory = false, $default = '');
            $api_script = getArg('eedomus_controller_module_id'); 
            $action = getArg("action"); 
 
            $xml .= "<POWERTIME>";
            $maintenant = date("H").":".date("i");
            $xml .= "<APPEL>".$maintenant." ".$api_script."</APPEL>";
 
            	//**********************************************************************************
		// RAZ demandé
		if ($action == 'raz' && $capteur == "jour") {
			$preload = loadVariable('POWERTIME');
			if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {
					$tab_powertime = $preload;
					if (array_key_exists($api_periph, $tab_powertime)) 
					{
						$tab_powertime[$api_periph]['jour'] = 0;
						$tab_powertime[$api_periph]['mois'] = 0;
						$tab_powertime[$api_periph]['annee'] = 0;
						$tab_powertime[$api_periph]['mois_prec'] = 0;
						$tab_powertime[$api_periph]['jour_prec'] = 0;
						$tab_powertime[$api_periph]['annee_prec'] = 0;
						$tab_powertime[$api_periph]['last'] = date('d')."-00:00";
						saveVariable('POWERTIME', $tab_powertime);
					}
            }
	    }
		// Lecture temps de fonctionnement
        if ($action == 'poll' && $api_periph != 'undefined') {
            	$daylast = 0;
            	$monthlast = 0;
				$anneelast = 0;
				$preload = loadVariable('POWERTIME');
				if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {
            	
							$tab_powertime = $preload;
							if (array_key_exists($api_periph, $tab_powertime)) 
							{
								$last = $tab_powertime[$api_periph]['last'];
								$daylast = $tab_powertime[$api_periph]['jour'];
								$monthlast = $tab_powertime[$api_periph]['mois'];
								$anneelast = $tab_powertime[$api_periph]['annee'];
							} 
							else
							{
								// initialisation du nouveau périphérique suivi
								$tab_powertime[$api_periph]['last'] = date('d')."-00:00";
								$tab_powertime[$api_periph]['jour'] = 0;
								$tab_powertime[$api_periph]['mois'] = 0;
								$tab_powertime[$api_periph]['annee'] = 0;
								$tab_powertime[$api_periph]['mois_prec'] = 0;
								$tab_powertime[$api_periph]['jour_prec'] = 0;
								$tab_powertime[$api_periph]['annee_prec'] = 0;
							}
            	}
            	else {
            			// aucune variable suivie, mise à zéro
            			$tab_powertime[$api_periph]['last'] = date('d')."-00:00";
						$tab_powertime[$api_periph]['jour'] = 0;
						$tab_powertime[$api_periph]['mois'] = 0;
						$tab_powertime[$api_periph]['annee'] = 0;
						$tab_powertime[$api_periph]['mois_prec'] = 0;
						$tab_powertime[$api_periph]['jour_prec'] = 0;
						$tab_powertime[$api_periph]['annee_prec'] = 0;
            	}
					
            	$valeurPeriph = getValue($api_periph);
				if ($capteur == "jour") {
            		// dernière mesure
					$lasttime = substr($last, 3, 5);
					$lastday = substr($last, 0, 2);
					$mesureveille = false;
					$razday = false;
					$razmois = false;
					$razannee = false;
					// si dernière mesure veille, dernière mesure à 00:00
					if ($lastday != date('d')) {
						$lasttime = '00:00';
						$mesureveille = true;
						$razday = true;
						$tab_powertime[$api_periph]['jour_prec'] = $tab_powertime[$api_periph]['jour'];
						$tab_powertime[$api_periph]['jour'] = 0;
						$daylast = 0;
						if (date('j') == 1) {
							$razmois = true;
							$tab_powertime[$api_periph]['mois_prec'] = $tab_powertime[$api_periph]['mois'];
							$tab_powertime[$api_periph]['mois'] = 0;
							$monthlast = 0;
						}
						if (date('n') == 1 && $razmois) {
							$razannee = true;
							$tab_powertime[$api_periph]['annee_prec'] = $tab_powertime[$api_periph]['annee'];
							$tab_powertime[$api_periph]['annee'] = 0;
							$anneelast = 0;
						}
					}
					
					if ($valeurPeriph['value'] == $valeurON) {
						// le périph est en fonctionnement, ajout du temps
						// dernier début de fonctionnement
						$lastchangetime = substr($valeurPeriph['change'], 11, 5);
						$lastchangeday = substr($valeurPeriph['change'], 8, 2);
						$jouracheval = false;
						// si début de fonctionne veille, début de fonctionnement à 00:00
						if ($lastchangeday != date('d')) {
							$lastchangetime = '00:00';
							$jouracheval = true;
						}
					
						// voir si la dernière mesure est plus récente que le dernier changement
						$borneinf = $lastchangetime;
						if ($lasttime > $lastchangetime) {
							$borneinf = $lasttime;
						}
						// calcul du temps passé depuis borne inférieure
						$timestamp = mktime(substr($borneinf, 0, 2), substr($borneinf, 3, 2) , 0, date('m'), date('d'), date('Y'));
						$difference = time()-$timestamp;
						$onlymn = floor($difference/60);
						//ajout des minutes calculées
            			$daylast += $onlymn;
            			$monthlast += $onlymn;
						$anneelast += $onlymn;
						
						$tab_powertime[$api_periph]['jour'] = $daylast;
						$tab_powertime[$api_periph]['mois'] = $monthlast;
						$tab_powertime[$api_periph]['annee'] = $anneelast;
						
            		}
					$tab_powertime[$api_periph]['last'] = date('d')."-".$maintenant;
					saveVariable('POWERTIME', $tab_powertime);
            	}	
            		$xml .= "<JOUR>".$tab_powertime[$api_periph]['jour']."</JOUR>";
					$minutes = $tab_powertime[$api_periph]['jour'];
            		$heure = floor($minutes/60);
					$reste2 = ($minutes%60);
					$minute = floor($reste2);
					$xml .= "<JOURLIT>".$heure."h ".$minute."mn</JOURLIT>";
					
            		$xml .= "<JOUR_PREC>".$tab_powertime[$api_periph]['jour_prec']."</JOUR_PREC>";
            		$minutes = $tab_powertime[$api_periph]['jour_prec'];
            		$heure = floor($minutes/60);
					$reste2 = ($minutes%60);
					$minute = floor($reste2);
					$xml .= "<JOURLIT_PREC>".$heure."h ".$minute."mn</JOURLIT_PREC>";
            		
            		$xml .= "<MOIS>".$tab_powertime[$api_periph]['mois']."</MOIS>";
            		$minutes = $tab_powertime[$api_periph]['mois'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<MOISLIT>".$jour."j ".$heure."h ".$minute."mn</MOISLIT>";
					
            		$xml .= "<MOIS_PREC>".$tab_powertime[$api_periph]['mois_prec']."</MOIS_PREC>";
            		$minutes = $tab_powertime[$api_periph]['mois_prec'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<MOISLIT_PREC>".$jour."j ".$heure."h ".$minute."mn</MOISLIT_PREC>";
					
					$xml .= "<ANNEE>".$tab_powertime[$api_periph]['annee']."</ANNEE>";
            		$minutes = $tab_powertime[$api_periph]['annee'];
					$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<ANNEELIT>".$jour."j ".$heure."h ".$minute."mn</ANNEELIT>";
					
            		$xml .= "<ANNEE_PREC>".$tab_powertime[$api_periph]['annee_prec']."</ANNEE_PREC>";
            		$minutes = $tab_powertime[$api_periph]['annee_prec'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<ANNEELIT_PREC>".$jour."j ".$heure."h ".$minute."mn</ANNEELIT_PREC>";
        }
        $xml .= "</POWERTIME>";
		sdk_header('text/xml');
		echo $xml;
?>
