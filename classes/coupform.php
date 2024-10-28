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

namespace auth_coupsign;

/**
 * Class form
 *
 * @package    auth_coupsign
 * @copyright  2024 Syed Dastagir <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coupform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $DB;
        $coupon = $this->generateCouponCode();
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('html', '<div class="container ">');
        $mform->addElement('html', '<div class="row  d-flex justify-content-center align-item-center">');
        $mform->addElement('html', '<div class="col-md-6 border shadow rounded pt-3  ">');
        $mform->addElement('html', '<h3 class=" text-center mb-5 text-success">Coupon Form</h3>');



        $id = $this->_customdata['id'];

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
       
        // Notes
        $mform->addElement('text', 'notes', get_string('notes','auth_coupsign'));
        $mform->setType('notes', PARAM_RAW);
        $mform->addHelpButton('notes', 'notes', 'auth_coupsign');
        // coupon code
        $mform->addElement('static', 'coupon', get_string('couponcode','auth_coupsign', 'coupon code'), $coupon);
        $mform->addElement('hidden', 'couponcode', $coupon);
        $mform->setType('couponcode', PARAM_RAW);
        // Company Name
        $companies = $DB->get_records_sql("SELECT id, name FROM {company}");

        $options = array();
        foreach ($companies as $company) {
            $options[$company->id] = $company->name;
        }
        
        $mform->addElement('select', 'company', get_string('company', 'auth_coupsign'), $options);
        
        // Add coupon code allow usage.
        $mform->addElement('text', 'allowusage', get_string('allowusage','auth_coupsign'));
        $mform->setType('allowusage', PARAM_NOTAGS);
        $mform->addHelpButton('allowusage', 'allowusage', 'auth_coupsign');

        // expiry Date
        $mform->addElement('date_selector', 'expiry_date', get_string('expirydate','auth_coupsign'));

        // When ready, add your action buttons.
        $this->add_action_buttons();
        
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
    }
    
    // function for couponcode generation
    private function generateCouponCode($length = 8) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
}
