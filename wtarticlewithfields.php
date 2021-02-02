<?php

/*
*   Copyright (C) 2019  Sergey Tolkachyov
*   Released under GNU GPL Public License
*   License: http://www.gnu.org/copyleft/gpl.html
*   https://web-tolk.ru
*/

defined('_JEXEC') or die('Restricted access');
use \Joomla\CMS\Factory;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;

class plgContentWtarticlewithfields extends JPlugin
{

	static $article = null;

  public function __construct(&$subject, $params)
  {
    parent::__construct($subject, $params);
    // Load the language file
	  $this->loadLanguage();
  }

  public function onContentPrepare($context, $article, $params, $limitstart = 0)
	{
		if ($context == 'com_finder.indexer')
			{
				return true;
			}

		  //Проверка есть ли строка замены в контенте
			if(strpos($article->text, 'wt_article_wf') === false)
			{
			  return;
			}
		
			$regex = '/{wt_article_wf\s(.*?)}/i';
			preg_match_all($regex, $article->text, $settings_string);

		for($i = 0; $i <= count($settings_string); $i++){
					$params = array('article_id' => '',
					                'tmpl' => 'default',
								);

					$output_replace_pattern = $settings_string["0"][$i];
					$settings = $settings_string["1"][$i];
					$settings1 = explode(" ",$settings);
						foreach($settings1 as $param){
							$param = explode("=", $param);
							if (isset($params[$param[0]])) {
								$params[$param[0]] = $param[1];
							} else {
								$params[$param[0]] = "";
							}
						}

				if (!empty($params["article_id"]))
				{
					$article_id = $params["article_id"];
					$tmpl = $params["tmpl"];

					BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_content/models/', 'ContentModel');
					$model = BaseDatabaseModel::getInstance('Article', 'ContentModel');
					$insert_article = $model->getItem($article_id);
					JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
					$insert_article->jcfields = FieldsHelper::getFields("com_content.article",$insert_article, true);

					/*
					 * Radical Multifield support
					 * onCustomFieldsPrepareField
					 */
//					PluginHelper::importPlugin('radicalmultifield');
//					$radicalmultifieldPlugins = PluginHelper::getPlugin('radicalmultifield');
//					$radicalmultifieldPlugins->onCustomFieldsPrepareField($context, $item, $field);

					$insert_article_sef_link = SEFLink("index.php?option=com_content&view=article&id=".$insert_article->id."&catid=".$insert_article->catid);
					ob_start();
					if(file_exists(JPATH_SITE . '/plugins/content/wtarticlewithfields/tmpl/'. $tmpl.'.php')){
						require JPATH_SITE . '/plugins/content/wtarticlewithfields/tmpl/' . $tmpl.'.php';
					} else {
						require JPATH_SITE . '/plugins/content/wtarticlewithfields/tmpl/default.php';
					}

					$html = ob_get_clean();

					$article->text = str_replace($output_replace_pattern, $html, $article->text);
					unset($params);
				} else {
					return;
				}
		}//end FOR

	} //onContentPrepare END

}