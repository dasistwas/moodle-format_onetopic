<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade scripts for course format "onetopic"
 *
 * @package   format_onetopic
 * @copyright 2018 David Herney Bernal - cirano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for format_onetopic
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_format_onetopic_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/course/format/onetopic/db/upgradelib.php');

    if ($oldversion < 2018010601) {

        // Remove 'numsections' option and hide or delete orphaned sections.
        format_onetopic_upgrade_remove_numsections();

        upgrade_plugin_savepoint(true, 2018010601, 'format', 'onetopic');
    }

    if ($oldversion < 2018010607) {
        $newjs = "require(['jquery'], function ($) {
    var sumwl = 0;
    var sumle = 0;
    $('.workload').each(function () {
        if ($.isNumeric(parseFloat($(this).text()))) {
            sumwl += parseFloat($(this).text());  // Or this.innerHTML, this.innerText
        }
    });
    $('.lerneinheiten').each(function () {
        if ($.isNumeric(parseFloat($(this).text()))) {
            sumle += parseFloat($(this).text());  // Or this.innerHTML, this.innerText
        }
    });
    $('.totalworkload').text(sumwl);
    $('.totallerneinheiten').text(sumle);

    $('.platzhalter').text('Summe');
    $('.textbeforewl').text('WL');
    $('.textbeforele').text('LE');

    $('.gesamtsumme').each(function () {
        if ($('div[class*=\"datalynxview\"] .alert-error').length) {
            $(this).addClass(\"hidden\");
        }
    });

    $('.enddate').each(function () {
        var enddate = $(this).text();
        var daypattern = /(\d+)(\..)([a-zA-Zäüöß]+)(.+?)(\d+)/;
        daypattern.exec(enddate);
        var months = {
            'Jänner': '00',
            'Januar': '00',
            'Februar': '01',
            'März': '02',
            'Mai': '04',
            'Juni': '05',
            'Juli': '06',
            'August': '07',
            'September': '08',
            'Oktober': '09',
            'November': '10',
            'Dezember': '11',
            'January': '00',
            'February': '01',
            'March': '02',
            'April': '03',
            'May': '04',
            'June': '05',
            'July': '06',
            'October': '09',
            'December': '11'
        };
        var day = RegExp.$1;
        var month = months[RegExp.$3];
        var year = RegExp.$5;
        //alert('Tag' +day+'monat'+month+'jahr'+year+'duration'+duration+'daystoadd'+daystoadd);
        var dateformat = \"%d. %B %Y\";
        var jsdate = new Date(Date.UTC(year, month, day));
        var currentdate = new Date();

        if (currentdate.getTime() > jsdate.getTime()) {
            $(this).closest('.show-grid').fadeTo(\"fast\", 0.5, function () {
                // Animation complete.
            });
        }
    });

    $(\".show-grid.Abschluss\").each(function (index) {
        var abschlusshref = $(this).find('.editabschluss a').attr('href');
        $(this).find('.editlink a').attr('href', abschlusshref);
    });

    $('.fhb_phase select').on('change', function () {
        if (this.value == 4) {
            var abschlusslink = $('.abschlusslink a').attr('href');
            var form = $(this).closest('form');
            form[0].reset();
            window.onbeforeunload = null;
            window.location.replace(abschlusslink);
        }
    });

    $('.datalynxview-Abschluss .abschlusstermintext input').attr('value', 'Abschluss');
    $('.datalynxview-Abschluss .fhb_phase select').val(4);
    $('.Abschluss .startdate').prepend('<span>1. Abschlusstermin: </span>');
    $('.Abschluss .enddate').prepend('<span>2. Abschlusstermin: </span>');

});";
        $like = $DB->sql_like('js', ':likestring');
        $params = ['likestring' => '%datalynxview-Abschluss%', 'newjs' => $newjs];
        $sql = "UPDATE {datalynx} SET js = :newjs WHERE {$like}";
        $DB->execute($sql, $params);

        // Upgrade teammemberselect field.
        $teamsql = "UPDATE {datalynx_fields} SET param1 = 10 WHERE  param1 = 3 AND name = 'Lehrende' AND type = 'teammemberselect'";
        $DB->execute($teamsql);

        upgrade_plugin_savepoint(true, 2018010607, 'format', 'onetopic');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
