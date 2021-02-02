<?php
defined('_JEXEC') or die('Restricted access');
/*
*   Copyright (C) 2019  Sergey Tolkachyov
*   Released under GNU GPL Public License
*   License: http://www.gnu.org/copyleft/gpl.html
*   https://web-tolk.ru
* 	Layout file for WT Articles with fields content plugin
*/
use Joomla\CMS\Language\Text;

// For full article object info uncomment this echos
//  echo "<pre>";
//  print_r($insert_article);
//  echo "</pre>";
  
// Show only article fields for constructing your own layout
//  echo "<pre>";
//  print_r($insert_article->jcfields);
//  echo "</pre>";

/* $insert_article->id                      article id
 * $insert_article->title                   article title
 * $insert_article->alias                   article alias
 * $insert_article->introtext               introtext
 * $insert_article->fulltext                fulltext
 * $insert_article->state                   publushed or not
 * $insert_article->catid                   article category id
 * $insert_article->created                 article date created
 * $insert_article->created_by              article created by user id
 * $insert_article->created_by_alias        article created by user alias
 * $insert_article->modified                article date modified
 * $insert_article->publish_up              article publish date start
 * $insert_article->publish_down            article publish date end
 * $insert_article->images                  [JSON] article images (image_intro, image_fulltext)
 * $insert_article->urls                    [JSON] article urls (urla, urlb)
 * $insert_article->attribs                 [JSON] article attributes (layout, show title etc)
 * $insert_article->version                 article version
 * $insert_article->ordering                article ordering
 * $insert_article->metakey                 article meta keywords
 * $insert_article->metadesc                article meta description
 * $insert_article->hits                    article hits number
 * $insert_article->featured                is article featured? [true/false]
 * $insert_article->language                article language
 * $insert_article->category_title          article category title
 * $insert_article->category_alias          article category alias
 * $insert_article->category_access         article category access level
 * $insert_article->author                  article author name
 * $insert_article->parent_title            article category parent title
 * $insert_article->parent_id               article category parent category id
 * $insert_article->rating                  article rating
 * $insert_article->rating_count            how many people rating this article
 * $insert_article->params                  [stdClass Object] Menu item params for inserted article
 *
 * **** Custom fields ****
 * $insert_article->jcfields                [Array][stdClass Object] Array of objects of article custom fields
 *                    You can access to fields via
 *
 *                    $insert_article->jcfields[0]->title                    field title    - [0] - field order number fron fields list in administrator panel
 *                    $insert_article->jcfields[0]->value                    field value
 *                    $insert_article->jcfields[0]->rawvalue                 field rawvalue     JSON for repeatable fields
 *                    $insert_article->jcfields[0]->fieldparams->options->options0 (1,2,3,4 etc)     stdClass Object
 *                    $insert_article->jcfields[0]->fieldparams->options->options0->name     field name
 *                    $insert_article->jcfields[0]->fieldparams->options->options0->value     field rawvalue
 *
 */

//$images = (array)json_decode($insert_article->jcfields[1]->rawvalue);

?>

<h4><?php echo $insert_article->title; ?></h4>
<br/>
<?php echo $insert_article->introtext; ?>
<br/>
<a href="<?php echo $insert_article_sef_link;?>"><?php echo Text::_('JDETAILS'); ?></a>
