<?php

namespace Sminnee\ModelComments;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\GridField AS GF;
use Symbiote\GridFieldExtensions AS GFE;
use Silverstripe\Forms AS F;

class HasModelCommentsExtension extends DataExtension
{
    private static $has_many = [
        'ModelComments' => ModelComment::class,
    ];

    public function updateCMSFields($fields)
    {
        // If a new record without relationships, we don't need this
        if (!$fields->fieldByName('Root')->fieldByName('ModelComments')) {
            return;
        }

        $fields->fieldByName('Root')->fieldByName('ModelComments')->setTitle('Comments');

        $fields->dataFieldByName('ModelComments')->setConfig((new GF\GridFieldConfig)
            ->addComponent(new GF\GridFieldButtonRow('after'))
            ->addComponent(new GFE\GridFieldTitleHeader())
            ->addComponent((new GFE\GridFieldEditableColumns())
                ->setDisplayFields([
                    'Comment' => ['title' => 'Comment', 'callback' => function ($record, $column, $grid) {
                        if ($record->ID) {
                            return new F\ReadonlyField($column);
                        } else {
                            return new F\TextareaField($column);
                        }
                    }],

                    'Author.Name' => ['title' => 'Author', 'field' => F\ReadonlyField::class],
                    'CreatedString' => ['title' => 'Date', 'field' => F\ReadonlyField::class],
                ])
            )
            // TO DO: amend this so that new records can be prepended rather than appended, and move to top of list
            ->addComponent((new GFE\GridFieldAddNewInlineButton('buttons-after-left'))->setTitle('Post comment (press Save after commenting)'))
            // TO DO: add pagination once the comments are list most-recent-first
            //->addComponent(new GF\GridFieldPaginator(30))
        );
    }
}
