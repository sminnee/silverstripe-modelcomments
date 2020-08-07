<?php

namespace Sminnee\ModelComments;

use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Core\Convert;

class ModelComment extends DataObject implements PermissionProvider
{

    private static $table_name = 'ModelComment';

    private static $db = [
        'Comment' => 'Text',
    ];

    private static $has_one = [
        'Object' => DataObject::class,
        'Author' => Member::class,
    ];

    private static $summary_fields = [
        'CreatedString' => 'Date',
        'Author.Name' => 'Author',
        'ObjectType' => 'Object type',
        'ObjectLabel' => 'Object',
        'Comment' => 'Comment',
    ];

    private static $searchable_fields = [
        'Comment',
        'AuthorID' => [
            'title' => 'Author',
            'filter' => 'ExactMatchFilter',
        ],
    ];

    /**
     * Get the friendly name of the linked object class
     */
    public function getObjectType(): ?string
    {
        if (class_exists($this->ObjectClass)) {
            return singleton($this->ObjectClass)->singular_name();
        }
        return null;
    }

    /**
     * Create a label for the object being commented on
     * It will use Label if available, otherwise Title
     * It will link it if the CMSEditLink method exists
     */
    public function getObjectLabel():? DBHTMLText
    {
        $obj = $this->Object();

        if(!$obj) {
            return null;
        }

        $text = $obj->Label;
        if (!$text) {
            $text = $obj->Title;
        }

        $html = Convert::raw2xml($text);

        if ($obj->hasMethod('CMSEditLink')) {
            $link = $obj->CMSEditLink();
            $html = sprintf('<a href="%s">%s</a>', Convert::raw2att($link), $html);
        }

        return DBField::create_field(DBHTMLText::class, $html);

    }

    public function onBeforeWrite()
    {
        if (!$this->AuthorID || !Permission::check('MODELCOMMENT_ADMIN')) {
            $this->AuthorID = Member::currentUserID();
        }
        parent::onBeforeWrite();
    }

    public function onAfterWrite()
    {
        // Accidentally opening a comment field and leaving it blank is common. This prevents junk records from appearing
        // It also allows for the workflow of remove-comment-text-to-delete it, which occurs in e.g. Slack
        if (!trim($this->Comment)) {
            $this->delete();
        }
    }

    public function getCreatedString(): string
    {
        $c = $this->obj('Created');

        if ($c->Format('Y-MM-dd') === date('Y-m-d')) {
            $absolute = $c->Time12();
        } else {
            $absolute = $c->Nice();
        }

        return $c->Ago() . ' (' . $absolute . ')';
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Provide permission related to managing this model.
     * @return array
     */
    public function providePermissions()
    {
        $category = 'Comments (Admin)';
        return [
            "MODELCOMMENT" => array(
                'name' => _t(ModelComment::class.'.MODELCOMMENT', "Can comment on objects"),
                'category' => $category
            ),
            "MODELCOMMENT_VIEW" => array(
                'name' => _t(ModelComment::class.'.MODELCOMMENT_VIEW', "Can view comments on objects"),
                'category' => $category
            ),
            "MODELCOMMENT_ADMIN" => array(
                'name' => _t(ModelComment::class.'.MODELCOMMENT_ADMIN', "Can administer comments on objects (delete/edit others)"),
                'category' => $category
            ),
        ];
    }

    public function canEdit($member = null)
    {
        if (Permission::check('MODELCOMMENT_ADMIN', 'any', $member)) {
            return true;
        } else if ($this->AuthorID && $this->AuthorID == Member::currentUserID()) {
            return Permission::check('MODELCOMMENT', 'any', $member);
        }
        return false;
    }

    public function canCreate($member = null, $context = [])
    {
        return
            Permission::check('MODELCOMMENT', 'any', $member);
    }

    public function canDelete($member = null)
    {
        return $this->canEdit($member);
    }

    public function canView($member = null)
    {
        return
            Permission::check(['MODELCOMMENT', 'MODELCOMMENT_VIEW', 'MODELCOMMENT_ADMIN'], 'any', $member);
    }
}
