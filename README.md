# CleanMedia
Magento 2 CLI Command : remove images of deleted product in the `/media/catalog/product` folder

based on [EAV Cleaner Console Command](https://github.com/magento-hackathon/EAVCleaner/tree/magento2) from [FireGento e. V. - Hackathons](https://github.com/magento-hackathon)<br/>

--------------------
Purpose of this module

- scan the `media/catalog/product` folder including `/cache`
- find all images used by products in the db :  
`catalog_product_entity_media_gallery_value_to_entity` table gives `value_id` of images.  
`catalog_product_entity_media_gallery` table gives `value` (real path)
- delete all files in the `media/catalog/product` folder NOT USED by any products.  
- delete related records in database.  

----------------------
## Installation

Copy all files in `/path/to/magento/app/code/Cap/CleanMedia`  
Run commands :  
`php/bin magento module:enable Cap_CleanMedia`  
`php/bin magento setup:upgrade`  

----------------------
## Admin Grid

Go to System > Tools > CleanMedia

----------------------
## CLI Commands

`php bin/magento cap:clean:media --help`  
`php bin/magento cap:clean:media`  
`php bin/magento cap:clean:media --dry-run`  
`php bin/magento cap:clean:media --no-cache`  
`php bin/magento cap:clean:media --dry-run --no-cache`  

`php bin/magento cap:clean:db --help`  
`php bin/magento cap:clean:db`  
`php bin/magento cap:clean:db --dry-run`  

