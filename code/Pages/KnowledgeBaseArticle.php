<?php

/**
 * @author Damo
 */
class KnowledgeBaseArticle extends KnowledgeBasePage
{
    static $singular_name = 'KB Article';
    
    static $db = array(
        'Featured' => 'Boolean'
    );

    static $has_many = array(
        'Ratings' => 'KnowledgeBaseArticleRating'
    );
    
    /**
     * Determines the visual descriptor of all categories this article lies within
     * @return string list of categories
     */
    public function getCategoryText()
    {
        $category = $this->Parent();
        $items = array();
        while($category && $category->exists())
        {
            $items[] = $category->MenuTitle;
            $category = $category->Parent();
        }
        $items = array_reverse($items);
        return join(' > ', $items);
    }

    public static $rating_enabled = true;

    /**
     * Determines if the current article can be rated
     * @return boolean Flag indicating if ratings are enabled
     */
    public function getRatingEnabled()
    {
        return self::$rating_enabled;
    }
    
    /**
     * Determines the number of previous ratings
     * @return integer The number of ratings
     */
    public function getRatingCount()
    {
        return intval(DB::query('SELECT COUNT(`Rating`) as Count FROM `KnowledgeBaseArticleRating` WHERE `ArticleID` = ' . $this->ID)->value());
    }
    
    /**
     * Determines the average of previous ratings
     * @return integer the rounded average rating
     */
    public function getRating()
    {
        return intval(DB::query('SELECT ROUND(AVG(`Rating`)) as Rating FROM `KnowledgeBaseArticleRating` WHERE `ArticleID` = ' . $this->ID)->value());
    }
    
    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Content.Main', new CheckboxField('Featured','Feature this article?'), 'Content');
        return $fields;
    }

}

class KnowledgeBaseArticle_Controller extends KnowledgeBasePage_Controller
{
    static $allowed_actions = array('rate');

    function init()
    {
        parent::init();

        Requirements::javascript(KNOWLEDGEBASE_MODULE_DIR.'/javascript/jRating.jquery.min.js');
        Requirements::javascript(KNOWLEDGEBASE_MODULE_DIR.'/javascript/kb.rating.js');
    }

    /**
     * Retrieves the identifier that the current user is identified by
     * for the purposes of rating articles
     * @return string A unique identifier
     */
    protected function ratingIdentifier()
    {
        if ($cookie = Cookie::get('RatingIdentifier'))
            return $cookie;
        Cookie::set('RatingIdentifier', $cookie = uniqid());
        return $cookie;
    }

    /**
     * Determines if the user has already rated the current article
     */
    protected function userAlreadyRatedArticle()
    {
        // check by cookie
        if ($cookie = $this->ratingIdentifier())
        {
            $result = DB::query(sprintf("SELECT COUNT(*) FROM KnowledgeBaseArticleRating WHERE ArticleID = %d AND Cookie = '%s'", $this->data()->ID, Convert::raw2sql($cookie)
                            ))->value();
            if ($result > 0)
                return true;
        }

        // check by user
        if ($memberID = Member::currentUserID())
        {
            $result = DB::query(sprintf('SELECT COUNT(*) FROM KnowledgeBaseArticleRating WHERE ArticleID = %d AND AuthorID = %d', $this->data()->ID, $memberID
                            ))->value();
            if ($result > 0)
                return true;
        }

        return false;
    }

    /**
     * Rates the current page 
     * @todo Figure out how to display these messages on the front end. 
     * jRating doesn't seem to support server messages
     */
    public function rate()
    {
        if(!$this->data()->RatingEnabled)
            return json_message(array(
                    'Message' => _t('ERROR_RATING_DISABLED', "Rating is disabled on this article"),
                    'Result' => 'Error'
                ));
            
        // Ensure that this user isn't voting multiple times
        if ($this->userAlreadyRatedArticle())
            return json_encode(array(
                        'Message' => _t('ERROR_ALREADY_VOTED', "You've already voted on this article"),
                        'Result' => 'Error'
                    ));

        // Make sure the rating is valid
        $rating = isset($_REQUEST['rate'])
                ? intval($_REQUEST['rate'])
                : null;
        if (is_null($rating) || $rating < 0 || $rating > 20)
            return json_encode(array(
                        'Message' => _t('ERROR_PLEASE_SELECT', "Please select a rating"),
                        'Result' => 'Error'
                    ));

        // Save the  record
        $record = new KnowledgeBaseArticleRating();
        $record->Rating = $rating;
        $record->Cookie = $this->ratingIdentifier();
        $record->AuthorID = Member::currentUserID();
        $record->ArticleID = $this->data()->ID;
        $record->write();
                
        // Hurrah, return a nice ignorable message to the client
        return json_encode(array(
                    'Message' => _t('RATING_SUCCESS', 'Your rating has been recorded'),
                    'Result' => 'Success'
                ));
    }
}