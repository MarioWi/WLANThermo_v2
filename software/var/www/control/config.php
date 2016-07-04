<?php
session_start();

// ##################################################################################
// Files einbinden ------------------------------------------------------------------
// ##################################################################################

include("../header.php");
include("../function.php");
session("../conf/WLANThermo.conf");
$tmpFile = '../temperaturen.csv';
$inipath = '../conf/WLANThermo.conf';


$plotcolors = array('green', 'red', 'blue', 'olive', 'magenta', 'yellow', 'violet', 'orange',
	'mediumpurple3', 'aquamarine', 'brown', 'plum', 'skyblue', 'orange-red', 'salmon',
		'black', 'dark-grey', 'purple', 'turquoise', 'khaki', 'dark-violet', 'seagreen');
// ##################################################################################
// Config-Files einlesen ------------------------------------------------------------
// ##################################################################################

if(get_magic_quotes_runtime()) set_magic_quotes_runtime(0); 
$ini = getConfig("../conf/WLANThermo.conf", ";");  // dabei ist ; das zeichen für einen kommentar.	
$sensor_ini = getConfig("../conf/sensor.conf", ";");  // dabei ist ; das zeichen für einen kommentar.

// ##################################################################################
// Formular auswerten ---------------------------------------------------------------
// ##################################################################################	

if(isset($_POST["save"])) { 

	$error = "";				
	$restart = "0";
	$lcd_restart = "0";

			for ($i = 0; $i <= 7; $i++){
				if($ini['ch_show']['ch'.$i] == "True"){
				
					// Überprüfen ob sich die Sensorenauswahl geändert hat (Restart)
					if($ini['Sensoren']['ch'.$i] !== $_POST['fuehler'.$i]){ 
						$ini['Sensoren']['ch'.$i] = $_POST['fuehler'.$i];
					}
					// Überprüfen ob sich der Messwiderstand geändert hat (Restart)
					if($ini['Messen']['messwiderstand'.$i] !== $_POST['measuring_resistance'.$i]){ 
						$ini['Messen']['messwiderstand'.$i] = $_POST['measuring_resistance'.$i];
					}
					// Farben für den Plotter ändern --------------------------------
					$ini['plotter']['color_ch'.$i] = $_POST['plot_color'.$i];

					// Kanalbezeichnung ändern --------------------------------------
					$ini['ch_name']['ch_name'.$i] = $_POST['tch'.$i];

					// Temp "min" prüfen und Speichern ------------------------------
					if($_POST['temp_min'.$i] > "-40" && $_POST['temp_min'.$i] < "380"){
						$ini['temp_min']['temp_min'.$i] = $_POST['temp_min'.$i];
					}else{
						$ini['temp_min']['temp_min'.$i] = "0";
						$error .= "Die \"min\" Temperatur f&uuml;r \"".$_POST['tch'.$i]."\" liegt ausserhalb des G&uuml;ltigen Bereichs! ==> G&uuml;ltige Werte: -20 - 380Grad<br>";
					}

					// Temp "max" prüfen und Speichern ------------------------------
					if($_POST['temp_max'.$i] > "-20" && $_POST['temp_max'.$i] < "380"){
						$ini['temp_max']['temp_max'.$i] = $_POST['temp_max'.$i];
					}else{
						$ini['temp_max']['temp_max'.$i] = "200";
						$error .= "Die \"max\" Temperatur f&uuml;r \"".$_POST['tch'.$i]."\" liegt ausserhalb des G&uuml;ltigen Bereichs! ==> G&uuml;ltige Werte: -20 - 380Grad<br>";
					}						
					
					// Alarmierung prüfen und Speichern -----------------------------
					if(isset ($_POST['alert'.$i])) { $ini['web_alert']['ch'.$i] = "True"; } else { $ini['web_alert']['ch'.$i] = "False";}
					
				}				
				
				// ch_show prüfen und Speichern -------------------------------------
				if(isset ($_POST['ch_show'.$i])) { $ini['ch_show']['ch'.$i] = "True"; } else { $ini['ch_show']['ch'.$i] = "False";}
			}
			
			// ######################################################################
			// Plot Einstellungen ---------------------------------------------------
			// ######################################################################
			
			// Plot-Dienst Starten/Stoppen 
			if(isset ($_POST['plot_start'])) { $ini['ToDo']['plot_start'] = "True"; } else { $ini['ToDo']['plot_start'] = "False";}
			
			// Pitansteuerung plotten 
			if(isset ($_POST['plot_pit'])) { $ini['plotter']['plot_pit'] = "True"; } else { $ini['plotter']['plot_pit'] = "False";}
			
			// Farben für den Plotter ändern 
			if (isset($_POST['color_pit'])) {
				$ini['plotter']['color_pit'] = $_POST['color_pit'];
			}
			
			// Farben für den Plotter ändern 
			if (isset($_POST['color_pitsoll'])) {
				$ini['plotter']['color_pitsoll'] = $_POST['color_pitsoll'];
			}
			
			// Plot-Dienst keyboxframe 
			if(isset ($_POST['keyboxframe'])) { $ini['plotter']['keyboxframe'] = "True"; } else { $ini['plotter']['keyboxframe'] = "False";}
			
			// Plotbereich MIN
			if (isset($_POST['plotbereich_min'])) {
				$ini['plotter']['plotbereich_min'] = $_POST['plotbereich_min'];
			}
			// Plotbereich MAX
			if (isset($_POST['plotbereich_max'])) {
				$ini['plotter']['plotbereich_max'] = $_POST['plotbereich_max'];
			}
			// Plotsize
			if (isset($_POST['plotsize'])) {
				$ini['plotter']['plotsize'] = $_POST['plotsize'];
			}
			// Plotname
			if (isset($_POST['plotname'])) {
				$ini['plotter']['plotname'] = $_POST['plotname'];
			}
			// Keybox Rahmen
			if (isset($_POST['keybox'])) {
				$ini['plotter']['keybox'] = $_POST['keybox'];
			}		
			// ######################################################################
			// Alarm Einstellungen --------------------------------------------------
			// ######################################################################
			
			// Template für Temperaturüberschreitung
			if (isset($_POST['alarm_high_template'])) {
				if($ini['Alert']['alarm_high_template'] !== $_POST['alarm_high_template']){
					$ini['Alert']['alarm_high_template'] = $_POST['alarm_high_template'];
				}
			}
			// Template für Temperaturunterschreitung
			if (isset($_POST['alarm_low_template'])) {
				if($ini['Alert']['alarm_low_template'] !== $_POST['alarm_low_template']){
					$ini['Alert']['alarm_low_template'] = $_POST['alarm_low_template'];
				}
			}
			// Template für Statusmeldungen
			if (isset($_POST['status_template'])) {
				if($ini['Alert']['status_template'] !== $_POST['status_template']){
					$ini['Alert']['status_template'] = $_POST['status_template'];
				}
			}
			// Template für die Nachricht
			if (isset($_POST['message_template'])) {
				if($ini['Alert']['message_template'] !== $_POST['message_template']){
					$ini['Alert']['message_template'] = $_POST['message_template'];
				}
			}
			// Intervall für Statusmeldungen
			if (isset($_POST['status_interval'])) {
				if($ini['Alert']['status_interval'] !== $_POST['status_interval']){
					$ini['Alert']['status_interval'] = $_POST['status_interval'];
				}
			}
			// Intervall für Alarmmeldungen
			if (isset($_POST['alarm_interval'])) {
				if($ini['Alert']['alarm_interval'] !== $_POST['alarm_interval']){
					$ini['Alert']['alarm_interval'] = $_POST['alarm_interval'];
				}
			}
			// ######################################################################
			// WhatsApp Einstellungen -----------------------------------------------
			// ######################################################################
			
			// WhatsApp Empfänger
			if (isset($_POST['whatsapp_number'])) {
				if($ini['WhatsApp']['whatsapp_number'] !== $_POST['whatsapp_number']){
					$ini['WhatsApp']['whatsapp_number'] = $_POST['whatsapp_number'];
				}
			}
			// WhatsApp Benachrichtigung Aktivieren/Deaktivieren
			if(isset ($_POST['whatsapp_alert'])) {$_POST['whatsapp_alert'] = "True"; }else{ $_POST['whatsapp_alert'] = "False";}
			if($ini['WhatsApp']['whatsapp_alert'] !== $_POST['whatsapp_alert']){
				$ini['WhatsApp']['whatsapp_alert'] = $_POST['whatsapp_alert'];
			}
			
			// ######################################################################
			// Email Einstellungen --------------------------------------------------
			// ######################################################################
			
			// Emailbenachrichtigung Aktivieren/Deaktivieren
			if(isset ($_POST['email'])) {$_POST['email'] = "True"; }else{ $_POST['email'] = "False";}
			if($ini['Email']['email_alert'] !== $_POST['email']){
				$ini['Email']['email_alert'] = $_POST['email'];
			}
			// Emailtls Aktivieren/Deaktivieren 
			if(isset ($_POST['starttls'])) {$_POST['starttls'] = "True"; }else{ $_POST['starttls'] = "False";}
			if($ini['Email']['starttls'] !== $_POST['starttls']){
				$ini['Email']['starttls'] = $_POST['starttls'];
			}
			// Email Authentifizierung Aktivieren/Deaktivieren
			if(isset ($_POST['auth_check'])) {$_POST['auth_check'] = "True"; }else{ $_POST['auth_check'] = "False";}
			if($ini['Email']['auth'] !== $_POST['auth_check']){
				$ini['Email']['auth'] = $_POST['auth_check'];
			}
			// Email Empfänger
			if (isset($_POST['email_to'])) {
				if($ini['Email']['email_to'] !== $_POST['email_to']){
					$ini['Email']['email_to'] = $_POST['email_to'];
				}
			}
			// Email Absender
			if (isset($_POST['email_from'])) {
				if($ini['Email']['email_from'] !== $_POST['email_from']){
					$ini['Email']['email_from'] = $_POST['email_from'];
				}
			}	
			// Email Betreff 
			if (isset($_POST['email_subject'])) {
				if($ini['Email']['email_subject'] !== $_POST['email_subject']){
					$ini['Email']['email_subject'] = $_POST['email_subject'];
				}
			}
			// Email Server
			if (isset($_POST['email_smtp'])) {
				if($ini['Email']['server'] !== $_POST['email_smtp']){
					$ini['Email']['server'] = $_POST['email_smtp'];
				}
			}
			// Email Passwort
			if (isset($_POST['email_password'])) {
				if($ini['Email']['password'] !== $_POST['email_password']){
					$ini['Email']['password'] = $_POST['email_password'];
				}
			}
			// Email Benutzername
			if (isset($_POST['email_username'])) {
				if($ini['Email']['username'] !== $_POST['email_username']){
					$ini['Email']['username'] = $_POST['email_username'];
				}			
			}
			
			// ######################################################################
			// Push Einstellungen --------------------------------------------------
			// ######################################################################
			
			// Pushbenachrichtigung Aktivieren/Deaktivieren
			if(isset ($_POST['push_on'])) {$_POST['push_on'] = "True"; }else{ $_POST['push_on'] = "False";}
			if($ini['Push']['push_on'] !== $_POST['push_on']){
				$ini['Push']['push_on'] = $_POST['push_on'];
			}
			// Push URL
			if (isset($_POST['push_url'])) {
				if($ini['Push']['push_url'] !== $_POST['push_url']){
					$ini['Push']['push_url'] = $_POST['push_url'];
				}
			}
			// Push Body
			if (isset($_POST['push_body'])) {
				if($ini['Push']['push_body'] !== $_POST['push_body']){
					$ini['Push']['push_body'] = $_POST['push_body'];
				}
			}
			// Push push_inst_id
			if (isset($_POST['push_inst_id'])) {
				if($ini['Push']['push_inst_id'] !== $_POST['push_inst_id']){
					$ini['Push']['push_inst_id'] = $_POST['push_inst_id'];
				}
			}
			// Push push_device
			if (isset($_POST['push_device'])) {
				if($ini['Push']['push_device'] !== $_POST['push_device']){
					$ini['Push']['push_device'] = $_POST['push_device'];
				}			
			}
			// Push push_inst_id2
			if (isset($_POST['push_inst_id2'])) {
				if($ini['Push']['push_inst_id2'] !== $_POST['push_inst_id2']){
					$ini['Push']['push_inst_id2'] = $_POST['push_inst_id2'];
				}			
			}
			// Push push_device2
			if (isset($_POST['push_device2'])) {
				if($ini['Push']['push_device2'] !== $_POST['push_device2']){
					$ini['Push']['push_device2'] = $_POST['push_device2'];
				}			
			}
			// Push push_inst_id3
			if (isset($_POST['push_inst_id3'])) {
				if($ini['Push']['push_inst_id3'] !== $_POST['push_inst_id3']){
					$ini['Push']['push_inst_id3'] = $_POST['push_inst_id3'];
				}			
			}
			// Push push_device3
			if (isset($_POST['push_device3'])) {
				if($ini['Push']['push_device3'] !== $_POST['push_device3']){
					$ini['Push']['push_device3'] = $_POST['push_device3'];
				}			
			}
			// Push push_chat_id
			if (isset($_POST['push_chat_id'])) {
				if($ini['Push']['push_chat_id'] !== $_POST['push_chat_id']){
					$ini['Push']['push_chat_id'] = $_POST['push_chat_id'];
				}			
			}
			// Push push_token
			if (isset($_POST['push_token'])) {
				if($ini['Push']['push_token'] !== $_POST['push_token']){
					$ini['Push']['push_token'] = $_POST['push_token'];
				}			
			}

			// ######################################################################
			// LCD Einstellungen ----------------------------------------------------
			// ######################################################################
			
			// LCD EIN/AUS
			if(isset ($_POST['lcd_show'])) { $_POST['lcd_show'] = "True"; } else { $_POST['lcd_show'] = "False";}	
			if($ini['Display']['lcd_present'] !== $_POST['lcd_show']){
				$ini['Display']['lcd_present'] = $_POST['lcd_show'];
				$restart = "1";
				$lcd_restart = "1";
			}			
			
			// LCD Start Seite
			if (isset($_POST['lcd_start_page'])) {
				$ini['Display']['start_page'] = $_POST['lcd_start_page'];
			}
			
			// LCD Type
			if (isset($_POST['lcd_type'])) {
				$ini['Display']['lcd_type'] = $_POST['lcd_type'];
			}
		
			// New Logfile on restart -----------------------------------------------
			if(isset ($_POST['new_logfile_restart'])) { $_POST['new_logfile_restart'] = "True"; } else { $_POST['new_logfile_restart'] = "False";}
				if($ini['Logging']['write_new_log_on_restart'] !== $_POST['new_logfile_restart']){
					$ini['Logging']['write_new_log_on_restart'] = $_POST['new_logfile_restart'];
				}
				
			// checkUpdate ----------------------------------------------------------
			if(isset ($_POST['checkUpdate'])) { $_POST['checkUpdate'] = "True"; } else { $_POST['checkUpdate'] = "False";}
				if($ini['update']['checkupdate'] !== $_POST['checkUpdate']){
					$ini['update']['checkupdate'] = $_POST['checkUpdate'];
				}

			// showCPUlast ----------------------------------------------------------
			if(isset ($_POST['showcpulast'])) { $_POST['showcpulast'] = "True"; } else { $_POST['showcpulast'] = "False";}
				if($ini['Hardware']['showcpulast'] !== $_POST['showcpulast']){
					$ini['Hardware']['showcpulast'] = $_POST['showcpulast'];
				}
			
			// Beeper EIN/AUS -------------------------------------------------------
			if(isset ($_POST['beeper_enabled'])) { $_POST['beeper_enabled'] = "True"; } else { $_POST['beeper_enabled'] = "False";}
				if($ini['Sound']['beeper_enabled'] !== $_POST['beeper_enabled']){
					$ini['Sound']['beeper_enabled'] = $_POST['beeper_enabled'];
					$restart = "1";
				}
				
			// Hardware Version				
			if($ini['Hardware']['version'] !== $_POST['hardware_version']){
				if(($_POST['hardware_version'] == "v3") or ($ini['Hardware']['version'] == "v3")) {
					if($ini['ToDo']['pit_on'] == "True"){
						$ini['ToDo']['restart_pitmaster'] = "True";
					}
				}
				$ini['Hardware']['version'] = $_POST['hardware_version'];
				$restart = "1";
			}
			// ######################################################################
			// Dienste nach Änderung neu starten ------------------------------------
			// ######################################################################
			
			// wlt_2_comp.py neu starten
			if($restart == "1"){
				$ini['ToDo']['restart_thermo'] = "True";
			}
			
			// LCD-Dienst neu starten 
			if($lcd_restart == "1"){
				$ini['ToDo']['restart_display'] = "True";
			}
			
			//#######################################################################
			// Pitmaster Einstellungen ----------------------------------------------
			//#######################################################################
			
			// Pitmaster EIN/AUS
			if(isset ($_POST['pit_on'])) {$_POST['pit_on'] = "True"; }else{ $_POST['pit_on'] = "False";}
			if($ini['ToDo']['pit_on'] !== $_POST['pit_on']){
				$ini['ToDo']['pit_on'] = $_POST['pit_on'];
			}
			// Pit invertierung EIN/AUS
			if(isset ($_POST['pit_inverted'])) {$_POST['pit_inverted'] = "True"; }else{ $_POST['pit_inverted'] = "False";}
			if($ini['Pitmaster']['pit_inverted'] !== $_POST['pit_inverted']){
				$ini['Pitmaster']['pit_inverted'] = $_POST['pit_inverted'];
			}
			// Pitmaster PID EIN/AUS
			if(isset ($_POST['pit_controller_type'])) {$_POST['pit_controller_type'] = "PID"; }else{ $_POST['pit_controller_type'] = "False";}
			if($ini['Pitmaster']['pit_controller_type'] !== $_POST['pit_controller_type']){
				$ini['Pitmaster']['pit_controller_type'] = $_POST['pit_controller_type'];
			}
			// Open Lid detection EIN/AUS
			if(isset ($_POST['pit_open_lid_detection'])) {$_POST['pit_open_lid_detection'] = "True"; }else{ $_POST['pit_open_lid_detection'] = "False";}
			if($ini['Pitmaster']['pit_open_lid_detection'] !== $_POST['pit_open_lid_detection']){
				$ini['Pitmaster']['pit_open_lid_detection'] = $_POST['pit_open_lid_detection'];
				//$ini['ToDo']['restart_pitmaster'] = "True";
			}
			// Pitmaster manuell einstellen
			if (isset($_POST['pit_man'])) {
				if($ini['Pitmaster']['pit_man'] !== $_POST['pit_man']){
					$ini['Pitmaster']['pit_man'] = $_POST['pit_man'];
				}
			}
			// Pitmaster bei Ã„nderung neu starten
			if (isset($_POST['pit_curve'])) {
				//if($ini['Pitmaster']['pit_curve'] !== $_POST['pit_curve']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_curve'] = $_POST['pit_curve'];
			}
			if (isset($_POST['pit_type'])) {
				//if($ini['Pitmaster']['pit_type'] !== $_POST['pit_type']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_type'] = $_POST['pit_type'];
			}
			//if (isset($_POST['pit_controller_type'])) {
			//	if($ini['Pitmaster']['pit_controller_type'] !== $_POST['pit_controller_type']){
			//		$ini['ToDo']['restart_pitmaster'] = "True";
			//	}			
			//}
			//Pitmaster PID kp
			if (isset($_POST['pit_kp'])) {
				//if($ini['Pitmaster']['pit_kp'] !== $_POST['pit_kp']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_kp'] = $_POST['pit_kp'];
			}
			//Pitmaster PID ki
			if (isset($_POST['pit_ki'])) {
				//if($ini['Pitmaster']['pit_ki'] !== $_POST['pit_ki']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_ki'] = $_POST['pit_ki'];
			}
			//Pitmaster PID kd
			if (isset($_POST['pit_kd'])) {
				//if($ini['Pitmaster']['pit_kd'] !== $_POST['pit_kd']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_kd'] = $_POST['pit_kd'];
			}
			//Pitmaster PID kp_a
			if (isset($_POST['pit_kp_a'])) {
				//if($ini['Pitmaster']['pit_kp_a'] !== $_POST['pit_kp_a']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_kp_a'] = $_POST['pit_kp_a'];
			}
			//Pitmaster PID ki_a
			if (isset($_POST['pit_ki_a'])) {
				//if($ini['Pitmaster']['pit_ki_a'] !== $_POST['pit_ki_a']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_ki_a'] = $_POST['pit_ki_a'];
			}
			//Pitmaster PID kd_a
			if (isset($_POST['pit_kd_a'])) {
				//if($ini['Pitmaster']['pit_kd_a'] !== $_POST['pit_kd_a']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_kd_a'] = $_POST['pit_kd_a'];
			}
			//Pitmaster IO GPIO
			if (isset($_POST['pit_io_gpio'])) {
				//if($ini['Pitmaster']['pit_io_gpio'] !== $_POST['pit_io_gpio']){
				//	$ini['ToDo']['restart_pitmaster'] = "True";
				//}
				$ini['Pitmaster']['pit_io_gpio'] = $_POST['pit_io_gpio'];
			}						
			// Pitmaster Kanal
			if (isset($_POST['pit_ch'])) {
				$ini['Pitmaster']['pit_ch'] = $_POST['pit_ch'];
			}
			// Pitmaster Temperatur 
			if (isset($_POST['pit_set'])) {
				$ini['Pitmaster']['pit_set'] = $_POST['pit_set'];
			}
			// Pitmaster Temperatur
			if (isset($_POST['pit_pause'])) {
				$ini['Pitmaster']['pit_pause'] = $_POST['pit_pause'];
			}
			// Pitmaster PWM min 
			if (isset($_POST['pit_pwm_min'])) {
				if($_POST['pit_pwm_min'] < 0){
					$ini['Pitmaster']['pit_pwm_min'] = "0";
				}else{
					$ini['Pitmaster']['pit_pwm_min'] = $_POST['pit_pwm_min'];
				}
			}
			// Pitmaster PWM max 
			if (isset($_POST['pit_pwm_max'])) {
				if($_POST['pit_pwm_max'] > 100){
					$ini['Pitmaster']['pit_pwm_max'] = "100";
				}else{
					$ini['Pitmaster']['pit_pwm_max'] = $_POST['pit_pwm_max'];
				}
			}
			// Pitmaster Servo min
			if (isset($_POST['pit_servo_min'])) {
				if($_POST['pit_servo_min'] < 500){
					$ini['Pitmaster']['pit_servo_min'] = "500";
				}elseif($_POST['pit_servo_min'] > 2500){
					$ini['Pitmaster']['pit_servo_min'] = "2500";
				}else{
					$ini['Pitmaster']['pit_servo_min'] = $_POST['pit_servo_min'];
				}
			}
			// Pitmaster Servo max 
			if (isset($_POST['pit_servo_max'])) {
				if($_POST['pit_servo_max'] < 500){
					$ini['Pitmaster']['pit_servo_max'] = "500";
				}elseif($_POST['pit_servo_max'] > 2500){
					$ini['Pitmaster']['pit_servo_max'] = "2500";
				}else{
					$ini['Pitmaster']['pit_servo_max'] = $_POST['pit_servo_max'];
				}				
			}
			
			//#######################################################################
			// Webcam Einstellungen -------------------------------------------------
			//#######################################################################
			
			// Webcam EIN/AUS
			if(isset ($_POST['webcam_start'])) {$_POST['webcam_start'] = "True"; }else{ $_POST['webcam_start'] = "False";}
				if($ini['webcam']['webcam_start'] !== $_POST['webcam_start']){
					$ini['webcam']['webcam_start'] = $_POST['webcam_start'];
					
			}
			// Raspicam EIN/AUS
			if(isset ($_POST['raspicam_start'])) {$_POST['raspicam_start'] = "True"; }else{ $_POST['raspicam_start'] = "False";}
				if($ini['webcam']['raspicam_start'] !== $_POST['raspicam_start']){
					$ini['webcam']['raspicam_start'] = $_POST['raspicam_start'];
			}
			// Webcamsize
			if (isset($_POST['webcam_size'])) {
				$ini['webcam']['webcam_size'] = $_POST['webcam_size'];
			}
			// Raspicamsize
			if (isset($_POST['raspicam_size'])) {
				$ini['webcam']['raspicam_size'] = $_POST['raspicam_size'];
			}
			// Webcamname
			if (isset($_POST['webcam_name'])) {
				$ini['webcam']['webcam_name'] = $_POST['webcam_name'];
			}
			// Raspicamname
			if (isset($_POST['raspicam_name'])) {
				$ini['webcam']['raspicam_name'] = $_POST['raspicam_name'];
			}
			// Raspicamexposure
			if (isset($_POST['raspicam_exposure'])) {
				$ini['webcam']['raspicam_exposure'] = $_POST['raspicam_exposure'];
			}
			//#######################################################################
		
		
	// Alle POST Variablen Anzeigen ###################################################################################################
	//	echo nl2br(print_r($_POST,true));
	// --------------------------------------------------------------------------------------------------------------------------------
	
	// --------------------------------------------------------------------------------------------------------------------------------
	// Schreiben der WLANThermo.conf ##################################################################################################
	// --------------------------------------------------------------------------------------------------------------------------------
	
	write_ini($inipath, $ini);
	
	// --------------------------------------------------------------------------------------------------------------------------------
	// Flag setzen ####################################################################################################################
	// --------------------------------------------------------------------------------------------------------------------------------	
	exec("/usr/bin/touch /var/www/tmp/flag",$output);
	// --------------------------------------------------------------------------------------------------------------------------------
	// Schreiben der "temperaturen.csv" ###############################################################################################
	// --------------------------------------------------------------------------------------------------------------------------------	
	
	$tmp_min_max_value =  $ini['temp_max']['temp_max0']."\n".$ini['temp_max']['temp_max1']."\n".$ini['temp_max']['temp_max2']."\n".$ini['temp_max']['temp_max3']."\n".$ini['temp_max']['temp_max4']."\n".$ini['temp_max']['temp_max5']."\n".$ini['temp_max']['temp_max6']."\n".$ini['temp_max']['temp_max7']."\n".$ini['temp_min']['temp_min0']."\n".$ini['temp_min']['temp_min1']."\n".$ini['temp_min']['temp_min2']."\n".$ini['temp_min']['temp_min3']."\n".$ini['temp_min']['temp_min4']."\n".$ini['temp_min']['temp_min5']."\n".$ini['temp_min']['temp_min6']."\n".$ini['temp_min']['temp_min7']."\n";
	writeTmpMinMaxFile($tmp_min_max_value, $tmpFile);
		
	echo "<div class=\"infofield\">";
	echo "  <head> <meta http-equiv=\"refresh\" content=\"1;URL='../index.php'\"> </head> <body> <h2>Einstellungen werden gespeichert...</h2></body>";	
	if($restart == "1"){
		echo "<h2>wlt_2_comp.py wird neu gestartet...</h2>";
	}
	echo "</div>";

// ##################################################################################
// Zurück Button auswerten ----------------------------------------------------------
// ##################################################################################

}elseif(isset($_POST["back"])) {
	echo "<div class=\"infofield\">";
	echo "  <head> <meta http-equiv=\"refresh\" content=\"1;URL='../index.php'\"> </head> <body> <h2>Verlassen der Seite ohne Speichern!...</h2></body>";
	echo "</div>";
}elseif(isset($_GET["alert-test"]) && $_GET["alert-test"] == "true") {
	touch( __DIR__ . '/../alert.test' );
	echo "<div class=\"infofield\">";
	echo "  <head> <meta http-equiv=\"refresh\" content=\"1;URL='config.php'\"></head>
			<body> <h2>Testalarm wird gesendet...</h2></body>";
	echo "</div>";
}else{

// ##################################################################################
// Formular ausgeben ----------------------------------------------------------------
// ##################################################################################
?>
<div id="config">	
	<h1>Konfiguration</h1>
	<form action="config.php" method="post" >
	
<?php
// ##################################################################################
// Formular Fühler/Farbe/Temp min/Temp max/Kanal ------------------------------------
// ##################################################################################

		for ($i = 0; $i <= 7; $i++){ ?>
			<div id="ch<?php echo $i; ?>" class="config small">
				<div class="headline"><?php echo htmlentities($ini['ch_name']['ch_name'.$i], ENT_QUOTES, "UTF-8"); ?></div>
				<div class="headicon"><font color="<?php echo $ini['plotter']['color_ch'.$i];?>">#<?php echo $i?></font></div>
				<div class="config_text row_1 col_1">Name:</div>
				<div class="config_text row_1 col_6">F&uuml;hler:</div>
				<div class="config_text row_1 col_3"><input type="text" name="tch<?php echo $i;?>" size="25" maxlength="28" value="<?php echo $ini['ch_name']['ch_name'.$i];?>"></div>
				<div class="config_text row_3 col_1">Temperatur</div>
				<div class="config_text row_3 col_2">min:</div>
				<div class="config_text row_3 col_3"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9-]/g,'');" name="temp_min<?php echo $i;?>" size="6" maxlength="3" value="<?php echo $ini['temp_min']['temp_min'.$i];?>"></div>
				<div class="config_text row_3 col_4">max:</div>
				<div class="config_text row_3 col_5"><input type="text" onkeyup="this.value=this.value.replace(/\D/, '');" name="temp_max<?php echo $i;?>" size="6" maxlength="3" value="<?php echo $ini['temp_max']['temp_max'.$i];?>"></div>
				<div class="config_text row_2 col_6">Plotfarbe:</div>
				<div class="config_text row_2 col_1">Messwiderstand:</div>
				<div class="config_text row_2 col_3"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9.]/g,'');" name="measuring_resistance<?php echo $i;?>" size="6" maxlength="6" value="<?php echo $ini['Messen']['messwiderstand'.$i];?>"></div>
				<div class="config_text row_1 col_7">
					<select name="fuehler<?php echo $i?>" size="1">	
						<?php
						foreach($sensor_ini AS $sensor_number => $sensor_name)
						{
							 $sensor_number = $sensor_name['number'];?> 
							<option value="<?php echo $sensor_number ?>" <?php if ($ini['Sensoren']['ch'.$i] == $sensor_number){echo " selected";} ?> ><?php echo $sensor_name['name'] ?></option><?php
						}?>			
					</select>
				</div>
				<div class="config_text row_2 col_7">
					<select name="plot_color<?php echo $i?>" size="1">
					<?php
					foreach($plotcolors AS $color)
					{
					?>
						<option <?php if($ini['plotter']['color_ch'.$i] == $color)	{echo " selected";} ?> ><?php echo $color ?></option>
					<?php
					}
					?>
					</select>				
				</div>
				<div class="config_text row_3 col_7"><input type="checkbox" name="alert<?php echo $i;?>" value="salarm<?php echo $i?>" <?php if($ini['web_alert']['ch'.$i] == "True") {echo "checked=\"checked\"";}?> ></div>
				<div class="config_text row_3 col_6">WebSound Alarm:</div>
			</div>
<?php 	}
// ##################################################################################
// Formular Alarmierungseinstellungen -----------------------------------------------------
// ##################################################################################	
?>
		<div class="config middle">
			<div class="headline">Alarmierungseinstellungen</div>
			<div class="config_text row_1 col_6">Alarmintervall:</div>
			<div class="config_text row_1 col_7"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9.]/g,'');" name="alarm_interval" size="6" maxlength="6" value="<?php echo $ini['Alert']['alarm_interval'];?>"></div>
			<div class="config_text row_2 col_6">Statusintervall:</div>
			<div class="config_text row_2 col_7"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9.]/g,'');" name=status_interval size="6" maxlength="6" value="<?php echo $ini['Alert']['status_interval'];?>"></div>
			<div class="config_text row_1 col_1">Über:</div>
			<div class="config_text row_2 col_1">Unter:</div>
			<div class="config_text row_3 col_1">Status:</div>
			<div class="config_text row_4 col_1">Nachricht:</div>
			<div class="config_text row_1 col_5"><input type="text" name="alarm_high_template" id="alarm_high_template" size="40" maxlength="250" value="<?php echo $ini['Alert']['alarm_high_template'];?>"></div>
			<div class="config_text row_2 col_5"><input type="text" name="alarm_low_template" id="alarm_low_template" size="40" maxlength="250" value="<?php echo $ini['Alert']['alarm_low_template'];?>"></div>
			<div class="config_text row_3 col_5"><input type="text" name="status_template" id="status_template" size="40" maxlength="250" value="<?php echo $ini['Alert']['status_template'];?>"></div>
			<div class="config_text row_4 col_5"><input type="text" name="message_template" id="message_template" size="40" maxlength="250" value="<?php echo $ini['Alert']['message_template'];?>"></div>
			<div class="config_text row_4 col_7"><button type="button" onclick="$.get('config.php?alert-test=true')">Test senden!</button></div>

		</div>
<?php
// ##################################################################################
// Formular EMail Einstellungen -----------------------------------------------------
// ##################################################################################	
?>
		<div class="config middle">
			<div class="headline">EMail Einstellungen</div>
			<div class="config_text row_4 col_4">TLS Verschl&uuml;sselung:&nbsp;<input type="checkbox" name="starttls" id="email_starttls" value="True" <?php if($ini['Email']['starttls'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_1 col_6">EMail versenden:</div>			
			<div class="config_text row_1 col_7"><input type="checkbox" name="email" id="email" value="True" <?php if($ini['Email']['email_alert'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="headicon"><img src="../images/icons16x16/mail.png" alt=""></div>
			<div class="config_text row_2 col_6">Authentifizierung:</div>
			<div class="config_text row_2 col_7"><input type="checkbox" name="auth_check" id="email_auth_check" value="True" <?php if($ini['Email']['auth'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_1 col_1">An:</div>
			<div class="config_text row_2 col_1">Von:</div>
			<div class="config_text row_3 col_1">Betreff:</div>
			<div class="config_text row_1 col_3"><input type="text" name="email_to" id="email_to" size="25" maxlength="50" value="<?php echo $ini['Email']['email_to'];?>"></div>
			<div class="config_text row_2 col_3"><input type="text" name="email_from" id="email_from" size="25" maxlength="50" value="<?php echo $ini['Email']['email_from'];?>"></div>
			<div class="config_text row_3 col_3"><input type="text" name="email_subject" id="email_subject" size="25" maxlength="50" value="<?php echo $ini['Email']['email_subject'];?>"></div>
			<div class="config_text row_3 col_6">Server:</div>
			<div class="config_text row_3 col_7"><input type="text" name="email_smtp" id="email_smtp" size="18" maxlength="50" value="<?php echo $ini['Email']['server'];?>"></div>
			<div class="config_text row_4 col_6">Passwort:</div>
			<div class="config_text row_4 col_7"><input type="password" name="email_password" id="email_password" size="18" maxlength="50" value="<?php echo $ini['Email']['password'];?>"></div>
			<div class="config_text row_4 col_1">Benutzername:</div>
			<div class="config_text row_4 col_3"><input type="text" name="email_username" id="email_username" size="15" maxlength="50" value="<?php echo $ini['Email']['username'];?>"></div>
		</div>
<?php
// ##################################################################################
// Formular Push Einstellungen -----------------------------------------------------
// ##################################################################################	
?>
		<div class="config middle">
			<div class="headline">Pushdienst Einstellungen</div>
			<div class="config_text row_1 col_6">Pushnachricht versenden:</div>			
			<div class="config_text row_1 col_7"><input type="checkbox" name="push_on" id="push_on" value="True" <?php if($ini['Push']['push_on'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="headicon">&nbsp;</div>
			<div class="config_text row_1 col_1">URL:</div>
			<div class="config_text row_2 col_1">Body:</div>
			<div class="config_text row_3 col_1">chat_id:</div>
			<div class="config_text row_4 col_1">token:</div>
			<div class="config_text row_1 col_3"><input type="text" name="push_url" id="push_url" size="25" maxlength="500" value="<?php echo $ini['Push']['push_url'];?>"></div>
			<div class="config_text row_2 col_3"><input type="text" name="push_body" id="push_body" size="25" maxlength="500" value="<?php echo $ini['Push']['push_body'];?>"></div>
			<div class="config_text row_3 col_3"><input type="text" name="push_chat_id" id="push_chat_id" size="25" maxlength="200" value="<?php echo $ini['Push']['push_chat_id'];?>"></div>
			<div class="config_text row_4 col_3"><input type="text" name="push_token" id="push_token" size="25" maxlength="200" value="<?php echo $ini['Push']['push_token'];?>"></div>
			<div class="config_text row_2 col_4">device:&nbsp;&nbsp; <input type="text" name="push_device" id="push_device" size="20" maxlength="200" value="<?php echo $ini['Push']['push_device'];?>"></div>
			<div class="config_text row_3 col_4">device2: <input type="text" name="push_device2" id="push_device2" size="20" maxlength="200" value="<?php echo $ini['Push']['push_device2'];?>"></div>
			<div class="config_text row_4 col_4">device3: <input type="text" name="push_device3" id="push_device3" size="20" maxlength="200" value="<?php echo $ini['Push']['push_device3'];?>"></div>
			<div class="config_text row_2 col_5"></div>
			<div class="config_text row_3 col_5"></div>
			<div class="config_text row_4 col_5"></div>
			<div class="config_text row_2 col_6">inst_id:</div>
			<div class="config_text row_3 col_6">inst_id2:</div>
			<div class="config_text row_4 col_6">inst_id3:</div>
			<div class="config_text row_2 col_7"><input type="text" name="push_inst_id" id="push_inst_id" size="20" maxlength="200" value="<?php echo $ini['Push']['push_inst_id'];?>"></div>
			<div class="config_text row_3 col_7"><input type="text" name="push_inst_id2" id="push_inst_id2" size="20" maxlength="200" value="<?php echo $ini['Push']['push_inst_id2'];?>"></div>
			<div class="config_text row_4 col_7"><input type="text" name="push_inst_id3" id="push_inst_id3" size="20" maxlength="200" value="<?php echo $ini['Push']['push_inst_id3'];?>"></div>

		</div>
<?php
// ##################################################################################
// Formular WhatsApp Einstellungen --------------------------------------------------
// ##################################################################################	
?>
		<div class="config little">
			<div class="headline">WhatsApp Einstellungen</div>
			<div class="headicon"><img src="../images/icons16x16/whatsapp.png" alt=""></div>
			<div class="config_text row_1 col_1">An:</div>
			<div class="config_text row_1 col_3"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" name="whatsapp_number" id="whatsapp_number" size="27" maxlength="27" value="<?php echo $ini['WhatsApp']['whatsapp_number'];?>"></div>
			<div class="config_text row_1 col_6">WhatsApp aktivieren:</div>			
			<div class="config_text row_1 col_7"><input type="checkbox" name="whatsapp_alert" id="whatsapp_alert" value="True" <?php if($ini['WhatsApp']['whatsapp_alert'] == "True") {echo "checked=\"checked\"";}?> ></div>
		</div>
<?php
// ##################################################################################
// Formular Plotter Einstellungen ---------------------------------------------------
// ##################################################################################

?>
        <div class="config five_lines">
            <div class="headline">Plotter Einstellungen</div>           
            <div class="headicon"><img src="../images/icons16x16/chart.png" alt=""></div>
            <div class="config_text row_1 col_1">Plotter Titel:</div>
            <div class="config_text row_1 col_3"><input type="text" name="plotname" id="plot_name" size="18" maxlength="25" value="<?php echo $ini['plotter']['plotname'];?>"></div>
            <div class="config_text row_1 col_6">Plotdienst Start:</div>
            <div class="config_text row_1 col_7"><input type="checkbox" name="plot_start" id="plot_start" value="True" <?php if($ini['ToDo']['plot_start'] == "True") {echo "checked=\"checked\"";}?> ></div>
            <div class="config_text row_2 col_1">Plotbereich</div>
            <div class="config_text row_2 col_2">von:</div>
            <div class="config_text row_2 col_3"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9-]/g,'');" name="plotbereich_min" id="plotbereich_min" size="6" maxlength="3" value="<?php echo $ini['plotter']['plotbereich_min'];?>"></div>
            <div class="config_text row_2 col_4">bis:</div>
            <div class="config_text row_2 col_5"><input type="text" onkeyup="this.value=this.value.replace(/\D/, '');" name="plotbereich_max" id="plotbereich_max" size="6" maxlength="3" value="<?php echo $ini['plotter']['plotbereich_max'];?>"></div>
            <div class="config_text row_2 col_6">Key Box:</div>
            <div class="config_text row_2 col_7">
                <select name="keybox" id="plot_keybox" size="1">
                    <option <?php if($ini['plotter']['keybox'] == "top left")                {echo " selected";} ?> value="top left"     >oben links</option>
                    <option <?php if($ini['plotter']['keybox'] == "top right")                {echo " selected";} ?> value="top right"     >oben rechts</option>
                    <option <?php if($ini['plotter']['keybox'] == "bottom left")            {echo " selected";} ?> value="bottom left"     >unten links</option>
                    <option <?php if($ini['plotter']['keybox'] == "bottom right")            {echo " selected";} ?> value="bottom right" >unten rechts</option>
                    <option <?php if($ini['plotter']['keybox'] == "center left")            {echo " selected";} ?> value="center left"     >mitte links</option>
                    <option <?php if($ini['plotter']['keybox'] == "center right")            {echo " selected";} ?> value="center right" >mitte rechts</option>
                </select>       
            </div>
            <div class="config_text row_3 col_1">Plotsize:</div>
            <div class="config_text row_3 col_3">
                <select name="plotsize" id="plot_size" size="1">                       
                    <option <?php if($ini['plotter']['plotsize'] == "700x350")              {echo " selected";} ?> value="700x350" >700x350</option>
                    <option <?php if($ini['plotter']['plotsize'] == "800x500")              {echo " selected";} ?> value="800x500" >800x500</option>
                    <option <?php if($ini['plotter']['plotsize'] == "900x600")              {echo " selected";} ?> value="900x600" >900x600</option>
                    <option <?php if($ini['plotter']['plotsize'] == "1000x700")             {echo " selected";} ?> value="1000x700" >1000x700</option>
                    <option <?php if($ini['plotter']['plotsize'] == "1280x1024")            {echo " selected";} ?> value="1280x1024" >1280x1024</option>
                    <option <?php if($ini['plotter']['plotsize'] == "1600x1200")            {echo " selected";} ?> value="1600x1200" >1600x1200</option>
                    <option <?php if($ini['plotter']['plotsize'] == "1920x1200")            {echo " selected";} ?> value="1920x1200" >1920x1200</option>
                </select>
            </div>
            <div class="config_text row_3 col_6">Rahmen Legende:</div>
            <div class="config_text row_3 col_7"><input type="checkbox" name="keyboxframe" id="plot_keyboxframe" value="True" <?php if($ini['plotter']['keyboxframe'] == "True") {echo "checked=\"checked\"";}?> ></div>   
            <div class="config_text row_4 col_1">Pitmaster Ausgang:</div>
            <div class="config_text row_4 col_4">
                <select name="color_pit" id="plot_color_pit" size="1">
                <?php
                foreach($plotcolors AS $color)
                {
                ?>
                    <option <?php if($ini['plotter']['color_pit'] == $color)    {echo " selected";} ?> ><?php echo $color ?></option>
                <?php
                }
                ?>
                </select>
            </div>           
            <div class="config_text row_4 col_6">Pitmaster plotten:</div>
            <div class="config_text row_4 col_7"><input type="checkbox" name="plot_pit" id="plot_pit" value="True" <?php if($ini['plotter']['plot_pit'] == "True") {echo "checked=\"checked\"";}?> ></div>
            <div class="config_text row_5 col_1">Pitmaster Sollwert:</div>
            <div class="config_text row_5 col_4">
                <select name="color_pitsoll" id="plot_color_pitsoll" size="1">
                <?php
                foreach($plotcolors AS $color)
                {
                ?>
                    <option <?php if($ini['plotter']['color_pitsoll'] == $color) {echo " selected";} ?>><?php echo $color ?></option>
                <?php
                }
                ?>
                </select>
            </div>
        </div> 	
<?php
// ##################################################################################
// Formular Webcam Einstellungen ----------------------------------------------------
// ##################################################################################
?>	
		<div class="config middle">
			<div class="headline">Webcam Einstellungen</div>
			<div class="headicon"><img src="../images/icons16x16/webcam.png" alt=""></div>
			<div class="config_text row_1 col_1">Name:</div>
			<div class="config_text row_1 col_3"><input type="text" name="webcam_name" id="webcam_name" size="25" maxlength="28" value="<?php echo $ini['webcam']['webcam_name'];?>"></div>
			<div class="config_text row_1 col_6">Webcam Start:</div>
			<div class="config_text row_1 col_7"><input type="checkbox" name="webcam_start" id="webcam_start" value="True" <?php if($ini['webcam']['webcam_start'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_2 col_1">Webcam size:</div>
			<div class="config_text row_2 col_3">
				<select name="webcam_size" id="webcam_size" size="1">						
					<option <?php if($ini['webcam']['webcam_size'] == "320x240")			{echo " selected";} ?> >320x240</option>
					<option <?php if($ini['webcam']['webcam_size'] == "640x480")			{echo " selected";} ?> >640x480</option>
					<option <?php if($ini['webcam']['webcam_size'] == "1080x720")			{echo " selected";} ?> >1080x720</option>
		            <option <?php if($ini['webcam']['webcam_size'] == "1280x1024")			{echo " selected";} ?> >1280x1024</option>
				</select>
			</div>					
			<div class="config_text row_3 col_1">Name:</div>
			<div class="config_text row_3 col_3"><input type="text" name="raspicam_name" id="raspicam_name" size="25" maxlength="28" value="<?php echo $ini['webcam']['raspicam_name'];?>"></div>
			<div class="config_text row_3 col_6">Raspicam Start:</div>
			<div class="config_text row_3 col_7"><input type="checkbox" name="raspicam_start" id="raspicam_start" value="True" <?php if($ini['webcam']['raspicam_start'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_4 col_1">Raspicam size:</div>
			<div class="config_text row_4 col_3">
				<select name="raspicam_size" id="raspicam_size" size="1">						
					<option <?php if($ini['webcam']['raspicam_size'] == "320x240")			{echo " selected";} ?> >320x240</option>
					<option <?php if($ini['webcam']['raspicam_size'] == "640x480")			{echo " selected";} ?> >640x480</option>
					<option <?php if($ini['webcam']['raspicam_size'] == "1024x768")			{echo " selected";} ?> >1024x768</option>
					<option <?php if($ini['webcam']['raspicam_size'] == "1280x720")			{echo " selected";} ?> >1280x720</option>
					<option <?php if($ini['webcam']['raspicam_size'] == "1280x1024")		{echo " selected";} ?> >1280x1024</option>
					<option <?php if($ini['webcam']['raspicam_size'] == "2592x1944")		{echo " selected";} ?> >2592x1944</option>
				</select>
			</div>
			<div class="config_text row_3 col_4">Exposure:&nbsp;&nbsp;
				<select name="raspicam_exposure" id="raspicam_exposure" size="1">						
					<option <?php if($ini['webcam']['raspicam_exposure'] == "off")			{echo " selected";} ?> >off</option>
					<option <?php if($ini['webcam']['raspicam_exposure'] == "auto")			{echo " selected";} ?> >auto</option>
					<option <?php if($ini['webcam']['raspicam_exposure'] == "night")		{echo " selected";} ?> >night</option>
					<option <?php if($ini['webcam']['raspicam_exposure'] == "backlight")	{echo " selected";} ?> >backlight</option>
					<option <?php if($ini['webcam']['raspicam_exposure'] == "spotlight")	{echo " selected";} ?> >spotlight</option>
				</select>
			</div>
			
		</div>

<?php
// ##################################################################################
// Formular Pitmaster Einstellungen -------------------------------------------------
// ##################################################################################
?>			
        <div class="config big">
            <div class="headline">Pitmaster Einstellungen</div>
            <div class="headicon"><img src="../images/icons16x16/pitmaster.png" alt=""></div>
            <div class="config_text row_1 col_1">Temperatur:</div>
            <div class="config_text row_3 col_1">Regelkurve:</div>
            <div class="config_text row_4 col_1">Duty Cycle (%)</div>
			<div class="config_text row_4 col_2">min:</div>
            <div class="config_text row_1 col_6">Pitmaster Start:</div>
            <div class="config_text row_1 col_7"><input type="checkbox" name="pit_on" id="pit_on" value="True" <?php if($ini['ToDo']['pit_on'] == "True") {echo "checked=\"checked\"";}?> ></div>
            <div class="config_text row_3 col_6">Type:</div>
            <div class="config_text row_3 col_7">
                <select name="pit_type" id="pit_type" size="1">
                    <option <?php if($ini['Pitmaster']['pit_type'] == "servo")            	{echo " selected";} ?> value="servo">Servo</option>
                    <option <?php if($ini['Pitmaster']['pit_type'] == "fan_pwm")            {echo " selected";} ?> value="fan_pwm">PWM L&uuml;fter</option>
                    <option <?php if($ini['Pitmaster']['pit_type'] == "fan")            	{echo " selected";} ?> value="fan">L&uuml;fter</option>
                    <option <?php if($ini['Pitmaster']['pit_type'] == "io")                	{echo " selected";} ?> value="io">IO</option>
                    <option <?php if($ini['Pitmaster']['pit_type'] == "io_pwm")         	{echo " selected";} ?> value="io_pwm">IO mit PWM</option>
                </select>
            </div>
            <div class="config_text row_3 col_5"><input type="text" name="pit_curve" id="pit_curve" size="35" maxlength="50" value="<?php echo $ini['Pitmaster']['pit_curve'];?>"></div>
            
            <div class="config_text row_2 col_6">Kanal:</div>
			<div class="config_text row_2 col_7">
				<select name="pit_ch" id="pit_ch" size="1">
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "0")                {echo " selected";} ?> value="0" >Kanal0</option>
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "1")                {echo " selected";} ?> value="1" >Kanal1</option>
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "2")                {echo " selected";} ?> value="2" >Kanal2</option>
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "3")                {echo " selected";} ?> value="3" >Kanal3</option>
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "4")                {echo " selected";} ?> value="4" >Kanal4</option>
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "5")                {echo " selected";} ?> value="5" >Kanal5</option>
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "6")                {echo " selected";} ?> value="6" >Kanal6</option>
                    <option <?php if($ini['Pitmaster']['pit_ch'] == "7")                {echo " selected";} ?> value="7" >Kanal7</option>
                </select>
            </div>
            <div class="config_text row_1 col_3"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9.]/g,'');" name="pit_set" id="pit_set" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_set'];?>"></div>
            <div class="config_text row_1 col_4">Pause: </div>
            <div class="config_text row_2 col_3"><input type="text" onkeyup="this.value=this.value.replace(/[^0-9.]/g,'');" name="pit_man" id="pit_man" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_man'];?>"></div>
            <div class="config_text row_2 col_1">Manueller Wert: </div>
            <div class="config_text row_1 col_5"><input type="text" onkeyup="this.value=this.value.replace(/[^\d\.]/g, '');" name="pit_pause" id="pit_pause" size="5" maxlength="4" value="<?php echo $ini['Pitmaster']['pit_pause'];?>"></div>
            <div class="config_text row_4 col_2"></div>
            <div class="config_text row_4 col_3"><input type="text" onkeyup="this.value=this.value.replace(/\D/, '');" name="pit_pwm_min" id="pit_pwm_min" size="5" maxlength="3" value="<?php echo $ini['Pitmaster']['pit_pwm_min'];?>"></div>      
            <div class="config_text row_4 col_4">max:</div>
            <div class="config_text row_4 col_5"><input type="text" onkeyup="this.value=this.value.replace(/\D/, '');" name="pit_pwm_max" id="pit_pwm_max" size="5" maxlength="3" value="<?php echo $ini['Pitmaster']['pit_pwm_max'];?>" ></div>  
            <div class="config_text row_5 col_1">Servoimpuls (µs) min:</div>
            <div class="config_text row_5 col_3"><input type="text" onkeyup="this.value=this.value.replace(/\D/, '');" name="pit_servo_min" id="pit_servo_min" size="5" maxlength="4" value="<?php echo $ini['Pitmaster']['pit_servo_min'];?>"></div>      
            <div class="config_text row_7 col_6">PID Regelung:</div>
			<div class="config_text row_7 col_7"><input type="checkbox" name="pit_controller_type" id="pit_controller_type" value="True" <?php if($ini['Pitmaster']['pit_controller_type'] == "PID") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_6 col_6">Deckel&uuml;berwachung:</div>
			<div class="config_text row_6 col_7"><input type="checkbox" name="pit_open_lid_detection" id="pit_open_lid_detection" value="True" <?php if($ini['Pitmaster']['pit_open_lid_detection'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_5 col_4">max:</div>
            <div class="config_text row_5 col_5"><input type="text" onkeyup="this.value=this.value.replace(/\D/, '');" name="pit_servo_max" id="pit_servo_max" size="5" maxlength="4" value="<?php echo $ini['Pitmaster']['pit_servo_max'];?>"></div>  
			<div class="config_text row_5 col_6">Ansteuerung umkehren:</div>
            <div class="config_text row_5 col_7"><input type="checkbox" name="pit_inverted" id="pit_inverted" value="True" <?php if($ini['Pitmaster']['pit_inverted'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_6 col_1">Kp:</div>
			<div class="config_text row_6 col_1_5"><input type="text" onkeyup="this.value=this.value.replace(/,/, '.');" name="pit_kp" id="pit_kp" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_kp'];?>"></div>
			<div class="config_text row_6 col_2">Ki:</div>
			<div class="config_text row_6 col_3"><input type="text" onkeyup="this.value=this.value.replace(/,/, '.');" name="pit_ki" id="pit_ki" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_ki'];?>"></div>
			<div class="config_text row_6 col_4">Kd:</div>
			<div class="config_text row_6 col_5"><input type="text" onkeyup="this.value=this.value.replace(/,/, '.');" name="pit_kd" id="pit_kd" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_kd'];?>"></div>
			<div class="config_text row_7 col_1">Kp_a:</div>
			<div class="config_text row_7 col_1_5"><input type="text" onkeyup="this.value=this.value.replace(/,/, '.');" name="pit_kp_a" id="pit_kp_a" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_kp_a'];?>"></div>
			<div class="config_text row_7 col_2">Ki_a:</div>
			<div class="config_text row_7 col_3"><input type="text" onkeyup="this.value=this.value.replace(/,/, '.');" name="pit_ki_a" id="pit_ki_a" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_ki_a'];?>"></div>
			<div class="config_text row_7 col_4">Kd_a:</div>
			<div class="config_text row_7 col_5"><input type="text" onkeyup="this.value=this.value.replace(/,/, '.');" name="pit_kd_a" id="pit_kd_a" size="5" maxlength="5" value="<?php echo $ini['Pitmaster']['pit_kd_a'];?>"></div>
			<div class="config_text row_4 col_6">IO GPIO:</div>
			<div class="config_text row_4 col_7">
				<select name="pit_io_gpio" id="pit_io_gpio" size="1">
                    <option <?php if($ini['Pitmaster']['pit_io_gpio'] == "2")                {echo " selected";} ?> value="2" >GPIO2</option>
                    <option <?php if($ini['Pitmaster']['pit_io_gpio'] == "4")                {echo " selected";} ?> value="4" >GPIO4</option>
                </select>
            </div>
		</div>			
<?php
// ##################################################################################
// Formular Allgemeine Einstellungen ------------------------------------------------
// ##################################################################################
?>
		<div class="config middle">
			<div class="headline">Allgemeine Einstellungen</div>		
			<div class="headicon"><img src="../images/icons16x16/settings.png" alt=""></div>
			<div class="config_text row_2 col_1">Neues Logfile bei Neustart:</div>
			<div class="config_text row_3 col_1">Nach Updates suchen:</div>
			<div class="config_text row_3 col_4"><input type="checkbox" name="checkUpdate" value="True" <?php if($ini['update']['checkupdate'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_2 col_6">CPU Auslastung anzeigen:</div>
			<div class="config_text row_2 col_7"><input type="checkbox" name="showcpulast" value="True" <?php if($ini['Hardware']['showcpulast'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_4 col_1">Kanal anzeigen:</div>
			<div class="config_text row_1 col_1">Hardware Version:</div>
			<div class="config_text row_1 col_4"><input type="radio" name="hardware_version" value="v1" <?php if($ini['Hardware']['version'] == "v1") {echo "checked=\"checked\"";}?> > v1 <input type="radio" name="hardware_version" value="v2" <?php if($ini['Hardware']['version'] == "v2") {echo "checked=\"checked\"";}?> > v2 <input type="radio" name="hardware_version" value="v3" <?php if($ini['Hardware']['version'] == "v3") {echo "checked=\"checked\"";}?> > v3</div>
			<div class="config_text row_1 col_6">Beeper:</div>
			<div class="config_text row_1 col_7"><input type="checkbox" name="beeper_enabled" value="True" <?php if($ini['Sound']['beeper_enabled'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_2 col_4"><input type="checkbox" name="new_logfile_restart" value="True" <?php if($ini['Logging']['write_new_log_on_restart'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_4 col_7">
				ch0&nbsp;<input type="checkbox" name="ch_show0" id="show_ch0" value="True" <?php if($ini['ch_show']['ch0'] == "True") {echo "checked=\"checked\"";}?> >&nbsp;&nbsp;
				ch1&nbsp;<input type="checkbox" name="ch_show1" id="show_ch1" value="True" <?php if($ini['ch_show']['ch1'] == "True") {echo "checked=\"checked\"";}?> >&nbsp;&nbsp;
				ch2&nbsp;<input type="checkbox" name="ch_show2" id="show_ch2" value="True" <?php if($ini['ch_show']['ch2'] == "True") {echo "checked=\"checked\"";}?> >&nbsp;&nbsp;
				ch3&nbsp;<input type="checkbox" name="ch_show3" id="show_ch3" value="True" <?php if($ini['ch_show']['ch3'] == "True") {echo "checked=\"checked\"";}?> >&nbsp;&nbsp;
				ch4&nbsp;<input type="checkbox" name="ch_show4" id="show_ch4" value="True" <?php if($ini['ch_show']['ch4'] == "True") {echo "checked=\"checked\"";}?> >&nbsp;&nbsp;
				ch5&nbsp;<input type="checkbox" name="ch_show5" id="show_ch5" value="True" <?php if($ini['ch_show']['ch5'] == "True") {echo "checked=\"checked\"";}?> >&nbsp;&nbsp;
				ch6&nbsp;<input type="checkbox" name="ch_show6" id="show_ch6" value="True" <?php if($ini['ch_show']['ch6'] == "True") {echo "checked=\"checked\"";}?> >&nbsp;&nbsp;
				ch7&nbsp;<input type="checkbox" name="ch_show7" id="show_ch7" value="True" <?php if($ini['ch_show']['ch7'] == "True") {echo "checked=\"checked\"";}?> >
			</div>
		</div>
<?php
// ##################################################################################
// Display Einstellungen ------------------------------------------------------------
// ##################################################################################
?>
		<div class="config small">
			<div class="headline">Display Einstellungen</div>		
			<div class="headicon"><img src="../images/icons16x16/display.png" alt=""></div>
			<div class="config_text row_1 col_6">LCD Anzeige:</div>
			<div class="config_text row_1 col_7"><input type="checkbox" name="lcd_show" id="lcd_present" value="True" <?php if($ini['Display']['lcd_present'] == "True") {echo "checked=\"checked\"";}?> ></div>
			<div class="config_text row_2 col_6">LCD Type:</div>
			<div class="config_text row_2 col_7">
				<select name="lcd_type" id="lcd_type" size="1">
					<option <?php if($ini['Display']['lcd_type'] == "wlt_2_lcd_204.py") {echo " selected";} ?> value="wlt_2_lcd_204.py">4x20 HD44780</option>
                    <option <?php if($ini['Display']['lcd_type'] == "wlt_2_nextion.py") {echo " selected";} ?> value="wlt_2_nextion.py">Nextion LCD</option>
                </select>
            </div>
			<div class="config_text row_1 col_1">Start Seite:</div>
			<div class="config_text row_1 col_3">
				<select name="lcd_start_page" id="startpage" size="1">
					<option <?php if($ini['Display']['start_page'] == "main") {echo " selected";} ?> value="main">Men&uuml;</option>
                    <option <?php if($ini['Display']['start_page'] == "temp") {echo " selected";} ?> value="temp">Temperatur</option>
                </select>
            </div>						
		</div>
<?php
// ##################################################################################
// Einstellungen Überprüfen und Felder aktivieren/deaktivieren ----------------------
// ##################################################################################
?>
	<script type="text/javascript" src="../js/config.php.js"></script>
 
<?php

// ##################################################################################
// Speichern/Zurück Button ----------------------------------------------------------
// ##################################################################################
?>
		<br>
			<table align="center" width="80%"><tr><td width="20%"></td>
				<td align="center">
					<input type="submit" class=button_save name="save"  value="" onclick="enableallcheckbox()">
					<input type="submit" class=button_back name="back"  value=""> </td>
				<td width="20%"></td></tr>
			</table>
		<br>		
	</form>
</div>
<?php
	}
// ----------------------------------------------------------------------------------

include("../footer.php");
?>
