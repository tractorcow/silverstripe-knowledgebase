<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CustomSearchForm
 *
 * @author Damo
 */
class CustomSearchForm extends SearchForm
{
    protected $_extraFilter = null;

    protected $_searchField = 'Search';

    public function setExtraFilter($filter)
    {
        $this->_extraFilter = $filter;
    }

    public function getExtraFilter()
    {
        return $this->_extraFilter;
    }

    public function setSearchField($fieldName)
    {
        $this->_searchField = $fieldName;
    }

    public function getSearchField()
    {
        return $this->_searchField;
    }
	
	/**
	 * Get the search query for display in a "You searched for ..." sentence.
	 * 
	 * @param array $data
	 * @return string
	 */
    public function getSearchQuery($data = null)
    {
		if(!isset($data))
            $data = $_REQUEST;
        
        if (!is_array($this->_searchField))
            return $data[$this->_searchField];
        
        // find first best result
        foreach ($this->_searchField as $field)
            if (isset($data[$field]))
                return $data[$field];
    }

    /**
     * Return dataObjectSet of the results using $_REQUEST to get info from form.
     * Wraps around {@link searchEngine()}.
     * 
     * @param int $pageLength DEPRECATED 2.3 Use SearchForm->pageLength
     * @param array $data Request data as an associative array. Should contain at least a key 'Search' with all searched keywords.
     * @return DataObjectSet
     */
    public function getResults($pageLength = null, $data = null)
    {
        // legacy usage: $data was defaulting to $_REQUEST, parameter not passed in doc.silverstripe.org tutorials
        if (!isset($data) || !is_array($data))
            $data = $_REQUEST;

        // set language (if present)
        if (singleton('SiteTree')->hasExtension('Translatable') && isset($data['locale']))
        {
            $origLocale = Translatable::get_current_locale();
            Translatable::set_current_locale($data['locale']);
        }

        // Check all given search fields
        $keywords = $this->getSearchQuery($data);
        if(empty($keywords))
            return new DataObjectSet();

        $andProcessor = create_function('$matches', '
	 		return " +" . $matches[2] . " +" . $matches[4] . " ";
	 	');
        $notProcessor = create_function('$matches', '
	 		return " -" . $matches[3];
	 	');

        $keywords = preg_replace_callback('/()("[^()"]+")( and )("[^"()]+")()/i', $andProcessor, $keywords);
        $keywords = preg_replace_callback('/(^| )([^() ]+)( and )([^ ()]+)( |$)/i', $andProcessor, $keywords);
        $keywords = preg_replace_callback('/(^| )(not )("[^"()]+")/i', $notProcessor, $keywords);
        $keywords = preg_replace_callback('/(^| )(not )([^() ]+)( |$)/i', $notProcessor, $keywords);

        $keywords = $this->addStarsToKeywords($keywords);

        if (!$pageLength)
            $pageLength = $this->pageLength;
        $start = isset($_GET['start'])
                ? (int) $_GET['start']
                : 0;

        if (strpos($keywords, '"') !== false || strpos($keywords, '+') !== false || strpos($keywords, '-') !== false || strpos($keywords, '*') !== false)
        {
            $results = DB::getConn()->searchEngine($this->classesToSearch, $keywords, $start, $pageLength, "\"Relevance\" DESC", $this->_extraFilter, true);
        }
        else
        {
            $results = DB::getConn()->searchEngine($this->classesToSearch, $keywords, $start, $pageLength, null, $this->_extraFilter);
        }

        // filter by permission
        if ($results)
            foreach ($results as $result)
            {
                if (!$result->canView())
                    $results->remove($result);
            }

        // reset locale
        if (singleton('SiteTree')->hasExtension('Translatable') && isset($data['locale']))
        {
            Translatable::set_current_locale($origLocale);
        }

        return $results;
    }

}
