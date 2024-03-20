/**
 * @package     WT JShopping products anywhere
 * @copyright   Copyright (C) 2021-2023 Sergey Tolkachyov. All rights reserved.
 * @author      Sergey Tolkachyov - https://web-tolk.ru
 * @link 		https://web-tolk.ru
 * @version 	2.0.0
 * @license     GNU General Public License version 3 or later
 */
(() => {
    document.addEventListener('DOMContentLoaded', () => {
        // Get the elements
        const elements = document.querySelectorAll('[data-article-id]');

        for (let i = 0, l = elements.length; l > i; i += 1) {
            // Listen for click event
            elements[i].addEventListener('click', event => {
                event.preventDefault();
                const {
                    target
                } = event;

                const article_id = target.getAttribute('data-article-id');
                const tmpl = document.getElementById('wtarticlewithfieldseditorxtd_layout').value;

                if (!Joomla.getOptions('xtd-wtarticlewithfieldseditorxtd')) {
                    // Something went wrong!
                    // @TODO Close the modal
                    return false;
                }

                const {
                    editor
                } = Joomla.getOptions('xtd-wtarticlewithfieldseditorxtd');

                window.parent.Joomla.editors.instances[editor].replaceSelection("{wt_article_wf article_id=" + article_id + " tmpl=" + tmpl + "}");

                if (window.parent.Joomla.Modal) {
                    window.parent.Joomla.Modal.getCurrent().close();
                }
            });
        }
    });
})();
