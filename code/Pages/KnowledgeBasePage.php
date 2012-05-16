<?php

/**
 * Base class from which all knowledgebase pages are extended
 */
class KnowledgeBasePage extends Page
{
    public static $default_article_order = '`SiteTree`.`MenuTitle` ASC';

    public static $default_category_order = '`SiteTree`.`MenuTitle` ASC';

    static $db = array(
        /**
         * List of ancestor page IDs (not including self).
         * Denormalised node position used to assist in filtering by tree
         * section (category). For instance, a TreePosition of 1.3.4 would
         * mean this node exists in KB id 1 with two levels of sub-categories, 
         * (3 and 4). This means we could do a filter to search for all articles
         * In category 3, whether they are a child of category 3 directly or 4
         * with the below;
         * "WHERE TreePosition LIKE '1.3.%' OR TreePosition = '1.3'"
         * (This prevents matches against 1.30.%).
         * This speeds up filtering by preventing excessive SQL queries
         */
        'TreePosition' => 'Varchar(255)'
    );

    function canCreate($member = null)
    {
        // Alternative to making this an abstract class, which crashes silverstripe
        return parent::canCreate($member) &&
                get_class() != get_class($this);
    }

    /**
     * Extracts ID of the current knowledge base
     * @return null 
     */
    function getKnowledgeBaseID()
    {
        if ($this instanceof KnowledgeBase)
            return $this->ID;

        return (($index = strpos($this->TreePosition, '.')) !== FALSE)
                ? substr($this->TreePosition, 0, $index)
                : $this->TreePosition;
    }

    /**
     * Determines the current knowledgebase for this page
     */
    function getKnowledgeBase()
    {
        if ($this instanceof KnowledgeBase)
            return $this;

        $kbID = $this->getKnowledgeBaseID();
        if (empty($kbID))
            return null;

        return DataObject::get_by_id('KnowledgeBase', $kbID);
    }

    /**
     * Retrieves all categories that are a direct descendant
     * @return DataObjectSet
     */
    public function ChildCategories()
    {
        return DataObject::get(
                        'KnowledgeBaseCategory', "ParentID = {$this->ID}", self::$default_category_order
        );
    }

    /**
     * Retrieves all descendant categories
     * @return DataObjectSet
     */
    public function SubCategories()
    {
        $prefix = $this->getChildTreeFilter();
        return DataObject::get(
                        "KnowledgeBaseCategory", "(
                    `KnowledgeBasePage`.`TreePosition` LIKE '$prefix.%'
                    OR `KnowledgeBasePage`.`TreePosition` = '$prefix'
                )", self::$default_category_order
        );
    }

    /**
     * Retrieves all articles that are a direct descendant of this
     * @return DataObjectSet
     */
    public function ChildArticles()
    {
        return DataObject::get(
                        'KnowledgeBaseArticle', "ParentID = {$this->ID}", self::$default_article_order
        );
    }

    /**
     * Retrieves all descendant articles
     * @return DataObjectSet
     */
    public function SubArticles()
    {
        $prefix = $this->getChildTreeFilter();
        return DataObject::get(
                        "KnowledgeBaseArticle", "(
                    `KnowledgeBasePage`.`TreePosition` LIKE '$prefix.%'
                    OR `KnowledgeBasePage`.`TreePosition` = '$prefix'
                )", self::$default_article_order
        );
    }

    /**
     * Determines the treeposition that children of this node should use
     * for quick filtering by ancestry
     * @return string TreePosition for child nodes
     */
    public function getChildTreeFilter()
    {
        $prefix = $this->TreePosition;
        if (!empty($prefix))
            $prefix .= '.';
        return $prefix . $this->ID;
    }

    /**
     * Recursively updates the tree position for child pages after a save 
     */
    protected function updateChildTreePositions($doPublish = false)
    {
        $prefix = $this->getChildTreeFilter();

        foreach ($this->Children() as $child)
        {
            if (!($child instanceof KnowledgeBasePage))
                continue;

            $child->TreePosition = $prefix;
            if ($doPublish)
                $child->doPublish();
            else
                $child->write();
        }
    }

    /**
     * Updates the TreePosition field for this node
     */
    protected function updateTreePosition()
    {
        $parent = $this->Parent();

        if ($parent instanceof KnowledgeBasePage)
            $this->TreePosition = $parent->ChildTreeFilter;
        else // Handle case for root element
            $this->TreePosition = '';
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->updateTreePosition();
    }

    function onAfterWrite()
    {
        parent::onAfterWrite();

        // Update TreePosition for all children
        $this->updateChildTreePositions(false);
    }

    function onAfterPublish()
    {
        // Publish tree positions for all children
        $this->updateChildTreePositions(true);
    }

}

/**
 * Class for all page controller functions shared within the knowledge base
 */
class KnowledgeBasePage_Controller extends Page_Controller
{
    static $allowed_actions = array('ArticleQuickSearchForm', 'findarticles');

    protected function ArticleQuickSearchForm()
    {
        $fields = new FieldSet(
                        new TextField('SearchText', 'Search', null, 512)
        );

        $actions = new FieldSet(
                        new FormAction('findarticles', 'Search')
        );

        $form = new CustomSearchForm($this, 'ArticleQuickSearchForm', $fields, $actions);

        // Set ajax search queries
        $form->setSearchField(array('SearchText', 'term'));

        // Filter articles by individual knowledge base
        $kbID = $this->data()->getKnowledgeBaseID();
        if ($kbID)
            $form->setExtraFilter("ClassName = 'KnowledgeBaseArticle' AND ID IN 
                (
                    SELECT `ID`
                    FROM `KnowledgeBasePage` 
                    WHERE `KnowledgeBasePage`.`TreePosition` LIKE '$kbID.%' 
                    OR `KnowledgeBasePage`.`TreePosition` = '$kbID'
                )");
        else
            $form->setExtraFilter("ClassName = 'KnowledgeBaseArticle'");
        return $form;
    }

    public function findarticles($data, $form)
    {
        $results = $form->getResults();
        if (Director::is_ajax())
        {
            $output = array();
            if ($results)
                foreach ($results as $result)
                {
                    $rating = ($result->RatingEnabled && $result->RatingCount)
                            ? sprintf('<div class="StaticRating Rating_%1$s" title="Rated %1$s/10">%1$s/10</div>', intval($result->Rating))
                            : '';
                    $output[] = array(
                        'value' => $result->ID,
                        'label' => $result->MenuTitle,
                        'description' => $result->CategoryText,
                        'link' => $result->Link(),
                        'rating' => $rating
                    );
                }
            return json_encode($output);
        }
        $data = array(
            'Results' => $form->getResults(),
            'Query' => $form->getSearchQuery(),
            'Title' => 'Search Results'
        );

        return $this->customise($data)->renderWith(array('KnowledgeBasePage_results', 'Page_results', 'Page'));
    }

    /**
     * Retrieves the list of top level categories
     * @return type 
     */
    public function BaseCategories()
    {
        $kbID = $this->data()->getKnowledgeBaseID();
        return DataObject::get('KnowledgeBaseCategory', "ParentID = $kbID");
    }

    /**
     * Retrieves the list of featured articles
     * @param integer $limit Limit to number of articles to receive
     * @return DataObjectSet list of {@see KnowledgeBaseArticle}
     */
    public function FeaturedArticles($limit = 5)
    {
        return $this->LatestArticles($limit, "Featured = 1");
    }

    /**
     * Retrieves the latest articles in the current knowledge base
     * @param integer $limit Limit to number of articles to receive
     * @return DataObjectSet list of {@see KnowledgeBaseArticle}
     */
    public function LatestArticles($limit = 5, $extraFilter = null)
    {
        $kbID = $this->data()->getKnowledgeBaseID();
        $condition = "(
                    `KnowledgeBasePage`.`TreePosition` LIKE '$kbID.%'
                    OR `KnowledgeBasePage`.`TreePosition` = '$kbID'
                )";
        if ($extraFilter)
            $condition .= "AND ($extraFilter)";
        return DataObject::get('KnowledgeBaseArticle', $condition, 'Created DESC', null, $limit);
    }

    /**
     * Retrieves the top rated articles in the knowledge base
     * @param integer $limit Limit to number of articles to receive
     * @return DataObjectSet list of {@see KnowledgeBaseArticle}
     */
    public function BestArticles($limit = 5)
    {
        $kbID = $this->data()->getKnowledgeBaseID();
        $condition =
                "(
                    `KnowledgeBasePage`.`TreePosition` LIKE '$kbID.%'
                    OR `KnowledgeBasePage`.`TreePosition` = '$kbID'
                )";
        $join = " LEFT JOIN (
                SELECT ArticleID, AVG(`Rating`) AS Rating 
                FROM `KnowledgeBaseArticleRating`
                GROUP BY ArticleID
            ) `Ratings`
            ON `SiteTree`.`ID` = `Ratings`.`ArticleID`";
        return DataObject::get('KnowledgeBaseArticle', $condition, '`Ratings`.`Rating` DESC', $join, $limit);
    }

    function init()
    {
        parent::init();

        // Jquery UI and themes
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery-packed.js');
        Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/minified/jquery.ui.core.min.js');
        Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/minified/jquery.ui.widget.min.js');
        Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/minified/jquery.ui.position.min.js');
        Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/minified/jquery.ui.autocomplete.min.js');
        Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery.ui.core.css');
        Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery.ui.autocomplete.css');
        Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery.ui.theme.css');

        Requirements::javascript(KNOWLEDGEBASE_MODULE_DIR . '/javascript/kb.search.js');
    }

}