The MovieSorter
===============

Rename all your *legally obtained* movie folders from their bizarrely named formats to something   lovely and neat :D

Before:  
`./Some.Obscene.Folder.Title.2011.1080p.Bluray-AN0NYmous`  
After:  
`./Some Obscene Folder Title (2011) [BluRay 1080p]`


Install:
--------

`composer install`  
`php install.php`

Run *at your own risk!*
------------------------
(seriously, I suggest you create some test folders to try this out on and read the code for your peace of mind)

`php run.php /path/to/movie/folders [--dry] [--redo]`

### Options:

`--dry` Dry run does not modify folders  
`--redo` Will not skip folders already processed

The StringExploder
==================

The StringExploder is a decoupled toolkit/framework for breaking strings apart into structured, named indexes.

 - `Indexer`: Stores and retrieves words for later indexing. It's pretty basic.
 - `AbstractIndexable`: Once extended, this will define the type of information to be processed.  (See: `MovieSorter\Movie`)
 - `AbstractIndexingCommand`: Once extended, this will provide a CLI for processing a batch of data. (See: `MovieSorter\CleanFoldersCommand`)

The MovieSorter is the canonical implementation of this (for now) however I can see myself using this for batch processing of **physical address** strings, **full name** strings or any other nasty unstructured data.
