<?php
/**
 * @package    WT Articles anywhere with fields
 * @version       2.0.3
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Content\Wtarticlewithfields\Extension;

use Exception;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Database\DatabaseAwareTrait;

use function property_exists;
use function strpos;
use function preg_match_all;
use function defined;
use function ob_start;
use function file_exists;
use function ob_get_clean;
use function explode;
use function str_replace;

defined('_JEXEC') or die('Restricted access');

final class Wtarticlewithfields extends CMSPlugin implements SubscriberInterface
{
	use DatabaseAwareTrait;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepare' => 'onContentPrepare',
		];
	}

	/**
	 * Plugin that change short code to article data with specified layout
	 *
	 * @param   ContentPrepareEvent  $event
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */

	public function onContentPrepare(ContentPrepareEvent $event): void
	{

		// Don't run if in the API Application
		// Don't run this plugin when the content is being indexed
		if (!$this->getApplication()->isClient('site') || $event->getContext() === 'com_finder.indexer')
		{
			return;
		}

		// Get content item
		$article = $event->getItem();

		// If the item does not have a text property there is nothing to do
		if (!property_exists($article, 'text'))
		{
			return;
		}

		//Проверка есть ли строка замены в контенте
		if (strpos($article->text, 'wt_article_wf') === false)
		{
			return;
		}

		$regex = '/{wt_article_wf\s(.*?)}/i';
		preg_match_all($regex, $article->text, $short_codes);

		$i                 = 0;
		$short_code_params = [];

		foreach ($short_codes[1] as $short_code)
		{

			$settings = explode(' ', $short_code);

			foreach ($settings as $param)
			{
				$param                        = explode('=', $param);
				$short_code_params[$param[0]] = $param[1];

			}
			if (!empty($short_code_params["article_id"]))
			{

				$html = '';

				$tmpl = $short_code_params['tmpl'] ?? 'default';

				$insert_article = $this->getArticle((int) $short_code_params["article_id"]);

				if ($insert_article)
				{
					$insert_article->jcfields = FieldsHelper::getFields('com_content.article', $insert_article, true);
					$insert_article_sef_link  = Route::_('index.php?option=com_content&view=article&id=' . $insert_article->id . '&catid=' . $insert_article->catid);
					ob_start();
					if (file_exists(JPATH_SITE . '/plugins/content/wtarticlewithfields/tmpl/' . $tmpl . '.php'))
					{
						require JPATH_SITE . '/plugins/content/wtarticlewithfields/tmpl/' . $tmpl . '.php';
					}
					else
					{
						require JPATH_SITE . '/plugins/content/wtarticlewithfields/tmpl/default.php';
					}

					$html = ob_get_clean();

				}

				$article->text = str_replace($short_codes[0][$i], $html, $article->text);
				if (property_exists($article, 'introtext') && !empty($article->introtext))
				{
					$article->introtext = str_replace($short_codes[0][$i], $html, $article->introtext);
				}
				if (property_exists($article, 'fulltext') && !empty($article->fulltext))
				{
					$article->fulltext = str_replace($short_codes[0][$i], $html, $article->fulltext);
				}

			}

			$i++;
		}
	}

	/**
	 *  Wrapper for of \Joomla\Component\Content\Site\Model\ArticleModel
	 *  because native ArticleModel throws exception for unpublished articles.
	 *  We return false for this case.
	 *
	 * @param   int  $pk  article id
	 *
	 * @return bool|object
	 *
	 * @throws Exception
	 * @since 2.0.1
	 */
	private function getArticle(int $pk): bool|object
	{
		if (empty($pk))
		{
			return false;
		}

		$model = $this->getApplication()
			->bootComponent('com_content')
			->getMVCFactory()
			->createModel('Article', 'Site', ['ignore_request' => false]);

		try
		{
			return $model->getItem($pk);
		}
		catch (\Exception $e)
		{

			Log::add('WT Article anywhere with fields: ' . ($e->getMessage()) . '. Article id ' . $pk, Log::ERROR);

			return false;
		}
	}
}
