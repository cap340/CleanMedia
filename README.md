# CleanMedia
Magento 2 CLI Command : remove images of deleted product in the `/media/catalog/product` folder

based on [EAV Cleaner Console Command](https://github.com/magento-hackathon/EAVCleaner/tree/magento2) from [FireGento e. V. - Hackathons](https://github.com/magento-hackathon)<br/>

--------------------
Purpose of this module

- scan the `media/catalog/product` folder excluding OR including `/cache`
- find all images used by products in the db :<br/>
`catalog_product_entity_media_gallery_value_to_entity` table gives `value_id` of images.<br/>
`catalog_product_entity_media_gallery` table gives `value` (real path)
- delete all files in the `media/catalog/product` folder NOT USED by any products.
- delete related records in database.

----------------------
## Installation

Copy all files in `/path/to/magento/app/code/Cap/CleanMedia`<br/><br/>
Run commands : <br/>
`php bin/magento module:enable Cap_CleanMedia`<br/>
`php bin/magento setup:upgrade`<br/>
`php bin/magento cache:flush`<br/>

----------------------
## Commands
 

Use --dry-run to check result without deleting any files

`php bin/magento cap:clean:media-folder --dry-run`

`php bin/magento cap:clean:media-folder`
`php bin/magento cap:clean:media-db`

----------------------
## Options

`php bin/magento cap:clean:media-folder --exclude-cache` to exclude the `/cache` folder.
