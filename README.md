# M2DeletedProductImage
Magento 2 CLI Command : remove images of deleted product in the /media/catalog/product folder

based on [EAV Cleaner Console Command](https://github.com/magento-hackathon/EAVCleaner/tree/magento2) from [FireGento e. V. - Hackathons](https://github.com/magento-hackathon)

--------------------
Purpose of this module

- scan the media/catalog/product folder excluding cache
- fetch all images used by products in the DB :
    'catalog_product_entity_media_gallery_value_to_entity' table gives only 'value_id'
    => need to find real path ('value') of the 'value_id' in 'catalog_product_entity_media_gallery'
- delete all files in the media/catalog/product folder NOT USED by any product (in the 'catalog_product_entity_media_gallery_value_to_entity' table)

TODO :
- Clean any cached files older than 6 months instead of flushing all for performance

----------------------
## Commands

Use --dry-run to check result without deleting any files

`php bin/magento cap:media:remove-deleted --dry-run`

`php bin/magento cap:media:remove-deleted`
