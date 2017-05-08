#Recipes Elasticsearch Backend 

## Installation
1. enable the module, this module relies on elasticsearch_helper.
2. index content: fab config:mbb drush:'index-recipes';
   this creates a 'recipes_index', 
   implemented in src/Normalizer/NodeNormalizer.php  - constructs the data object for indexing
   
   recipes_elasticsearch.module - defines the index and field properties, 
   http://example.com.dev:9200/_search?pretty=true
   http://example.com.dev:9200/_mapping?pretty=true

3. to search for content use the autocomplete search block, 
   that leverages drupal autocomplete form api feature.
   elasticsearch is exposed as an end-point.
   suggestion and search logic are implemented in 
   src/Controller/ElasticSearchController.php
   the debug function is used for development of new features, use 
   http://example.com.dev/recipes_elasticsearch/debug?q=your-input
   to access it.
    



