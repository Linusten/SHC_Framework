<?php

namespace RWF\Form\FormElements;

//Imports
use RWF\Form\AbstractFormElement;
use RWF\Util\String;

/**
 * Eingabefeld fuer Ganzzahlen
 * 
 * Optionen:
 * Integer min  kleinste Zahl
 * Integer max  groeste Zahl
 * Integer step Schritte
 * 
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.0.0-0
 * @version    2.0.0-0
 */
class IntegerInputField extends AbstractFormElement {

    /**
     * erzeugt das HTML Element fuer die Web View
     * 
     * @return String
     */
    protected function fetchWebView() {

        //Zufaellige ID
        $randomId = String::randomStr(64);
        $this->addId('a' . $randomId);

        //Deaktiviert
        $disabled = '';
        if ($this->isDisabled()) {

            $disabled = ' disabled="disabled" ';
            $this->addClass('disabled');
        }

        //CSS Klassen
        $class = '';
        if (count($this->classes) > 0) {

            $class = ' ' . String::encodeHTML(implode(' ', $this->classes));
        }

        //CSS IDs
        $id = '';
        if (count($this->ids) > 0) {

            $id = ' id="' . String::encodeHTML(implode(' ', $this->ids)) . '" ';
        }

        //HTML Code
        $html = '<div class="rwf-ui-form-content">' . "\n";

        //Titel
        if ($this->getTitle() != '') {

            $html .= '<div class="rwf-ui-form-content-title">' . String::encodeHTML($this->getTitle()) . ($this->isRequiredField() ? ' <span class="rwf-ui-form-content-required">*</span>' : '') . "</div>\n";
        }

        //Formularfeld
        $html .= '<div class="rwf-ui-form-content-element">';
        $html .= '<input type="text" name="' . String::encodeHTML($this->getName()) . '" class="rwf-ui-form-content-integerinputfield' . $class . '" value="' . String::encodeHTML($this->getValue()) . '" ' . $id . $disabled . ' />';
        $html .= "</div>\n";

        //Beschreibung
        if ($this->getDescription() != '') {

            $html .= '<div class="rwf-ui-form-content-description">' . String::encodeHTML($this->getDescription()) . '</div>';
        }

        $html .= "</div>\n";

        //JavaScript ueberpruefung
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "
            \$(function() {
                \$('#a" . $randomId . "').spinner({
                    min: " . (isset($this->options['min']) ? $this->options['min'] : 0) . ",
                    max: " . (isset($this->options['max']) ? $this->options['max'] : 100) . ",
                    step: " . (isset($this->options['step']) ? $this->options['step'] : 1) . ",
                });
            });\n";
        $html .= "</script>\n";

        return $html;
    }

    /**
     * erzeugt das HTML Element fuer die Mobile View
     * 
     * @return String
     */
    protected function fetchMobileView() {

        return 'not implemented';
    }

    /**
     * prueft die Eingabedaten auf gueltigkeit
     * 
     * @return Boolean
     */
    public function validate() {

        $valid = true;
        $value = $this->getValue();

        //Pflichtfeld
        if ($this->isRequiredField() && $value == '') {

            $this->messages[] = 'Das Feld ' . String::encodeHTML($this->getTitle()) . ' muss ausgefüllt werden';
            $valid = false;
        }

        //Minimalwert
        if ((isset($this->options['min']) && $value < $this->options['min']) || (!isset($this->options['min']) && $value < 0)) {

            $this->messages[] = 'Das Feld ' . String::encodeHTML($this->getTitle()) . ' muss mindestens einen Wert von ' . String::numberFormat($this->options['min']) . ' haben';
            $valid = false;
        }

        //Maximalwert
        if ((isset($this->options['max']) && $value > $this->options['max']) || (!isset($this->options['max']) && $value > 100)) {

            $this->messages[] = 'Das Feld ' . String::encodeHTML($this->getTitle()) . ' darf maximal einen Wert von ' . String::numberFormat($this->options['max']) . ' haben';
            $valid = false;
        }

        //Schritte
        if ((isset($this->options['step']) && $value % $this->options['step'] > 0) || (!isset($this->options['step']) && $value % 1 > 0)) {

            $this->messages[] = 'Das Feld ' . String::encodeHTML($this->getTitle()) . ' darf nur Wert in ' . String::numberFormat($this->options['step']) . 'er Schritten haben';
            $valid = false;
        }

        if ($valid === false) {

            $this->addClass('rwf-ui-form-content-invalid');
        }
        return $valid;
    }

}