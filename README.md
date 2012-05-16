# Knowledge Base module for Silverstripe

This module provides basic knowledge base functionality for silverstripe with the following features:
 * Article full text search with as-you-type drop-down suggestions
 * Article categorisation
 * Ajax powered article rating system (star system) which can be easily turned off

There is nothing really special about this, but it gives you everything you need
to build a simple knowledge base.

This module has been tested to work (and look terrible) out of the box with the
2.4 blackcandy theme installed; Other themes will require an additional subtheme.

## Credits and Authors

 * Damian Mooyman - <https://github.com/tractorcow/silverstripe-knowledgebase>

## Requirements

 * SilverStripe 2.4.7, may work on lower versions
 * PHP 5.2

## Installation Instructions

 * Extract all files into the 'knowledgebase' folder under your Silverstripe root.
 * Template away!

## General housekeeping

 * The knowledge base is designed to operate in a hierarchy similar to the below:

 * Knowledge Base root page
  * Category 1
		* Article
		* Category 1.1
			* Article
		* Category 1.2
			* Article
	* Category 2
		* Article

If you want to arrange your knowledge base into a slightly different structure 
(for instance, without categories) you may need to do a bit of re-coding. Submit 
a feature request if it doesn't work the way you want or expected it to.

## Options

You can disable ratings by using the following code in your _config.php

```php
KnowledgeBaseArticle::$rating_enabled = false;
```
