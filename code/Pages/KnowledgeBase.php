<?php

/**
 * Root page for knowledge base section
 * @author Damo
 */
class KnowledgeBase extends KnowledgeBasePage
{
    public static $create_knowledgebase_pages = true;
    
    static $allowed_children = array(
        'KnowledgeBaseArticle',
        'KnowledgeBaseCategory'
    );
    
    static $singular_name = 'KB Section';

    static $has_many = array('Articles' => 'KnowledgeBaseArticle');

    /**
     * Add default records to database.
     *
     * This function is called whenever the database is built, after the
     * database tables have all been created. Overload this to add default
     * records when the database is built, but make sure you call
     * parent::requireDefaultRecords().
     */
    function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        // Ignore inherited pages
        if ($this->class != get_class() || !self::$create_knowledgebase_pages)
            return;

        if (DB::query("SELECT COUNT(*) FROM `SiteTree` WHERE `SiteTree`.`ClassName` = '" . get_class() . "'")->value() > 0)
            return;

        $kbSection = new KnowledgeBase();
        $kbSection->Title = _t('KnowledgeBase.DEFAULT_TITLE', 'Knowledge Base');
        $kbSection->Content = _t('KnowledgeBase.DEFAULT_CONTENT', '<p>Welcome to our Knowledge Base</p>');
        $kbSection->Status = 'Published';
        $kbSection->Sort = 8;
        $kbSection->write();
        $kbSection->publish('Stage', 'Live');
        $kbSection->flushCache();
        DB::alteration_message('Knowledge Base created', 'created');

        $kbCategory = new KnowledgeBaseCategory();
        $kbCategory->Title = _t('KnowledgeBase.DEFAULT_CATEGORY_TITLE', 'General');
        $kbCategory->Content = _t('KnowledgeBase.DEFAULT_CATEGORY_CONTENT', '<p>General articles on this knowledge base are below</p>');
        $kbCategory->Description = _t('KnowledgeBase.DEFAULT_CATEGORY_DESCRIPTION', 'General articles');
        $kbCategory->Status = 'Published';
        $kbCategory->Sort = 1;
        $kbCategory->ParentID = $kbSection->ID;
        $kbCategory->write();
        $kbCategory->publish('Stage', 'Live');
        $kbCategory->flushCache();
        DB::alteration_message('Knowledge Base Category created', 'created');

        $kbArticle = new KnowledgeBaseArticle();
        $kbArticle->Title = _t('KnowledgeBase.DEFAULT_ARTICLE_TITLE', 'How to use the Knowledge Base module');
        $kbArticle->Content = _t('KnowledgeBase.DEFAULT_ARTICLE_CONTENT', '<p>Create your articles here!</p>');
        $kbArticle->Status = 'Published';
        $kbArticle->Sort = 1;
        $kbArticle->ParentID = $kbCategory->ID;
        $kbArticle->write();
        $kbArticle->publish('Stage', 'Live');
        $kbArticle->flushCache();
        DB::alteration_message('Knowledge Base Article created', 'created');
    }

}

class KnowledgeBase_Controller extends KnowledgeBasePage_Controller
{
}