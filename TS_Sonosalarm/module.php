<?php
	class TS_SonosAlarm extends IPSModule
	{
		public function Create()		
    {
			//Never delete this line!
			parent::Create();
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
        //You cannot use variables here. Just static values.
      $this->RegisterPropertyInteger("Sonos_ID", 0 );
      $this->RegisterPropertyInteger("Trigger", 0);
			$this->RegisterPropertyString("Pfad", "10.10.0.11:3777/user/ansage/");
			$this->RegisterPropertyString("Alarm1", "alarm-sirene.mp3");
      $this->RegisterPropertyInteger("AlarmVolume", 5);
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

      $steuer_id =  $this->ReadPropertyInteger("Trigger");

$alarmskript= '<?php 
$SonosId = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Sonos_ID");
$ip = IPS_GetProperty($SonosId, "IPAddress");
if (Sys_Ping($ip, 1000) == true) {
    $pfad = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Pfad");
    $alarmvol = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "AlarmVolume");
    $alarmdatei = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Alarm1");

    SNS_PlayFiles($SonosId, "[\"http://".$pfad.$alarmdatei."\"]",$alarmvol);
}
';
 $alarmskript_ID = $this->RegisterScript("Alarm_abspielen", "Alarm_abspielen", $alarmskript);
 IPS_SetHidden($alarmskript_ID,true);

if ($steuer_id <> 0) {
    $this->Registerevent2($alarmskript_ID,$steuer_id); 
 }

  $sk_id=IPS_GetObjectIDByIdent('Alarm_abspielen', $this->InstanceID);
  if ( IPS_ScriptExists($sk_id)){
      IPS_SetScriptContent ( $sk_id, $alarmskript);
  }
 

    }

		private function Registerevent2($TargetID,$Ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_true",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_true", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_true", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_true");
//      IPS_SetEventTrigger($eid, 1, $Ziel_id);        //Bei Änderung von Variable 
      IPS_SetEventTrigger($eid, 4, $Ziel_id);        //Bei bestimmten Wert
      @IPS_SetEventTriggerValue($eid, true);       
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventAction($eid, '{7938A5A2-0981-5FE0-BE6C-8AA610D654EB}', []);			
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }

 }   

