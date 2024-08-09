<?php

/**
 * -------------------------------------------------------------------------
 * DataInjection plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of DataInjection.
 *
 * DataInjection is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * DataInjection is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DataInjection. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2007-2023 by DataInjection plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/datainjection
 * -------------------------------------------------------------------------
 */

class PluginDatainjectionModelcsv extends CommonDBChild
{

   static $rightname = "plugin_datainjection_model";
    var $specific_fields;

    // From CommonDBChild
   static public $itemtype  = 'PluginDatainjectionModel';
   static public $items_id  = 'models_id';
   public $dohistory        = true;


   function getEmpty() {

      $this->fields['delimiter']         = ';';
      $this->fields['is_header_present'] = 1;
   }

   function init() {

   }


    //---- Getters -----//

   function getDelimiter() {

      return $this->fields["delimiter"];
   }


   function isHeaderPresent() {

      return $this->fields["is_header_present"];
   }


    /**
    * If a Sample could be generated
   **/
   function haveSample() {

      return $this->fields["is_header_present"];
   }


    /**
    * Display Sample
    *
    * @param $model     PluginDatainjectionModel object
   **/
   function showSample(PluginDatainjectionModel $model) {

      $headers = PluginDatainjectionMapping::getMappingsSortedByRank($model->fields['id']);
      $sample = '"'.implode('"'.$this->getDelimiter().'"', $headers)."\"\n";

      header(
          'Content-disposition: attachment; filename="'.str_replace(
              ' ', '_',
              $model->getName()
          ).'.csv"'
      );
      header('Content-Type: text/comma-separated-values');
      header('Content-Transfer-Encoding: UTF-8');
      header('Content-Length: '.mb_strlen($sample, 'UTF-8'));
      header('Pragma: no-cache');
      header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
      header('Expires: 0');
      echo $sample;
   }


    /**
    * Check if filename ends with .csv
    *
    * @param $filename  the filename
    *
    * @return boolean true if name is correct, false is not
   **/
   function checkFileName($filename) {

      return ( !strstr(strtolower(substr($filename, strlen($filename)-4)), '.csv'));
   }


    /**
    * Get CSV's specific ID for a model
    * If row doesn't exists, it creates it
    *
    * @param $models_id the model ID
    *
    * @return the ID of the row in glpi_plugin_datainjection_modelcsv
   **/
   function getFromDBByModelID($models_id) {

      global $DB;

      $query = "SELECT `id`
                FROM `".$this->getTable()."`
                WHERE `models_id` = '".$models_id."'";

      $results = $DB->query($query);
      $id = 0;

      if ($DB->numrows($results) > 0) {
         $id = $DB->result($results, 0, 'id');
         $this->getFromDB($id);

      } else {
         $this->getEmpty();
         $tmp = $this->fields;
         $tmp['models_id'] = $models_id;
         $id  = $this->add($tmp);
         $this->getFromDB($id);
      }

      return $id;
   }


    /**
    * @param $model              PluginDatainjectionModel object
    * @param $options   array
   **/
   function showAdditionnalForm(PluginDatainjectionModel $model, $options = []) {
      $this->getFromDBByModelID($model->fields['id']);

      echo "<h2>".__('Specific file format options', 'datainjection')."</h2>";

      echo "<div>";
          echo "<label for='is_header_presend'>".__("Header's presence", 'datainjection')."</label>";
          Dropdown::showYesNo('is_header_present', $this->isHeaderPresent());
      echo "</div>";

      echo "<div class='mb-2'>";
          echo "<label for='delimiter'>".__('File delimitor', 'datainjection')."</label>";
          echo "<input type='text' class='form-control' size='1' name='delimiter' value='".$this->getDelimiter()."'";
      echo "</div>";
   }


    /**
    * @param $fields
   **/
   function saveFields($fields) {

      $csv                      = clone $this;
      $tmp['models_id']         = $fields['id'];
      $tmp['delimiter']         = $fields['delimiter'];
      $tmp['is_header_present'] = $fields['is_header_present'];
      $csv->getFromDBByModelID($fields['id']);
      $tmp['id']                = $csv->fields['id'];
      $csv->update($tmp);
   }

}
