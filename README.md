# M2DeletedProductImage
Magento 2 CLI Command : remove images of deleted product in the /media/catalog/product folder

based on [EAV Cleaner Console Command](https://github.com/magento-hackathon/EAVCleaner/tree/magento2) from [FireGento e. V. - Hackathons](https://github.com/magento-hackathon)<br/>
upload

--------------------
Purpose of this module

- scan the 'media/catalog/product' folder excluding cache
- find all images used by products in the DB :<br/>
`catalog_product_entity_media_gallery_value_to_entity` table gives `value_id` of images associated to a product<br/>
`catalog_product_entity_media_gallery` table gives `value` (real path) of `value_id`
- delete all files in the media/catalog/product folder NOT USED by any product


--------------------
TODO :

- Transform Command Line in Admin Module more easy to use

----------------------
## Installation

Copy all files in `/path/to/magento/app/code/Cap/M2DeletedProductImage`<br/><br/>
Run commands : <br/>
`php/bin magento module:enable Cap_M2DeletedProductImage`<br/>
`php/bin magento setup:upgrade`<br/>
`php/bin magento cache:flush`<br/>

----------------------
## Commands

Use --dry-run to check result without deleting any files

`php bin/magento cap:remove-deleted-product-image --dry-run`

`php bin/magento cap:remove-deleted-product-image`
