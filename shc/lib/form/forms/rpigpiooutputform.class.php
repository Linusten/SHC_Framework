<?php

namespace SHC\Form\Forms;

//Imports
use RWF\Core\RWF;
use RWF\Form\DefaultHtmlForm;
use RWF\Form\FormElements\OnOffOption;
use RWF\Form\FormElements\TextField;
use SHC\Form\FormElements\GroupPremissonChooser;
use SHC\Form\FormElements\IconChooser;
use SHC\Form\FormElements\RoomChooser;
use SHC\Form\FormElements\RPiGpioChooser;
use SHC\Form\FormElements\SwitchPointsChooser;
use SHC\Form\FormElements\SwitchServerChooser;
use SHC\Room\Room;
use SHC\Switchable\Switchables\RpiGpioOutput;

/**
 * RPi GPIO Formular
 *
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.0.0-0
 * @version    2.0.0-0
 */
class RpiGpioOutputForm extends DefaultHtmlForm {

    /**
     * @param RpiGpioOutput $rpiGpioOutput
     */
    public function __construct(RpiGpioOutput $rpiGpioOutput = null) {

        //Konstruktor von TabbedHtmlForm aufrufen
        parent::__construct();

        RWF::getLanguage()->disableAutoHtmlEndocde();

        //Name des GPIO
        $name = new TextField('name', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->getName() : ''), array('minlength' => 3, 'maxlength' => 25));
        $name->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.name'));
        $name->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.name.description'));
        $name->requiredField(true);
        $this->addFormElement($name);

        //Icon
        $icon = new IconChooser('icon', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->getIcon() : ''));
        $icon->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.icon'));
        $icon->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.icon.description'));
        $icon->requiredField(true);
        $this->addFormElement($icon);

        //Raum
        $room = new RoomChooser('room', ($rpiGpioOutput instanceof RpiGpioOutput && $rpiGpioOutput->getRoom() instanceof Room ? $rpiGpioOutput->getRoom()->getId() : null));
        $room->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.room'));
        $room->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.room.description'));
        $room->requiredField(true);
        $this->addFormElement($room);

        //Schaltserver Auswahl
        $switchServer = new SwitchServerChooser('switchServer', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->getSwitchServer() : 0), SwitchServerChooser::FILTER_WRITEGPIO);
        $switchServer->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.switchServer'));
        $switchServer->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.switchServer.description'));
        $switchServer->requiredField(true);
        $this->addFormElement($switchServer);

        //GPIO
        $gpio = new RPiGpioChooser('gpio', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->getPinNumber() : -1));
        $gpio->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.gpioPin'));
        $gpio->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.gpioPin.description'));
        $gpio->requiredField(true);
        $this->addFormElement($gpio);

        //Schaltpunkte Auswahl
        $switchPoints = new SwitchPointsChooser('switchPoints', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->listSwitchPoints() : array()));
        $switchPoints->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.switchPoints'));
        $switchPoints->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.switchPoints.description'));
        $switchPoints->requiredField(true);
        $this->addFormElement($switchPoints);

        //Aktiv/Inaktiv
        $enabled = new OnOffOption('enabled', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->isEnabled() : true));
        $enabled->setActiveInactiveLabel();
        $enabled->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.active'));
        $enabled->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.active.description'));
        $enabled->requiredField(true);
        $this->addFormElement($enabled);

        //Sichtbarkeit
        $visibility = new OnOffOption('visibility', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->isVisible() : true));
        $visibility->setOnOffLabel();
        $visibility->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.visibility'));
        $visibility->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.visibility.description'));
        $visibility->requiredField(true);
        $this->addFormElement($visibility);

        //erlaubte Benutzer
        $allowedUsers = new GroupPremissonChooser('allowedUsers', ($rpiGpioOutput instanceof RpiGpioOutput ? $rpiGpioOutput->listAllowedUserGroups() : array()));
        $allowedUsers->setTitle(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.allowedUsers'));
        $allowedUsers->setDescription(RWF::getLanguage()->get('acp.switchableManagement.form.addGpioOutput.allowedUsers.description'));
        $allowedUsers->requiredField(true);
        $this->addFormElement($allowedUsers);

        RWF::getLanguage()->enableAutoHtmlEndocde();
    }
}