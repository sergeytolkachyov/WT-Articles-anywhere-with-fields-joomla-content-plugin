<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Uri\Uri;

class Pkg_pkgwtarticlewithfieldsInstallerScript extends \Joomla\CMS\Installer\InstallerScript
{
	
		
    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $installer - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $installer)
    {

		if ((new Version())->isCompatible('4.3.0') === false){
			Factory::getApplication()->enqueueMessage('<strong>WT Articles anywhere with fields plugin:</strong> This package version works only under Joomla <strong>4.3.+</strong>. For Joomla 3 please <a href=\"https://web-tolk.ru/dev/joomla-plugins/wt-articles-anywhere-with-fields?from='.(Uri::getInstance())->getHost().'\" target=\"_blank\">download version 1.0.1.</a>','error');
			return false;
		}
		
		
    }
	
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $installer - Parent object calling this method.
     *
     * @return void
     */
    public function install($installer)
    {
		
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $installer - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($installer) 
    {

		
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $installer - Parent object calling object.
     *
     * @return void
     */
    public function update($installer) 
    {
		
		
    }

	


    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $installer - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $installer)
    {
		if($type == 'install' || $type == 'update')
		{
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$object = new stdClass();
			$object->element = 'wtarticlewithfields'; 
			$object->type = 'plugin';
			$object->folder = 'content';
			$object->enabled = 1;
			
			$result = $db->updateObject('#__extensions', $object, 'element');
			
			$object2 = new stdClass();
			$object2->element = 'wtarticlewithfieldseditorxtd';
			$object2->type = 'plugin';
			$object2->folder = 'editors-xtd';
			$object2->enabled = 1;
			
			$result = $db->updateObject('#__extensions', $object2, 'element');
		}
		
	    $smile = '';
	    if($type != 'uninstall')
	    {
		    $smiles    = ['&#9786;', '&#128512;', '&#128521;', '&#128525;', '&#128526;', '&#128522;', '&#128591;'];
		    $smile_key = array_rand($smiles, 1);
		    $smile     = $smiles[$smile_key];
	    } else {
			$smile = ':(';
		}

	    $element = strtoupper($installer->getElement());
		echo "
		<div class='row bg-white m-3 p-3 shadow-sm border'>
		<div class='col-12 col-lg-8'>
		<h2>".$smile." ".Text::_($element."_AFTER_".strtoupper($type))." <br/>".Text::_($element)."</h2>
		".Text::_($element."_DESC");
		
		
			echo Text::_($element."_WHATS_NEW");

		echo "</div>
		<div class='col-12 col-lg-4 d-flex flex-column justify-content-start'>
		<img width='200px' src='https://web-tolk.ru/web_tolk_logo_wide.png'>
		<p>Joomla Extensions</p>
		<p class='btn-group'>
			<a class='btn btn-sm btn-outline-primary' href='https://web-tolk.ru' target='_blank'>https://web-tolk.ru</a>
			<a class='btn btn-sm btn-outline-primary' href='mailto:info@web-tolk.ru'><i class='icon-envelope'></i> info@web-tolk.ru</a>
		</p>
		<p><a class='btn btn-info' href='https://t.me/joomlaru' target='_blank'>Joomla Russian Community in Telegram</a></p>
		
		".Text::_($element."_MAYBE_INTERESTING")."
		</div>


		";		
	
    }
}