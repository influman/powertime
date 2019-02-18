<?php  
            $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";  
	        //**********************************************************************************************************
            // V2.41 : Script de suivi du temps de fonctionnement d'un appareil, par relevé régulier de l'état (relevé non précis)
            //*************************************** ******************************************************************
            // recuperation des infos depuis la requete
            $api_periph = getArg("api", true, 'undefined');
            $valeurON = getArg("val", true, 100);
            $capteur = getArg("capteur", false, '');
			$option = getArg("option", false, '');
            $api_script = getArg('eedomus_controller_module_id'); 
            $action = getArg("action"); 
 
            $xml .= "<POWERTIME>";
            $maintenant = date("H").":".date("i");
            $xml .= "<APPEL>".$maintenant." ".$api_script."</APPEL>";
 
            	//**********************************************************************************
		// RAZ demandé
		if ($action == 'raz' && $capteur == "jour") {
			$preload = loadVariable('POWERTIME_'.$api_periph);
			if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {
					$tab_powertime = $preload;
					$tab_powertime['jour'] = 0;
					$tab_powertime['mois'] = 0;
					$tab_powertime['semaine'] = 0;
					$tab_powertime['annee'] = 0;
					$tab_powertime['mois_prec'] = 0;
					$tab_powertime['semaine_prec'] = 0;
					$tab_powertime['jour_prec'] = 0;
					$tab_powertime['annee_prec'] = 0;
					$tab_powertime['last'] = date('d')."-00:00";
					$tab_powertime['j-7'] = 0;
					$tab_powertime['j-6'] = 0;
					$tab_powertime['j-5'] = 0;
					$tab_powertime['j-4'] = 0;
					$tab_powertime['j-3'] = 0;
					$tab_powertime['j-2'] = 0;
					$tab_powertime['j-1'] = 0;
					$tab_powertime['moy7jours']  = 0;
					saveVariable('POWERTIME_'.$api_periph, $tab_powertime);
					
            }
	    }
		// Migration v2.0
		if ($action == 'migrate' && $capteur == "jour") {
			$preload = loadVariable('POWERTIME');
			if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {
					$tab_powertime = $preload;
					if (array_key_exists($api_periph, $tab_powertime)) {
					    saveVariable('POWERTIME_'.$api_periph, $tab_powertime[$api_periph]);
					}
			}
		}
		// Lecture temps de fonctionnement
        if ($action == 'poll' && $api_periph != 'undefined') {
            	$daylast = 0;
            	$monthlast = 0;
				$weeklast = 0;
				$anneelast = 0;
				$j7last = 0;
				$j6last = 0;
				$j5last = 0;
				$j4last = 0;
				$j3last = 0;
				$j2last = 0;
				$j1last = 0;
				
				$preload = loadVariable('POWERTIME_'.$api_periph);
				if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {
            		$tab_powertime = $preload;
					$last = $tab_powertime['last'];
					$daylast = $tab_powertime['jour'];
					$monthlast = $tab_powertime['mois'];
					$anneelast = $tab_powertime['annee'];
					if (array_key_exists("semaine", $tab_powertime)) {
						$weeklast = $tab_powertime['semaine'];
					}  
					if (array_key_exists("j-7", $tab_powertime)) {
						$j7last = $tab_powertime['j-7'];
						$j6last = $tab_powertime['j-6'];
						$j5last = $tab_powertime['j-5'];
						$j4last = $tab_powertime['j-4'];
						$j3last = $tab_powertime['j-3'];
						$j2last = $tab_powertime['j-2'];
						$j1last = $tab_powertime['j-1'];
					}  
				}
            	else {
            			// aucune variable suivie, mise à zéro
            			$tab_powertime['last'] = date('d')."-00:00";
						$tab_powertime['jour'] = 0;
						$tab_powertime['mois'] = 0;
						$tab_powertime['annee'] = 0;
						$tab_powertime['mois_prec'] = 0;
						$tab_powertime['jour_prec'] = 0;
						$tab_powertime['annee_prec'] = 0;
						$tab_powertime['semaine'] = 0;
						$tab_powertime['semaine_prec'] = 0;
						$tab_powertime['j-7'] = 0;
						$tab_powertime['j-6'] = 0;
						$tab_powertime['j-5'] = 0;
						$tab_powertime['j-4'] = 0;
						$tab_powertime['j-3'] = 0;
						$tab_powertime['j-2'] = 0;
						$tab_powertime['j-1'] = 0;
						$tab_powertime['moy7jours'] = 0;
            	}
					
            	$valeurPeriph = getValue($api_periph);
				if ($capteur == "jour") {
            		// dernière mesure
					$lasttime = substr($last, 3, 5);
					$lastday = substr($last, 0, 2);
					$mesureveille = false;
					$razday = false;
					$razweek = false;
					$razmois = false;
					$razannee = false;
					// si dernière mesure veille, dernière mesure à 00:00
					if ($lastday != date('d')) {
						$lasttime = '00:00';
						$mesureveille = true;
						$razday = true;
						$tab_powertime['jour_prec'] = $tab_powertime['jour'];
						
						$daylast = 0;
						$tab_powertime['j-7']  = $tab_powertime['j-6'];
						$tab_powertime['j-6']  = $tab_powertime['j-5'];
						$tab_powertime['j-5']  = $tab_powertime['j-4'];
						$tab_powertime['j-4']  = $tab_powertime['j-3'];
						$tab_powertime['j-3']  = $tab_powertime['j-2'];
						$tab_powertime['j-2']  = $tab_powertime['j-1'];
						$temp = $tab_powertime['j-1'];
						$tab_powertime['j-1'] = $tab_powertime['jour'];
						$moyenne7jours = round((($tab_powertime['j-7'] + $tab_powertime['j-6'] + $tab_powertime['j-5'] + $tab_powertime['j-4'] + $tab_powertime['j-3'] + $tab_powertime['j-2'] + $tab_powertime['j-1']) / 7), 0);
						$tab_powertime['moy7jours'] = $moyenne7jours;
						if ($option == "cumul=oui") {
							$tab_powertime['j-1'] += $temp;
						} 
						$tab_powertime['jour'] = 0;
						if (date('w') == 1) {
							$razweek = true;
							$tab_powertime['semaine_prec'] = $tab_powertime['semaine'];
							$tab_powertime['semaine'] = 0;
							$weeklast = 0;
						}
						if (date('j') == 1) {
							$razmois = true;
							$tab_powertime['mois_prec'] = $tab_powertime['mois'];
							$tab_powertime['mois'] = 0;
							$monthlast = 0;
						}
						if (date('n') == 1 && $razmois) {
							$razannee = true;
							$tab_powertime['annee_prec'] = $tab_powertime['annee'];
							$tab_powertime['annee'] = 0;
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
						$weeklast += $onlymn;
            			$monthlast += $onlymn;
						$anneelast += $onlymn;
						
						$tab_powertime['jour'] = $daylast;
						$tab_powertime['mois'] = $monthlast;
						$tab_powertime['annee'] = $anneelast;
						$tab_powertime['semaine'] = $weeklast;
						
            		}
					$tab_powertime['last'] = date('d')."-".$maintenant;
					saveVariable('POWERTIME_'.$api_periph, $tab_powertime);
            	}	
            		$xml .= "<JOUR>".$tab_powertime['jour']."</JOUR>";
					$minutes = $tab_powertime['jour'];
            		$heure = floor($minutes/60);
					$reste2 = ($minutes%60);
					$minute = floor($reste2);
					$xml .= "<JOURLIT>".$heure."h ".$minute."mn</JOURLIT>";
					
            		$xml .= "<JOUR_PREC>".$tab_powertime['jour_prec']."</JOUR_PREC>";
            		$minutes = $tab_powertime['jour_prec'];
            		$heure = floor($minutes/60);
					$reste2 = ($minutes%60);
					$minute = floor($reste2);
					$xml .= "<JOURLIT_PREC>".$heure."h ".$minute."mn</JOURLIT_PREC>";
            		
					$xml .= "<SEMAINE>".$tab_powertime['semaine']."</SEMAINE>";
            		$minutes = $tab_powertime['semaine'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<SEMAINELIT>".$jour."j ".$heure."h ".$minute."mn</SEMAINELIT>";
					
            		$xml .= "<SEMAINE_PREC>".$tab_powertime['semaine_prec']."</SEMAINE_PREC>";
            		$minutes = $tab_powertime['semaine_prec'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<SEMAINELIT_PREC>".$jour."j ".$heure."h ".$minute."mn</SEMAINELIT_PREC>";
					
            		$xml .= "<MOIS>".$tab_powertime['mois']."</MOIS>";
            		$minutes = $tab_powertime['mois'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<MOISLIT>".$jour."j ".$heure."h ".$minute."mn</MOISLIT>";
					
            		$xml .= "<MOIS_PREC>".$tab_powertime['mois_prec']."</MOIS_PREC>";
            		$minutes = $tab_powertime['mois_prec'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<MOISLIT_PREC>".$jour."j ".$heure."h ".$minute."mn</MOISLIT_PREC>";
					
					$xml .= "<ANNEE>".$tab_powertime['annee']."</ANNEE>";
            		$minutes = $tab_powertime['annee'];
					$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<ANNEELIT>".$jour."j ".$heure."h ".$minute."mn</ANNEELIT>";
					
            		$xml .= "<ANNEE_PREC>".$tab_powertime['annee_prec']."</ANNEE_PREC>";
            		$minutes = $tab_powertime['annee_prec'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<ANNEELIT_PREC>".$jour."j ".$heure."h ".$minute."mn</ANNEELIT_PREC>";
					
					$xml .= "<J-7>".$tab_powertime['j-7']."</J-7>";
					$xml .= "<J-6>".$tab_powertime['j-6']."</J-6>";
					$xml .= "<J-5>".$tab_powertime['j-5']."</J-5>";
					$xml .= "<J-4>".$tab_powertime['j-4']."</J-4>";
					$xml .= "<J-3>".$tab_powertime['j-3']."</J-3>";
					$xml .= "<J-2>".$tab_powertime['j-2']."</J-2>";
					$xml .= "<J-1>".$tab_powertime['j-1']."</J-1>";
					
					$xml .= "<MOY7JOURS>".$tab_powertime['moy7jours']."</MOY7JOURS>";
            		$minutes = $tab_powertime['moy7jours'];
            		$jour = floor($minutes/1440);
					$reste1 = ($minutes%1440);
            		$heure = floor($reste1/60);
					$reste2 = ($reste1%60);
					$minute = floor($reste2);
					$xml .= "<MOY7JOURSLIT>".$jour."j ".$heure."h ".$minute."mn</MOY7JOURSLIT>";
								
        }
        $xml .= "</POWERTIME>";
		sdk_header('text/xml');
		echo $xml;
?>
