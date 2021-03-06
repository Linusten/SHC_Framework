<?php

namespace SHC\Condition;

//Imports
use RWF\Util\String;
use SHC\Core\SHC;
use RWF\XML\XmlFileManager;

/**
 * Beingungen verwalten
 * 
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.0.0-0
 * @version    2.0.0-0
 */
class ConditionEditor {

    /**
     * nach ID sortieren
     * 
     * @var String
     */
    const SORT_BY_ID = 'id';

    /**
     * nach Namen sortieren
     * 
     * @var String
     */
    const SORT_BY_NAME = 'name';

    /**
     * nicht sortieren
     * 
     * @var String
     */
    const SORT_NOTHING = 'unsorted';

    /**
     * Bedingungen
     * 
     * @var Array 
     */
    protected $conditions = array();

    /**
     * Singleton Instanz
     * 
     * @var \SHC\Condition\ConditionEditor
     */
    protected static $instance = null;

    protected function __construct() {

        $this->loadData();
    }

    /**
     * laedt die Bedingungen aus den XML Daten und erzeugt die Objekte
     */
    public function loadData() {

        $xml = XmlFileManager::getInstance()->getXmlObject(SHC::XML_CONDITIONS);

        //Daten einlesen
        foreach ($xml->condition as $condition) {

            //Variablen Vorbereiten
            $class = (string) $condition->class;

            $data = array();
            foreach ($condition as $index => $value) {

                if (!in_array($index, array('id', 'name', 'class', 'enabled'))) {

                    $data[$index] = (string) $value;
                }
            }

            $this->conditions[(int) $condition->id] = new $class(
                    (int) $condition->id, (string) $condition->name, $data, ((int) $condition->enabled == 1 ? true : false)
            );
        }
    }

    /**
     * gibt die Bedingung mit der ID zurueck
     * 
     * @param  Integer $id ID
     * @return \SHC\Condition\Condition
     */
    public function getConditionByID($id) {

        if (isset($this->conditions[$id])) {

            return $this->conditions[$id];
        }
        return null;
    }

    /**
     * prueft ob der Name der Bedingung schon verwendet wird
     * 
     * @param  String  $name Name
     * @return Boolean
     */
    public function isConditionNameAvailable($name) {

        foreach ($this->conditions as $condition) {

            /* @var $condition \SHC\Condition\Condition */
            if (String::toLower($condition->getName()) == String::toLower($name)) {

                return false;
            }
        }
        return true;
    }

    /**
     * gibt eine Liste mit allen Bedingungen zurueck
     * 
     * @param  String $orderBy Art der Sortierung (
     *      id => nach ID sorieren, 
     *      name => nach Namen sortieren,
     *      unsorted => unsortiert
     *  )
     * @return Array
     */
    public function listConditions($orderBy = 'id') {

        if ($orderBy == 'id') {

            //nach ID sortieren
            $conditions = $this->conditions;
            ksort($conditions, SORT_NUMERIC);
            return $conditions;
        } elseif ($orderBy == 'name') {

            //nach Namen sortieren
            $conditions = $this->conditions;

            //Sortierfunktion
            $orderFunction = function($a, $b) {

                if ($a->getName() == $b->getName()) {

                    return 0;
                }

                if ($a->getName() < $b->getName()) {

                    return -1;
                }
                return 1;
            };
            usort($conditions, $orderFunction);
            return $conditions;
        }
        return $this->conditions;
    }

    /**
     * erstellt eine neue Bedingung
     * 
     * @param  String  $class   Klassenname
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @param  Array   $data    Zusatzdaten
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    protected function addCondition($class, $name, $enabled, array $data = array()) {

        //Ausnahme wenn Name der Bedingung schon belegt
        if (!$this->isConditionNameAvailable($name)) {

            throw new \Exception('Der Name der Bedingung ist schon vergeben', 1502);
        }

        //XML Daten Laden
        $xml = XmlFileManager::getInstance()->getXmlObject(SHC::XML_CONDITIONS, true);

        //Autoincrement
        $nextId = (int) $xml->nextAutoIncrementId;
        $xml->nextAutoIncrementId = $nextId + 1;

        //Datensatz erstellen
        $condition = $xml->addChild('condition');
        $condition->addChild('id', $nextId);
        $condition->addChild('class', $class);
        $condition->addChild('name', $name);
        $condition->addChild('enabled', ($enabled == true ? 1 : 0));

        //Daten hinzufuegen
        foreach ($data as $tag => $value) {

            if (!in_array($tag, array('id', 'name', 'class', 'enabled'))) {

                $condition->addChild($tag, $value);
            }
        }

        //Daten Speichern
        $xml->save();
        return true;
    }

    /**
     * bearbeitet eine Bedingung
     * 
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @param  Array   $data    Zusatzdaten
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editCondition($id, $name, $enabled, array $data = array()) {

        //XML Daten Laden
        $xml = XmlFileManager::getInstance()->getXmlObject(SHC::XML_CONDITIONS, true);

        //Server Suchen
        foreach ($xml->condition as $condition) {

            if ((int) $condition->id == $id) {

                //Name
                if ($name !== null) {

                    //Ausnahme wenn Name der Bedingung schon belegt
                    if ((string) $condition->name != $name && !$this->isConditionNameAvailable($name)) {

                        throw new \Exception('Der Name der Bedingung ist schon vergeben', 1502);
                    }

                    $condition->name = $name;
                }

                //Aktiv
                if ($enabled !== null) {

                    $condition->enabled = ($enabled == true ? 1 : 0);
                }

                //Zusatzdaten
                foreach($data as $tag => $value) {
                    
                    if (!in_array($tag, array('id', 'name', 'class', 'enabled'))) {
                        
                        if($value !== null) {
                            
                            $condition->$tag = $value;
                        }
                    }
                }

                //Daten Speichern
                $xml->save();
                return true;
            }
        }
        return false;
    }

    /**
     * erstellt eine neue Datumsbedingung
     * 
     * @param  String  $name    Name
     * @param  String  $start   Startdatum
     * @param  String  $end     Enddatum
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addDateCondition($name, $start, $end, $enabled) {

        //Daten vorbereiten
        $data = array(
            'start' => $start,
            'end' => $end
        );

        //Datensatz erstellen
        return $this->addCondition('\SHC\Condition\Conditions\DateCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Datumsbedingung
     * 
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  String  $start   Startdatum
     * @param  String  $end     Enddatum
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editDateCondition($id, $name = null, $start = null, $end = null, $enabled = null) {
        
        //Daten vorbereiten
        $data = array(
            'start' => $start,
            'end' => $end
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Wochentagsbedingung
     * 
     * @param  String  $name    Name
     * @param  String  $start   Starttag
     * @param  String  $end     Endtag
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addDayOfWeekCondition($name, $start, $end, $enabled) {

        //Daten vorbereiten
        $data = array(
            'start' => $start,
            'end' => $end
        );

        //Datensatz erstellen
        return $this->addCondition('\SHC\Condition\Conditions\DayOfWeekCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Wochentagsbedingung
     * 
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  String  $start   Starttag
     * @param  String  $end     Endtag
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editDayOfWeekCondition($id, $name = null, $start = null, $end = null, $enabled = null) {
        
        //Daten vorbereiten
        $data = array(
            'start' => $start,
            'end' => $end
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Zeitbedingung
     * 
     * @param  String  $name    Name
     * @param  String  $start   Startzeit
     * @param  String  $end     Endzeit
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addTimeOfDayCondition($name, $start, $end, $enabled) {

        //Daten vorbereiten
        $data = array(
            'start' => $start,
            'end' => $end
        );

        //Datensatz erstellen
        return $this->addCondition('\SHC\Condition\Conditions\TimeOfDayCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Zeitbedingung
     * 
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  String  $start   Startzeit
     * @param  String  $end     Endzeit
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editTimeOfDayCondition($id, $name = null, $start = null, $end = null, $enabled = null) {
        
        //Daten vorbereiten
        $data = array(
            'start' => $start,
            'end' => $end
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Bedingung fuer den Zeitraum zwischen Sonnenauf- und Sonnenuntergang
     * 
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addSunriseSunsetCondition($name, $enabled) {

        //Datensatz erstellen
        return $this->addCondition('\SHC\Condition\Conditions\SunriseSunsetCondition', $name, $enabled);
    }

    /**
     * bearbeitet eine Bedingung fuer den Zeitraum zwischen Sonnenauf- und Sonnenuntergang
     * 
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editSunriseSunsetCondition($id, $name = null, $enabled = null) {

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled);
    }

    /**
     * erstellt eine neue Bedingung fuer den Zeitraum zwischen Sonnenunter- und Sonnenaufgang
     * 
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addSunsetSunriseCondition($name, $enabled) {

        //Datensatz erstellen
        return $this->addCondition('\SHC\Condition\Conditions\SunsetSunriseCondition', $name, $enabled);
    }

    /**
     * bearbeitet eine Bedingung fuer den Zeitraum zwischen Sonnenunter- und Sonnenaufgang
     * 
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editSunsetSunriseCondition($id, $name = null, $enabled = null) {
        
        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled);
    }

    /**
     * erstellt eine neue Bedingung die prueft das niemand zu Hause ist
     *
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addNobodyAtHomeCondition($name, $enabled) {

        //Datensatz erstellen
        return $this->addCondition('\SHC\Condition\Conditions\NobodyAtHomeCondition', $name, $enabled);
    }

    /**
     * bearbeitet eine Bedingung die prueft das niemand zu Hause ist
     *
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editNobodyAtHomeCondition($id, $name = null, $enabled = null) {

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled);
    }

    /**
     * erstellt eine neue Bedingung fuer den Zeitraum zwischen Sonnenunter- und Sonnenaufgang
     *
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addUserAtHomeCondition($name, array $users, $enabled) {

        //Daten vorbereiten
        $data = array(
            'users' => implode(',', $users)
        );

        //Datensatz erstellen
        return $this->addCondition('\SHC\Condition\Conditions\UserAtHomeCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Bedingung fuer den Zeitraum zwischen Sonnenunter- und Sonnenaufgang
     *
     * @param  Integer $id      ID
     * @param  String  $name    Name
     * @param  Boolean $enabled Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editUserAtHomeCondition($id, $name = null, array $users = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'users' => ($users !== null ? implode(',', $users) : null)
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $humidity  Luftfeuchte als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addHumidityGreaterThanCondition($name, array $sensorIds, $humidity, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'humidity'=> $humidity
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\HumidityGreaterThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id        ID
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $humidity  Luftfeuchte als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editHumidityGreaterThanCondition($id, $name = null, array $sensorIds = null, $humidity = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'humidity'=> $humidity
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $humidity  Luftfeuchte als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addHumidityLowerThanCondition($name, array $sensorIds, $humidity, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'humidity'=> $humidity
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\HumidityLowerThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id        ID
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $humidity  Luftfeuchte als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editHumidityLowerThanCondition($id, $name = null, array $sensorIds = null, $humidity = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'humidity'=> $humidity
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name            Name
     * @param  Array   $sensorIds       Liste mit Sensoren
     * @param  Integer $lightIntensity  Lichtstaerke als Grenzwert
     * @param  Boolean $enabled         Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addLightIntensityGreaterThanCondition($name, array $sensorIds, $lightIntensity, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'lightIntensity'=> $lightIntensity
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\LightIntensityGreaterThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id              ID
     * @param  String  $name            Name
     * @param  Array   $sensorIds       Liste mit Sensoren
     * @param  Integer $lightIntensity  Lichtstaerke als Grenzwert
     * @param  Boolean $enabled         Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editLightIntensityGreaterThanCondition($id, $name = null, array $sensorIds = null, $lightIntensity = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'lightIntensity'=> $lightIntensity
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name            Name
     * @param  Array   $sensorIds       Liste mit Sensoren
     * @param  Integer $lightIntensity  Lichtstaerke als Grenzwert
     * @param  Boolean $enabled         Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addLightIntensityLowerThanCondition($name, array $sensorIds, $lightIntensity, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'lightIntensity'=> $lightIntensity
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\LightIntensityLowerThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id              ID
     * @param  String  $name            Name
     * @param  Array   $sensorIds       Liste mit Sensoren
     * @param  Integer $lightIntensity  Lichtstaerke als Grenzwert
     * @param  Boolean $enabled         Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editLightIntensityLowerThanCondition($id, $name = null, array $sensorIds = null, $lightIntensity = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'lightIntensity'=> $lightIntensity
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $moisture  Feuchtigkeit als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addMoistureGreaterThanCondition($name, array $sensorIds, $moisture, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'moisture'=> $moisture
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\MoistureGreaterThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id        ID
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $moisture  Feuchtigkeit als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editMoistureGreaterThanCondition($id, $name = null, array $sensorIds = null, $moisture = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'moisture'=> $moisture
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $moisture  Feuchtigkeit als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addLMoistureLowerThanCondition($name, array $sensorIds, $moisture, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'moisture'=> $moisture
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\MoistureLowerThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id        ID
     * @param  String  $name      Name
     * @param  Array   $sensorIds Liste mit Sensoren
     * @param  Integer $moisture  Feuchtigkeit als Grenzwert
     * @param  Boolean $enabled   Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editMoistureLowerThanCondition($id, $name = null, array $sensorIds = null, $moisture = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'moisture'=> $moisture
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name         Name
     * @param  Array   $sensorIds    Liste mit Sensoren
     * @param  Float   $temperature  Temperatur als Grenzwert
     * @param  Boolean $enabled      Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addTemperatureGreaterThanCondition($name, array $sensorIds, $temperature, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'temperature'=> $temperature
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\TemperatureGreaterThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id           ID
     * @param  String  $name         Name
     * @param  Array   $sensorIds    Liste mit Sensoren
     * @param  Float   $temperature  Temperatur als Grenzwert
     * @param  Boolean $enabled      Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editTemperatureGreaterThanCondition($id, $name = null, array $sensorIds = null, $temperature = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'temperature'=> $temperature
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * erstellt eine neue Vergleichsbedingung
     *
     * @param  String  $name         Name
     * @param  Array   $sensorIds    Liste mit Sensoren
     * @param  Float   $temperature  Temperatur als Grenzwert
     * @param  Boolean $enabled      Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function addTemperatureLowerThanCondition($name, array $sensorIds, $temperature, $enabled) {

        //Daten vorbereiten
        $data = array(
            'sensors' => implode(',', $sensorIds),
            'temperature'=> $temperature
        );

        //Datensatz bearbeiten
        return $this->addCondition('\SHC\Condition\Conditions\TemperatureLowerThanCondition', $name, $enabled, $data);
    }

    /**
     * bearbeitet eine Vergleichsbedingung
     *
     * @param  Integer $id           ID
     * @param  String  $name         Name
     * @param  Array   $sensorIds    Liste mit Sensoren
     * @param  Float   $temperature  Temperatur als Grenzwert
     * @param  Boolean $enabled      Aktiv
     * @return Boolean
     * @throws \Exception, \RWF\Xml\Exception\XmlException
     */
    public function editTemperatureLowerThanCondition($id, $name = null, array $sensorIds = null, $temperature = null, $enabled = null) {

        //Daten vorbereiten
        $data = array(
            'sensors' => ($sensorIds !== null ? implode(',', $sensorIds) : null),
            'temperature'=> $temperature
        );

        //Datensatz bearbeiten
        return $this->editCondition($id, $name, $enabled, $data);
    }

    /**
     * loascht eine Bedingung
     * 
     * @param  Integer $id ID
     * @return Boolean
     * @throws \RWF\Xml\Exception\XmlException
     */
    public function removeCondition($id) {

        //XML Daten Laden
        $xml = XmlFileManager::getInstance()->getXmlObject(SHC::XML_CONDITIONS, true);

        //Bedingung suchen
        for ($i = 0; $i < count($xml->condition); $i++) {

            if ((int) $xml->condition[$i]->id == $id) {

                //Bedingung loeschen
                unset($xml->condition[$i]);

                //Daten Speichern
                $xml->save();
                return true;
            }
        }
        return false;
    }

    /**
     * geschuetzt wegen Singleton
     */
    private function __clone() {
        
    }

    /**
     * gibt den Bedingungs Editor zurueck
     * 
     * @return \SHC\Condition\ConditionEditor
     */
    public static function getInstance() {

        if (self::$instance === null) {

            self::$instance = new ConditionEditor();
        }
        return self::$instance;
    }

}
