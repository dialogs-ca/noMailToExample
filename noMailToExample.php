<?php
/**
 * noMailToExample : just don't send email to example.org or example.com
 * http://example.org/ is a great tool for demonstration and test, but sending an email to user@example.org: you receive 4 hour after a notification
 * This plugin just disable sending email to this website, then you can use it when testing syste.
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2016 Denis Chenu <http://www.sondages.pro>
 * @license MIT
 * @version 0.0.2
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * The MIT License
 */
class noMailToExample extends \ls\pluginmanager\PluginBase
{
    static protected $description = 'Don\t send email to example.(com|org)';
    static protected $name = 'noMailToExample';

    public function init()
    {
        $this->subscribe('beforeTokenEmail');
    }

    /**
     * Set event send to false when sending an email to example.(com|org)
     * @link https://manual.limesurvey.org/BeforeTokenEmail
     */
    public function beforeTokenEmail()
    {
        // TODO not in the plugin
        /* Quick sytem to log email */
        $aToken=$this->event->get("token");
        if($aToken){
            $sLog='beforeTokenEmail for '.$this->event->get("survey");
            $sLog.=',';
            $sLog.=isset($aToken['tid']) ? $aToken['tid'] : "notset";
            if(Yii::app() instanceof CConsoleApplication) {
                $sLog.=',console';
            } else {
                $sLog.=','.Yii::app()->session['loginID'];
            }
            $sLog.=',';
            $sLog.=isset($aToken['participant_id']) ? $aToken['participant_id'] : "notset";
            $sLog.=',';
            $sLog.=$this->event->get("type");
            $sLog.=',';
            $sLog.=isset($aToken['email']) ? $aToken['email'] : "notset";
            if(Yii::app() instanceof CConsoleApplication) {
                $sLog.=',console';
                $sLog.=',console';
            } else {
                if(Yii::app()->getController()) {
                    $sLog.=','. Yii::app()->getController()->getId();
                    if(Yii::app()->getController()->getAction()) {
                        $sLog.=','. Yii::app()->getController()->getAction()->getId();
                    } else {
                        $sLog.=',notset';
                    }
                } else {
                    $sLog.=',notset';
                    $sLog.=',notset';
                }
            }
            Yii::log($sLog, 'info','application.plugins.noMailToExample');
        }
        // END

        $emailTos=$this->event->get("to");
        /* @var string[] no example.(org|com) from the list */
        $cleanedEmailTos=array();
        foreach($emailTos as $emailTo){
            if (strpos($emailTo, '<') ){
                $emailOnly=trim(substr($emailTo,strpos($emailTo,'<')+1,strpos($emailTo,'>')-1-strpos($emailTo,'<')));
            }else{
                $emailOnly=trim($emailTo);
            }
            /* @var string only domain from email */
            $domainName = strtolower(substr(strrchr($emailOnly, "@"), 1));
            if($domainName=='example.com' || $domainName=='example.org'){
                $this->event->set("send",false);
            }else{
                $cleanedEmailTos[]=$emailTo;
            }
        }
        /* If we have a list of email with some example.(org|com) and other : set new list to cleaned list */
        if($this->event->get("send",true)===false && !empty($cleanedEmailTos)){
            $this->event->set("send",true);
            $this->event->set("to",$cleanedEmailTos);
        }
    }
}
