<?php

namespace SHC\Form\Forms;

//Imports
use RWF\Core\RWF;
use RWF\Form\DefaultHtmlForm;
use RWF\Form\FormElements\OnOffOption;
use RWF\Form\FormElements\TextField;
use SHC\Form\FormElements\GroupPremissonChooser;
use SHC\Form\FormElements\IconChooser;
use SHC\Form\FormElements\ProtocolChooser;
use SHC\Form\FormElements\RoomChooser;
use SHC\Form\FormElements\SwitchPointsChooser;
use SHC\Room\Room;
use SHC\Switchable\Switchables\RadioSocket;

/**
 * Funksteckdose Formular
 *
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.0.0-0
 * @version    2.0.0-0
 */
class RadiosocketForm extends DefaultHtmlForm {

    /**
     * @param RadioSocket $radioSocket
     */
    public function __construct(RadioSocket $radioSocket = null) {

        //Konstruktor von TabbedHtmlForm aufrufen
        parent::__construct();

        RWF::getLanguage()->disableAutoHtmlEndocde();

        //Name der Funksteckdose
        $name = new TextField('name', ($radioSocket instanceof RadioSocket ? $radioSocket->getName() : ''), array('minlength' => 3, 'maxlength' => 25));
        $name->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.name'));
        $name->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.name.description'));
        $name->requiredField(true);
        $this->addFormElement($name);

        //Icon
        $icon = new IconChooser('icon', ($radioSocket instanceof RadioSocket ? $radioSocket->getIcon() : ''));
        $icon->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.icon'));
        $icon->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.icon.description'));
        $icon->requiredField(true);
        $this->addFormElement($icon);

        //Raum
        $room = new RoomChooser('room', ($radioSocket instanceof RadioSocket && $radioSocket->getRoom() instanceof Room ? $radioSocket->getRoom()->getId() : null));
        $room->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.room'));
        $room->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.room.description'));
        $room->requiredField(true);
        $this->addFormElement($room);

        //Protokoll
        $protocol = new ProtocolChooser('protocol', ($radioSocket instanceof RadioSocket ? $radioSocket->getProtocol() : null));
        $protocol->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.protocol'));
        $protocol->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.protocol.description'));
        $protocol->requiredField(true);
        $this->addFormElement($protocol);

        //Systemcode
        $systemCode = new TextField('systemCode', ($radioSocket instanceof RadioSocket ? $radioSocket->getSystemCode() : ''), array('minlength' => 3, 'maxlength' => 15));
        $systemCode->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.systemCode'));
        $systemCode->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.systemCode.description'));
        $systemCode->requiredField(true);
        $this->addFormElement($systemCode);

        //Device Code
        $deviceCode = new TextField('deviceCode', ($radioSocket instanceof RadioSocket ? $radioSocket->getDeviceCode() : ''), array('minlength' => 1, 'maxlength' => 15));
        $deviceCode->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.deviceCode'));
        $deviceCode->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.deviceCode.description'));
        $deviceCode->requiredField(true);
        $this->addFormElement($deviceCode);

        //Schaltpunkte Auswahl
        $switchPoints = new SwitchPointsChooser('switchPoints', ($radioSocket instanceof RadioSocket ? $radioSocket->listSwitchPoints() : array()));
        $switchPoints->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.switchPoints'));
        $switchPoints->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.switchPoints.description'));
        $switchPoints->requiredField(true);
        $this->addFormElement($switchPoints);

        //Aktiv/Inaktiv
        $enabled = new OnOffOption('enabled', ($radioSocket instanceof RadioSocket ? $radioSocket->isEnabled() : true));
        $enabled->setActiveInactiveLabel();
        $enabled->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.active'));
        $enabled->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.active.description'));
        $enabled->requiredField(true);
        $this->addFormElement($enabled);

        //Sichtbarkeit
        $visibility = new OnOffOption('visibility', ($radioSocket instanceof RadioSocket ? $radioSocket->isVisible() : true));
        $visibility->setOnOffLabel();
        $visibility->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.visibility'));
        $visibility->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.visibility.description'));
        $visibility->requiredField(true);
        $this->addFormElement($visibility);

        //erlaubte Benutzer
        $allowedUsers = new GroupPremissonChooser('allowedUsers', ($radioSocket instanceof RadioSocket ? $radioSocket->listAllowedUserGroups() : array()));
        $allowedUsers->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.allowedUsers'));
        $allowedUsers->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addRadioSocket.allowedUsers.description'));
        $allowedUsers->requiredField(true);
        $this->addFormElement($allowedUsers);

        RWF::getLanguage()->enableAutoHtmlEndocde();
    }
}