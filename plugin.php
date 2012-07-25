<?php
/*
Plugin Name: MHub
Plugin URI: http://mhub.co.za
Description: MHub mobi site redirector for WordPress by MHub. Ignoring the potential of the mobile web is something today's business owner simply cannot afford.
Version: 1.0.1
Date: 2012, July 16th
Author: Shaun Trennery
Author URI: http://twitter.com/shauntrennery
*/
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define("MHUB_PLUGINFILE", WP_PLUGIN_DIR."/mhub/lib/mdetect.php");

if (!class_exists("MHubPlugin")) {
  class MHubPlugin {

    function MHubPlugin() {
      $this->addActions();
      register_activation_hook(__FILE__, array($this, "activatePlugin"));
      register_deactivation_hook(__FILE__, array($this, "deactivatePlugin"));
    }

    function addActions() {
      add_action("admin_init", array(&$this, "adminInit"));
      add_action("init", array(&$this, "init"));
      add_action("admin_menu", array(&$this, "mhub_wp_add_menu"));
    }

    function activatePlugin() {
      add_option("mhub_do_activation_redirect", true);
    }

    function deactivatePlugin() {

    }

    function adminInit() {
      if(get_option("mhub_do_activation_redirect", false)) {
        delete_option("mhub_do_activation_redirect");
        wp_redirect("options-general.php?page=mhub/plugin.php");
      }
    }

    function init() {
      if(file_exists(MHUB_PLUGINFILE)) {
        require_once(MHUB_PLUGINFILE);

        if(!function_exists("wp_redirect")) {
          require(ABSPATH . WPINC . "/pluggable.php");
        }

        $dontRedirect = false;
        if(!is_bool(strpos($_SERVER["REQUEST_URI"], "no_redirect=true"))) $dontRedirect = true;
        $targetUrl = "http://mhub.co.za/".get_option("mhub_wp_target_url");

        if(($targetUrl != "") && ($this->isValidURL($targetUrl)) && (!$dontRedirect)) {
          $uagent_obj = new uagent_info();
          $detect_mobile = $uagent_obj->DetectMobileLong();

          if (($detect_mobile == 1)) {
            wp_redirect($targetUrl."?redirected=true");
            exit();
          }
        }
      }
    }

    function isValidURL($url) {
      return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

    function mhub_wp_add_menu() {
      add_options_page("MHub", "Mhub", 8, __FILE__, array($this, "admin_options_wp_mhub"));
    }

    function admin_options_wp_mhub() {
      echo '
        <div class="wrap">
          <h2>MHub Settings</h2>
          <p>This WordPress plugin will detect mobile devices and redirect your vistors to your MHub mobi site. For this plugin to work, please enter your MHub mobi slug below.</p>
          <p>Need a mobi? Head over to <a href="http://mhub.co.za" target="_blank">MHub</a> and get your mobi on.</p>
          <form method="post" action="options.php">
            '.wp_nonce_field('update-options').'
            <table class="form-table">
              <tr valign="top">
                <th scope="row"><label for="mhub_wp_target_url">MHub mobi slug</label></th>
                <td><input type="text" name="mhub_wp_target_url" id="mhub_wp_target_url" value="'.get_option('mhub_wp_target_url').'" class="regular-text code" /></td>
              </tr>
            </table>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="mhub_wp_target_url" />
            <p class="submit"><input type="submit" name="Submit" value="Submit" class="button-primary" /></p>
          </form>
        </div>';
    }
  }
}

$MHubPlugin = new MhubPlugin();
?>