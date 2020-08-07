<?php

namespace Sminnee\ModelComments;

use SilverStripe\Admin\ModelAdmin;

/**
 * Simple ModelAdmin to manage audit records
 */
class ModelCommentAdmin extends ModelAdmin {

    private static $managed_models = [
        ModelComment::class,
    ];

    private static $menu_title = 'Team Comments';

    private static $url_segment = 'modelcomments';

    private static $menu_icon_class = 'font-icon-comment';

}
