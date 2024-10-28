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
 * TODO describe file signup_form
 *
 * @package    auth_coupsign
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 require_once($CFG->libdir.'/formslib.php');
 require_once($CFG->dirroot.'/user/profile/lib.php');
 require_once($CFG->dirroot . '/user/editlib.php');
 require_once($CFG->dirroot .'/login/lib.php');
//  $action = new moodle_url('/auth/coupsign/signup.php');
 class login_signup_form extends moodleform implements renderable, templatable {
     function definition() {
         global $USER, $CFG, $SESSION;
 
         // Iomad
         if (!empty($SESSION->company)) {
             $this->company = $SESSION->company;
         }
         $mform = $this->_form;
 
         // Iomad
         if ($CFG->local_iomad_signup_useemail) {
             $mform->addElement('html', get_string('emailasusernamehelp', 'local_iomad_signup'));
 
             $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
             $mform->setType('email', PARAM_RAW_TRIMMED);
             $mform->addRule('email', get_string('missingemail'), 'required', null, 'server');
 
             $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
             $mform->setType('email2', PARAM_RAW_TRIMMED);
             $mform->addRule('email2', get_string('missingemail'), 'required', null, 'server');
         } else {
             $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12" autocapitalize="none"');
             $mform->setType('username', PARAM_RAW);
             $mform->addRule('username', get_string('missingusername'), 'required', null, 'client');
         }
 
         if (!empty($CFG->passwordpolicy)){
             $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
         }
         $mform->addElement('password', 'password', get_string('password'), [
             'maxlength' => 32,
             'size' => 12,
             'autocomplete' => 'new-password'
         ]);
         $mform->setType('password', core_user::get_property_type('password'));
         $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');
 
       
 
         $namefields = useredit_get_required_name_fields();
         foreach ($namefields as $field) {
             $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
             $mform->setType($field, core_user::get_property_type('firstname'));
             $stringid = 'missing' . $field;
             if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                 $stringid = 'required';
             }
             $mform->addRule($field, get_string($stringid), 'required', null, 'client');
         }
         
        
       
         // Coupon Code
         $mform->addElement('text', 'couponcode', get_string('couponcode','auth_coupsign'));
         $mform->setType('couponcode', PARAM_NOTAGS);
         $mform->addRule('couponcode', 'Please enter your coupon code.', 'required', null, 'client');

         if (signup_captcha_enabled()) {
             $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
             $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
             $mform->closeHeaderBefore('recaptcha_element');
         }
 
         // Hook for plugins to extend form definition.
         core_login_extend_signup_form($mform);
 
         // Add "Agree to sitepolicy" controls. By default it is a link to the policy text and a checkbox but
         // it can be implemented differently in custom sitepolicy handlers.
         $manager = new \core_privacy\local\sitepolicy\manager();
         $manager->signup_form($mform);
 
         // Iomad
         if (!empty($this->company)) {
             $mform->addElement('hidden', 'companyid', $this->company->id);
             $mform->addElement('hidden', 'code', $this->company->shortname);
             $mform->addElement('hidden', 'departmentid', $this->company->deptid);
             $mform->setType('companyid', PARAM_INT);
             $mform->setType('departmentid', PARAM_INT);
             $mform->setType('code', PARAM_CLEAN);
         }
 
         // buttons
         $this->set_display_vertical();
         $this->add_action_buttons(true, get_string('createaccount'));
 
     }
 
     function definition_after_data(){
         $mform = $this->_form;
         $mform->applyFilter('username', 'trim');
 
         // Trim required name fields.
         foreach (useredit_get_required_name_fields() as $field) {
             $mform->applyFilter($field, 'trim');
         }
     }
 
     /**
      * Validate user supplied data on the signup form.
      *
      * @param array $data array of ("fieldname"=>value) of submitted data
      * @param array $files array of uploaded files "element_name"=>tmp_file_path
      * @return array of "element_name"=>"error_description" if there are errors,
      *         or an empty array if everything is OK (true allowed for backwards compatibility too).
      */
     public function validation($data, $files) {
         global $DB, $CFG;
 
         $errors = parent::validation($data, $files);
 
         // Extend validation for any form extensions from plugins.
         $errors = array_merge($errors, core_login_validate_extend_signup_form($data));
 
         if (signup_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                // if (!$recaptchaelement->verify($response)) {
                //     $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                // }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
         }

         // Validate Coupon $data['couponcode']
         // Get coupon record
         $coupon = $DB->get_record_sql ("SELECT * FROM {auth_coupon} WHERE code LIKE ?", array($data['couponcode']));

         // Coupon text is valid
         if (empty($coupon)) {
            $errors['couponcode'] = get_string('invalidcoupon', 'auth_coupsign');
         } else {
            // Coupon date is not expired
            if (time() > $coupon->expiry_date) {
                $errors['couponcode'] = get_string('couponexpired', 'auth_coupsign');
            }
            // Coupon usages are not expired
            $couponusages = $DB->count_records('auth_coupon_usages', array('couponid' => $coupon->id));
            if ($couponusages >= $coupon->usage_count) {
                $errors['couponcode'] = get_string('couponusagelimitreached', 'auth_coupsign');
            }

         }
 
         // IOMAD
         if ($CFG->local_iomad_signup_useemail) {
                 $data['username'] = strtolower($data['email']);
         }
 
         $errors += signup_validate_data($data, $files);
 
         return $errors;
     }
 
     /**
      * Export this data so it can be used as the context for a mustache template.
      *
      * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
      * @return array
      */
     public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
        'formhtml' => $formhtml
        ];
        return $context;
     }
 }
 