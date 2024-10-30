<?php
/* 
Plugin Name: C4F Textarea Toolbar
Plugin URI: http://code4fun.org/c4f-textarea-toolbar
Version: 1.0
Author: Code4Fun Team
Author URI: http://code4fun.org
Description: C4F Textarea Toolbar adds a toolbar for markup and emoticons insertion to WordPress comments' textarea. Go to <strong><a href="../wp-admin/options-general.php?page=c4f-textarea-toolbar.php" >C4F Textarea Toolbar Options' Panel</a></strong> to customize it.
*/

/*
    Copyright (C) 2008  code4fun.org

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(dirname(__FILE__).'/php/C4F_ME_Option.php');

/**
 * We use a class structure to avoid naming collisions with other plugins but 
 * also because we love OOP!
 */
if (!class_exists("C4F_TextareaToolbar")) {
	class C4F_TextareaToolbar {

		var $PLUGIN_DIR;
		var $PLUGIN_URL;
		var $IMAGES_DIR;
		var $IMAGES_URL;
		var $CSS_URL;
		var $JS_URL;
		var $WP_SMILIES_URL;
				
		var $optionName			= "C4F_TextareaToolbarAdmin";
		var $deafultOptions		= array();
		
		var $ALL				= "all";
		var $ONLY_CONTROLS		= "only_controls";
		var $ONLY_SMILIES		= "only_smilies";
		
		var $BUTTONS_CONTROLS	= "";
		
		
		/**
		 * Default constructor
		 *
		 */
		function C4F_TextareaToolbar() { 
			
			// Pre-2.6 compatibility
			if ( !defined('WP_CONTENT_URL') ) 
				define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
			if ( !defined('WP_CONTENT_DIR') ) 
				define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
			if ( ! defined( 'WP_PLUGIN_URL' ) ) 
      			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
			if ( ! defined( 'WP_PLUGIN_DIR' ) )
      			define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
			
			$this->PLUGIN_DIR 		= WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__));
			$this->PLUGIN_URL 		= WP_PLUGIN_URL.'/'.plugin_basename(dirname(__FILE__));			
			$this->IMAGES_DIR		= $this->PLUGIN_DIR.'/imgs';	
			$this->IMAGES_URL		= $this->PLUGIN_URL.'/imgs';	
			$this->CSS_URL			= $this->PLUGIN_URL.'/css';
			$this->JS_URL			= $this->PLUGIN_URL.'/js';	
			$this->WP_SMILIES_URL	= get_option('siteurl').'/wp-includes/images/smilies';
			
			$this->deafultOptions['showhat'] 	= new C4F_ME_Option(array($this->ALL, $this->ONLY_CONTROLS, $this->ONLY_SMILIES));
			$this->deafultOptions['credit']  	= new C4F_ME_Option(array(TRUE, FALSE));																  	   
			$this->deafultOptions['controls']  	= new C4F_ME_Option($this->getImgSet($this->IMAGES_DIR));
		}
		
		/**
		 * Gets options.
		 * Attempts to find previous options that may have been stored in the 
		 * database, if options have been previously stored, it overwrites the 
		 * default values. 
		 */
		function getOptions() {

			$default 		= $this->deafultOptions;
         	$savedOptions 	= get_option($this->optionName);
         	if (!empty($savedOptions)) {
            	foreach ($savedOptions as $key => $option)
               		$default[$key] = $option;
        	}
        	return $default;
     	}
     	
     	/**
     	 * Plugin initialization.
     	 *
     	 */
		function init() {
			// creates a new option, does nothing if option already exists
			add_option($this->optionName, $this->deafultOptions);
		}	
		
		/**
		 * Removes options from database.
		 *
		 */
		function cleanup() {
			delete_option($this->optionName);
		}
		
		/**
		 * Resets options to default.
		 *
		 */
		function reset() {
			update_option($this->optionName, $this->deafultOptions);
		}
		
		function getImgSet($path) {
			$result = array($this->BUTTONS_CONTROLS);
			if ($handle = @opendir($path)) {
    			while (false !== ($file = readdir($handle))) {
	        		if ($file != "." && $file != "..") {
	        			$result[] = $file;
	        		}
    			}
    			closedir($handle);
			}
			return $result;
		}
		
		/**
		 * Includes external resources (css, javascript, etc...).
		 *
		 */
		function headInsertion() {
			// CSS
			echo '<link type="text/css" rel="stylesheet" href="' . $this->CSS_URL .'/c4ftt-style.css" />' . "\n";
			
			// Javascript
			if (function_exists('wp_enqueue_script') 
					&& function_exists('wp_register_script')) {
						
				wp_register_script('c4f-editor', $this->JS_URL . '/editor.js');
				wp_enqueue_script('c4f-editor');
				
				wp_register_script('c4f-html', $this->JS_URL . '/html.js');
				wp_enqueue_script('c4f-html');
				
				wp_register_script('c4f-smilies', $this->JS_URL . '/smilies.js');
				wp_enqueue_script('c4f-smilies');
			}
		}
		
		/**
		 * Includes admin external resources (css, javascript, etc...). 
		 *
		 */
		function adminHeadInsertion() {
			// CSS
			echo '<link type="text/css" rel="stylesheet" href="' . $this->CSS_URL .'/admin-style.css" />' . "\n";
			// Javascript
			if (function_exists('wp_enqueue_script') 
				&& function_exists('wp_register_script')) {
				wp_register_script('c4f-administration', $this->JS_URL . '/admin/admin.js');
				wp_enqueue_script('c4f-administration');														
			}
		}
		
		/**
		 * Prints controls and/or smilies according to the options.
		 *
		 * @param array $params - 
		 */
		function printToolbar($params = NULL) {
			
            $options = $this->getOptions();

	        // Override current option using function argument
            if (!is_null($params)) {
            	if (isset($params['showhat'])) {
            		$options['showhat']->setSelected(strtolower($params['showhat']));
            	}
            	
            	if (isset($params['credit']) && is_bool($params['credit'])) {
            		$options['credit']->setSelected($params['credit']);	
            	} 
            	
				if (isset($params['controls'])) {
            		$options['controls']->setSelected($params['controls']);
            	}            	
			}					
	            
			// Prints out according to options
			$this->printNoscript();
			if ($options['showhat']->getSelected()==$this->ALL 
					|| $options['showhat']->getSelected()==$this->ONLY_CONTROLS) {
				$this->printControls($options['controls']->getSelected());
			}
			if ($options['showhat']->getSelected()==$this->ALL 
					|| $options['showhat']->getSelected()==$this->ONLY_SMILIES) {
						
				$this->printSmilies();		
			}		
			if ($options['credit']->getSelected()) {
				$this->printCredit();
			}	
		}
		
		/**
		 * Prints the noscript element used to define an alternate content 
		 * (text) if a script is NOT executed.
		 *
		 */
		function printNoscript() {
			?>			
			<noscript>
			<div class="c4f-warning" >
				<h3>WARNING</h3>
				<p>Your browser does not support JavaScript or has JavaScript disabled!</p>
				<p>This will not compromise the possibility to leave a comment, although the automatic insertion of both markup tags and emoticons will not work.</p>
			</div>
			</noscript>
            <?php			
		}
		
		/**
		 * Prints buttons controls.
		 *
		 */
		function printControls($folder) {
			?>
            <fieldset id="c4ftt-controls" >
                <legend>Markup Controls</legend>
            <?php
			if($folder==$this->BUTTONS_CONTROLS || !file_exists($this->IMAGES_DIR.'/'.$folder)) {
				$this->printButtonControls();
			} else {			
				$this->printImageControls($this->IMAGES_URL.'/'.$folder);
			}
            ?>
            </fieldset>
            <?php
		}
		
		function printButtonControls() {
			?>
				<input type="button" value="strong" alt="Bold" onclick="c4f.insertTag('strong','comment');" />
				<input type="button" value="em" alt="Italic" onclick="c4f.insertTag('em','comment');" />
<?php /*
				<input type="button" value="strike" alt="Strike" onclick="c4f.insertTag('strike','comment');" />
				<input type="button" value="b" alt="Bold" onclick="c4f.insertTag('b','comment');" />
				<input type="button" value="i" alt="Italic" onclick="c4f.insertTag('i','comment');" />
*/ ?>
				<input type="button" value="code" alt="Code" onclick="c4f.insertTag('code','comment');" />
				<input type="button" value="blockquote" alt="Blockquote" onclick="c4f.insertTag('blockquote','comment');" />
				<input type="button" value="abbr" alt="Abbr" onclick="c4f.insertTag('abbr','comment');" />
				<input type="button" value="acronym" alt="Acronym" onclick="c4f.insertTag('acronym','comment');" />
				<input type="button" value="link" alt="Insert Link" onclick="c4f.insertTag('url','comment');" />
            <?php		
		}
		
		function printImageControls($folderName) {
			?>
				<img src="<?=$folderName?>/strong.png" alt="Bold" onclick="c4f.insertTag('strong','comment');" />
				<img src="<?=$folderName?>/em.png" alt="Italic" onclick="c4f.insertTag('em','comment');" />
<?php /*
				<img src="<?=$folderName?>/code.png" alt="Strike" onclick="c4f.insertTag('strike','comment');" />
				<img src="<?=$folderName?>/code.png" alt="Bold" onclick="c4f.insertTag('b','comment');" />
				<img src="<?=$folderName?>/code.png" alt="Italic" onclick="c4f.insertTag('i','comment');" />
*/ ?>
				<img src="<?=$folderName?>/code.png" alt="Code" onclick="c4f.insertTag('code','comment');" />
				<img src="<?=$folderName?>/blockquote.png" alt="Blockquote" onclick="c4f.insertTag('blockquote','comment');" />
				<img src="<?=$folderName?>/acronym.png" alt="Abbr" onclick="c4f.insertTag('abbr','comment');" />
				<img src="<?=$folderName?>/acronym.png" alt="Acronym" onclick="c4f.insertTag('acronym','comment');" />
				<img src="<?=$folderName?>/link.png" alt="Insert Link" onclick="c4f.insertTag('url','comment');" />
            <?php			
		}
		
		/**
		 * Prints smilies.
		 *
		 */
		function printSmilies() {
            ?>
            <fieldset id="c4ftt-emoticons" >
                <legend>Emoticons</legend>
                <img title="Smile" alt="Smile" src="<?=$this->WP_SMILIES_URL?>/icon_smile.gif" onclick="c4f.insertTag('smile','comment');" />
                <img title="Grin" alt="Grin" src="<?=$this->WP_SMILIES_URL?>/icon_biggrin.gif" onclick="c4f.insertTag('grin','comment');" />
                <img title="Sad" alt="Sad" src="<?=$this->WP_SMILIES_URL?>/icon_sad.gif" onclick="c4f.insertTag('sad','comment');" />
                <img title="Surprised" alt="Surprised" src="<?=$this->WP_SMILIES_URL?>/icon_surprised.gif" onclick="c4f.insertTag('surprised','comment');" />
                <img title="Shocked" alt="Shocked" src="<?=$this->WP_SMILIES_URL?>/icon_eek.gif" onclick="c4f.insertTag('shock','comment');" />
                <img title="Confused" alt="Confused" src="<?=$this->WP_SMILIES_URL?>/icon_confused.gif" onclick="c4f.insertTag('confused','comment');" />
                <img title="Cool" alt="Cool" src="<?=$this->WP_SMILIES_URL?>/icon_cool.gif" onclick="c4f.insertTag('cool','comment');" />
                <img title="Mad" alt="Mad" src="<?=$this->WP_SMILIES_URL?>/icon_mad.gif" onclick="c4f.insertTag('mad','comment');" />
                <img title="Razz" alt="Razz" src="<?=$this->WP_SMILIES_URL?>/icon_razz.gif" onclick="c4f.insertTag('razz','comment');" />
                <img title="Neutral" alt="Neutral" src="<?=$this->WP_SMILIES_URL?>/icon_neutral.gif" onclick="c4f.insertTag('neutral','comment');" />
                <img title="Wink" alt="Wink" src="<?=$this->WP_SMILIES_URL?>/icon_wink.gif" onclick="c4f.insertTag('wink','comment');" />
                <img title="Lol" alt="Lol" src="<?=$this->WP_SMILIES_URL?>/icon_lol.gif" onclick="c4f.insertTag('lol','comment');" />
                <img title="Red Face" alt="Red Face" src="<?=$this->WP_SMILIES_URL?>/icon_redface.gif" onclick="c4f.insertTag('oops','comment');" />
                <img title="Cry" alt="Cry" src="<?=$this->WP_SMILIES_URL?>/icon_cry.gif" onclick="c4f.insertTag('cry','comment');" />
                <img title="Evil" alt="Evil" src="<?=$this->WP_SMILIES_URL?>/icon_evil.gif" onclick="c4f.insertTag('evil','comment');" />
                <img title="Twisted" alt="Twisted" src="<?=$this->WP_SMILIES_URL?>/icon_twisted.gif" onclick="c4f.insertTag('twisted','comment');" />
                <img title="Roll" alt="Roll" src="<?=$this->WP_SMILIES_URL?>/icon_rolleyes.gif" onclick="c4f.insertTag('roll','comment');" />
                <img title="Exclaim" alt="Exclaim" src="<?=$this->WP_SMILIES_URL?>/icon_exclaim.gif" onclick="c4f.insertTag('exclaim','comment');" />
                <img title="Question" alt="Question" src="<?=$this->WP_SMILIES_URL?>/icon_question.gif" onclick="c4f.insertTag('question','comment');" />
                <img title="Idea" alt="Idea" src="<?=$this->WP_SMILIES_URL?>/icon_idea.gif" onclick="c4f.insertTag('idea','comment');" />
                <img title="Arrow" alt="Arrow" src="<?=$this->WP_SMILIES_URL?>/icon_arrow.gif" onclick="c4f.insertTag('arrow','comment');" />
                <img title="Mr Green" alt="Mr Green" src="<?=$this->WP_SMILIES_URL?>/icon_mrgreen.gif" onclick="c4f.insertTag('mrgreen','comment');" />
            </fieldset>
			<?php			
		}
		
		/**
		 * Prints credit.
		 *
		 */
		function printCredit() {
			?>
			<p id="c4ftt-credit" ><small>powered by <a href="http://code4fun.org/c4f-textarea-toolbar" title="C4F Textarea Toolbar - Code4Fun.org" >C4F Textarea Toolbar</a></small></p>			
			<?php
		}
		
		/**
		 * Prints the admin page.
		 * First checks for user submitted values and acts with regard to these,
		 * than prints the up to date page.
		 */
		function printOptionsPage() {
			
			$options = $this->getOptions();
			
			$selectedControl = $options['controls']->getSelected(); 
			$options['controls'] = new C4F_ME_Option($this->getImgSet($this->IMAGES_DIR));
			$options['controls']->setSelected($selectedControl);
			
			if (isset($_POST['C4F_TextareaToolbarSaveSettings'])) {
				switch ($_POST['action']) {
					case "clean":
						$this->cleanup();
						$userMessage = "Cleanup executed.";
					break;
					
					case "reset":
						$this->reset();
						$userMessage = "Default options loaded.";						
					break;
					
					default:
						if (isset($_POST['show'])) {
							$options['showhat']->setSelected($_POST['show']);
						}
						
						if (isset($_POST['controls'])) {
							$options['controls']->setSelected($_POST['controls']);
						}
						
						$options['credit']->setSelected(isset($_POST['credit']));
												
						update_option($this->optionName, $options);
						$userMessage = "Settings Updated.";
						
					break;
				}
				$options = $this->getOptions();					
			} else {
				// form has not been submitted
			}
	
			if (isset($userMessage)) {
				?>
				<div id="message" class="updated fade" >
					<p><strong><?php _e($userMessage, "C4F_TextareaToolbar");?></strong></p>
				</div>
				<?php
			}
			?>
			<div class="c4fAdmin wrap" >
				<h2>C4F Textarea Toolbar - Options</h2>
				<h3>Controls</h3>
				<p>Choose if you want to display <strong>markup controls</strong>, <strong>smilies controls</strong> or if you want <strong>both</strong> to show up when C4F Textarea Toolbar is activated.</p>
				<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" >
				<fieldset>
					<legend>Show Controls</legend>
					<input type="radio" id="show_both" name="show" value="<?php print $this->ALL ?>" <?php print ($options['showhat']->getSelected()==$this->ALL)?"checked=\"checked\"":'' ?> />
					<label for="show_both">Both <em>(default)</em></label><br />
					<input type="radio" id="show_controls" name="show" value="<?php print $this->ONLY_CONTROLS ?>" <?php print ($options['showhat']->getSelected()==$this->ONLY_CONTROLS)?"checked=\"checked\"":'' ?> />
					<label for="show_controls">Markup controls only</label><br />
					<input type="radio" id="show_smilies" name="show" value="<?php print $this->ONLY_SMILIES ?>" <?php print ($options['showhat']->getSelected()==$this->ONLY_SMILIES)?"checked=\"checked\"":'' ?> />
					<label for="show_smilies">Smilies controls only</label>
				</fieldset>
				<fieldset>
					<select id="controls" name="controls" onchange="c4f_admin.change(<?="'".$this->IMAGES_URL."'"?>,this);">
					<?php foreach ($options['controls']->getItems() as $item) :	?>	
						<option value="<?=$item?>" <?=($options['controls']->isSelected($item))?"selected=\"selected\"":''?>><?=($item==$this->BUTTONS_CONTROLS)?"standard (no icons)":$item?></option>
					<?php endforeach; ?>
					</select>
					<div id="buttons" style="display: none;">
						<input type="button" value="strong" alt="Bold" />
						<input type="button" value="em" alt="Italic" />
						<input type="button" value="code" alt="Code" />
						<input type="button" value="blockquote" alt="Blockquote" />
						<input type="button" value="abbr" alt="Abbr" />
						<input type="button" value="acronym" alt="Acronym" />
						<input type="button" value="link" alt="Insert Link" />					
					</div>
					<div id="icons" style="display: none;">
						<img src="" alt="Bold"/>
						<img src="" alt="Italic"/>
						<img src="" alt="Code"/>
						<img src="" alt="Blockquote"/>
						<img src="" alt="Acronym"/>
						<img src="" alt="Insert Link"/>
					</div>		
					<script type="text/javascript">c4f_admin.loadOnStartup(<?="'".$this->IMAGES_URL."'"?>, 'controls');</script>			
				</fieldset>
				<h3>Credit</h3>
				<p>Choose if you want to display the credit line below the toolbar(s).</p>
				<fieldset>
					<legend>Show Credit</legend>
					<input type="checkbox" name="credit" <?php print ($options['credit']->getSelected())?"checked=\"checked\"":'' ?> />
				</fieldset>
				<p>Please note that <strong>you can customize</strong> the credit line to fit your template's design by either modifying the <code>#c4ftt-credit</code> <code>id</code> inside the <tt>c4ftt-style.css</tt> file that is in the plugin's package or by adding the <acronym title="Cascading Style Sheet">CSS</acronym> code (always using the <code>#c4ftt-credit</code> <code>id</code>) inside your own stylesheet.<br />Of course you don't have to display the credit to use the plugin, but <strong>we will be very happy</strong> if you decide to do it.</p>
				<fieldset class="submit">
					<select name="action" >
  						<option value ="update"><?php _e('Update Settings', 'C4F_TextareaToolbar') ?></option>
  						<option value ="reset"><?php _e('Reset to default', 'C4F_TextareaToolbar') ?></option>
  						<option value ="clean"><?php _e('Cleanup database', 'C4F_TextareaToolbar') ?></option>
					</select>
					<input type="submit" name="C4F_TextareaToolbarSaveSettings" class="button-primary" value="<?php _e('Submit', 'C4F_TextareaToolbar') ?>" />
				</fieldset>
				</form>
				<p class="c4fsign" >C4F Textarea Toolbar is a plugin from <a href="http://code4fun.org" title="Code4Fun Homepage" >Code4Fun</a>. Please, consider making a <a href="http://code4fun.org/donate.php" title="Contribute to Code4Fun Project!" >donation</a> if you like it.</p>
 			</div>
			<?php
		}//End function printOptionsPage()		
	
	} //End Class C4F_TextareaToolbar

} //End (!class_exists) 

if (class_exists("C4F_TextareaToolbar")) {
	$c4f_txbar = new C4F_TextareaToolbar();
}

if (!function_exists("C4F_TextareaToolbar_AdminPanel")) {
	function C4F_TextareaToolbar_AdminPanel() {
		global $c4f_txbar;
		if (!isset($c4f_txbar)) {
			return;
		}
		if (function_exists('add_options_page')) {
			$plugin_page = add_options_page('C4F Textarea Toolbar', 'C4F Textarea Toolbar', 9, 
							 basename(__FILE__), array(&$c4f_txbar, 'printOptionsPage'));
			
			// includes css/javascript file into plugin administration page
			// add_action ("admin_head-$plugin_page", array(&$c4f_txbar, 'adminHeadInsertion'), 50);
			add_action( "admin_print_scripts-$plugin_page", array(&$c4f_txbar, 'adminHeadInsertion'), 50);
		}
	}	
}

/**
 * This is the function you can include in the template
 */ 
if (!function_exists("C4F_TextareaToolbar")) {
	function C4F_TextareaToolbar($params = NULL) {
		global $c4f_txbar;
		$c4f_txbar->printToolbar($params);
		remove_action('comment_form', array(&$c4f_txbar, 'printToolbar'), 1);						
	}
}

/**
 * Actions and filters
 */	
if (isset($c4f_txbar)) {
	
	// plugin activation
	register_activation_hook(__FILE__, array(&$c4f_txbar, 'init')); 
	// plugin deactivation
	// register_deactivation_hook(__FILE__, array(&$c4f_txbar, 'cleanup')); 
	// hook for adding admin menus
	add_action('admin_menu', 'C4F_TextareaToolbar_AdminPanel');
	// triggered within the <head></head> section of the user's template	
	add_action('wp_head', array(&$c4f_txbar, 'headInsertion'), 1);	
	// echo-only action that is fired within an entry's comment form
	add_action('comment_form', array(&$c4f_txbar, 'printToolbar'), 1);
}

?>