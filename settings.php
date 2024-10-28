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
 * Admin settings and defaults.
 *
 * @package auth_coupsign
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_coupsign/pluginname', '',
        new lang_string('auth_coupsigndescription', 'auth_coupsign')));

    $options = array(
        new lang_string('no'),
        new lang_string('yes'),
    );

    $settings->add(new admin_setting_configselect('auth_coupsign/recaptcha',
    new lang_string('auth_coupsignrecaptcha_key', 'auth_coupsign'),
    new lang_string('auth_coupsignrecaptcha', 'auth_coupsign'), 0, $options));


    if (is_null($ADMIN->locate('general'))) { 

        $ADMIN->add( 'root', new admin_category( 'general', get_string('general', 'auth_coupsign'))); 
        
    } 
    if (is_null($ADMIN->locate('IomadReports'))) { 

         $ADMIN->add( 'general', new admin_category( 'IomadReports', 
        
         get_string('iomadreports', 'block_iomad_company_admin'))); 
        
        } 
    

 

    // External Admin Page  
    
    // $ADMIN->add( 
    
    //     'coupmanage', 
        
    //     new admin_externalpage( 
        
    //         'adminpageexternal', 
            
    //         new lang_string('settings_general', 'auth_coupsign_coupmanage'), 
            
    //         new moodle_url( '/auth/coupsign/coupmanage.php') 
        
    //     ) 
    
    // ); 
    // // Settings -> Moodle Admin Page 

    // $adminpagemoodle->add( 

    //     new admin_setting_configtext( 
            
    //         'coupmanage_perpage_coupmanage', 
            
    //         get_string('settings_perpage_coupmanage', 'auth_coupsign_coupmanage'), 
            
    //         get_string('settings_perpage_coupmanage_help', 'auth_coupsign_coupmanage'), 
            
    //         8, 
            
    //         PARAM_INT 
            
    //     ) 
    
    // );  
     // Add the link to the administration block
    // $ADMIN->add('root', new admin_category('custom_category', get_string('custom_category', 'auth_coupsign')));

    // // Add a link within the custom category
    // $ADMIN->add('custom_category', new admin_externalpage('general', get_string('coupmanage', 'auth_coupsign'), 
    // new moodle_url('/auth/coupsign/coupmanage.php')));

    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin('coupsign');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
    get_string('auth_fieldlocks_help', 'auth'), false, false);
}
