<?php

class KnowledgeBaseCategory extends KnowledgeBasePage
{
    static $singular_name = 'KB Category';

    static $db = array(
        'Description' => 'Varchar(255)'
    );
    
    static $allowed_children = array(
        'KnowledgeBaseArticle',
        'KnowledgeBaseCategory'
    );

    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Content.Main', new TextField('Description', 'Category Description', null, 255), 'Content');
        return $fields;
    }
}

class KnowledgeBaseCategory_Controller extends KnowledgeBasePage_Controller
{
}
