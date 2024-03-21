<?php
/**
 * @package       WT Articles anywhere with fields
 * @version       2.0.1
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Content\Wtarticlewithfields\Extension;

defined('_JEXEC') or die('Restricted access');

use Exception;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;


final class Wtarticlewithfields extends CMSPlugin implements SubscriberInterface
{
	use DatabaseAwareTrait;

	/**
	 * If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	protected $allowLegacyListeners = false;

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
	 * @param   string   $context     The context of the content being passed to the plugin.
	 * @param   object & $article     The article object.  Note $article->text is also available
	 * @param   mixed &  $params      The article params
	 * @param   integer  $limitstart  The 'page' number
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */

	public function onContentPrepare(ContentPrepareEvent $event)
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

			$settings = explode(" ", $short_code);

			foreach ($settings as $param)
			{
				$param                        = explode("=", $param);
				$short_code_params[$param[0]] = $param[1];

			}
			if (!empty($short_code_params["article_id"]))
			{

				$html = '';

				$tmpl = (!empty($short_code_params["tmpl"]) ? $short_code_params["tmpl"] : 'default');

				try
				{
					$insert_article = $this->getArticle((int) $short_code_params["article_id"]);

					if (!empty($insert_article))
					{

						$insert_article->jcfields = FieldsHelper::getFields("com_content.article", $insert_article, true);
						$insert_article_sef_link  = Route::_("index.php?option=com_content&view=article&id=" . $insert_article->id . "&catid=" . $insert_article->catid);
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
				}
				catch (Exception $e)
				{

				}

				$article->text = str_replace($short_codes[0][$i], $html, $article->text);

			}
			else
			{
				return;
			}
			$i++;
		}
	}

	/**
	 *  Copy of \Joomla\Component\Content\Site\Model\ArticleModel
	 *  because native ArticleModel throws exception for unpublished articles.
	 *  We return false for this case.
	 *
	 * @param   int  $pk  article id
	 *
	 * @return bool|object
	 *
	 * @throws Exception
	 * @since 2.0.1
	 * @see   \Joomla\Component\Content\Site\Model\ArticleModel
	 */
	private function getArticle(int $pk): bool|object
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('a.id'),
				$db->quoteName('a.asset_id'),
				$db->quoteName('a.title'),
				$db->quoteName('a.alias'),
				$db->quoteName('a.introtext'),
				$db->quoteName('a.fulltext'),
				$db->quoteName('a.state'),
				$db->quoteName('a.catid'),
				$db->quoteName('a.created'),
				$db->quoteName('a.created_by'),
				$db->quoteName('a.created_by_alias'),
				$db->quoteName('a.modified'),
				$db->quoteName('a.modified_by'),
				$db->quoteName('a.checked_out'),
				$db->quoteName('a.checked_out_time'),
				$db->quoteName('a.publish_up'),
				$db->quoteName('a.publish_down'),
				$db->quoteName('a.images'),
				$db->quoteName('a.urls'),
				$db->quoteName('a.attribs'),
				$db->quoteName('a.version'),
				$db->quoteName('a.ordering'),
				$db->quoteName('a.metakey'),
				$db->quoteName('a.metadesc'),
				$db->quoteName('a.access'),
				$db->quoteName('a.hits'),
				$db->quoteName('a.metadata'),
				$db->quoteName('a.featured'),
				$db->quoteName('a.language'),
			]
		)
			->select(
				[
					$db->quoteName('fp.featured_up'),
					$db->quoteName('fp.featured_down'),
					$db->quoteName('c.title', 'category_title'),
					$db->quoteName('c.alias', 'category_alias'),
					$db->quoteName('c.access', 'category_access'),
					$db->quoteName('c.language', 'category_language'),
					$db->quoteName('fp.ordering'),
					$db->quoteName('u.name', 'author'),
					$db->quoteName('parent.title', 'parent_title'),
					$db->quoteName('parent.id', 'parent_id'),
					$db->quoteName('parent.path', 'parent_route'),
					$db->quoteName('parent.alias', 'parent_alias'),
					$db->quoteName('parent.language', 'parent_language'),
					'ROUND(' . $db->quoteName('v.rating_sum') . ' / ' . $db->quoteName('v.rating_count') . ', 1) AS '
					. $db->quoteName('rating'),
					$db->quoteName('v.rating_count', 'rating_count'),
				]
			)
			->from($db->quoteName('#__content', 'a'))
			->join(
				'INNER',
				$db->quoteName('#__categories', 'c'),
				$db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
			)
			->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'))
			->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
			->join('LEFT', $db->quoteName('#__categories', 'parent'), $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id'))
			->join('LEFT', $db->quoteName('#__content_rating', 'v'), $db->quoteName('a.id') . ' = ' . $db->quoteName('v.content_id'))
			->where(
				[
					$db->quoteName('a.id') . ' = :pk',
					$db->quoteName('c.published') . ' > 0',
				]
			)
			->bind(':pk', $pk, ParameterType::INTEGER);

		$user = $this->getApplication()->getIdentity();


		if (
			!$user->authorise('core.edit.state', 'com_content.article.' . $pk)
			&& !$user->authorise('core.edit', 'com_content.article.' . $pk)
		)
		{
			// Filter by start and end dates.
			$nowDate = Factory::getDate()->toSql();

			$query->extendWhere(
				'AND',
				[
					$db->quoteName('a.publish_up') . ' IS NULL',
					$db->quoteName('a.publish_up') . ' <= :publishUp',
				],
				'OR'
			)
				->extendWhere(
					'AND',
					[
						$db->quoteName('a.publish_down') . ' IS NULL',
						$db->quoteName('a.publish_down') . ' >= :publishDown',
					],
					'OR'
				)
				->bind([':publishUp', ':publishDown'], $nowDate);
		}

		$db->setQuery($query);

		$data = $db->loadObject();

		if (empty($data))
		{
			return false;
		}
		// Check for published state if filter set.
		if ($data->state != 1)
		{
			return false;
		}

		// Convert parameter fields to objects.
		$registry = new Registry($data->attribs);

		var_dump(Factory::getApplication()->getParams());


		$data->params = clone $this->getApplication()->getParams();
		$data->params->merge($registry);

		$data->metadata = new Registry($data->metadata);

		// Technically guest could edit an article, but lets not check that to improve performance a little.
		if (!$user->get('guest'))
		{
			$userId = $user->get('id');
			$asset  = 'com_content.article.' . $data->id;

			// Check general edit permission first.
			if ($user->authorise('core.edit', $asset))
			{
				$data->params->set('access-edit', true);
			}
			elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
			{
				// Now check if edit.own is available.
				// Check for a valid user and that they are the owner.
				if ($userId == $data->created_by)
				{
					$data->params->set('access-edit', true);
				}
			}
		}

		// Compute view access permissions.
		if ($access = $data->params->get('filter.access'))
		{
			// If the access filter has been set, we already know this user can view.
			$data->params->set('access-view', true);
		}
		else
		{
			// If no access filter is set, the layout takes some responsibility for display of limited information.
			$user   = $this->getApplication()->getIdentity();
			$groups = $user->getAuthorisedViewLevels();

			if ($data->catid == 0 || $data->category_access === null)
			{
				$data->params->set('access-view', \in_array($data->access, $groups));
			}
			else
			{
				$data->params->set('access-view', \in_array($data->access, $groups) && \in_array($data->category_access, $groups));
			}
		}

		return $data;
	}
}