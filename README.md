# CleanMedia
Magento 2 CLI Command : remove images of deleted product in the `/media/catalog/product` folder

based on [EAV Cleaner Console Command](https://github.com/magento-hackathon/EAVCleaner/tree/magento2) from [FireGento e. V. - Hackathons](https://github.com/magento-hackathon)<br/>

--------------------
Purpose of this module

- scan the `media/catalog/product` folder  
- find all images used by products in the db :<br/>
`catalog_product_entity_media_gallery_value_to_entity` table gives `value_id` of images.<br/>
`catalog_product_entity_media_gallery` table gives `value` (real path)
- delete all files in the `media/catalog/product` folder NOT USED by any products.
- delete related records in database.

----------------------
## Installation
Copy all files in `/path/to/magento/app/code/Cap/CleanMedia`<br/><br/>
Run commands : <br/>
`php/bin magento module:enable Cap_CleanMedia`  
`php/bin magento setup:upgrade`  
`php/bin magento setup:di:compile`  
`php/bin magento cache:flush`  

----------------------
## Commands
`php bin/magento cap:clean:media`  
`php bin/magento cap:clean:media --dry-run`  
`php bin/magento cap:clean:media --no-cache`  
`php bin/magento cap:clean:media --dry-run --no-cache`  

----------------------
## Fix
<del>Export with filter activated</del>  
Export selected items  
Grid Cache spinner or progress bar while executing (could be very long)
Thumbnail preview : remove link to image details page  

## ToDo
-[x] Db placeholders  
-[x] Grid sort
-[x] Grid filters
-[x] Grid thumbnails
-[ ] Controller acl authorization
-[x] Grid column massDelete
-[x] Grid column actions
-[x] Grid bookmarks
-[x] Grid columns size & order
-[x] Grid filter
-[x] Grid export
-[x] Grid actions column => confirmation message before delete
-[x] Grid notice message on delete, massDelete actions  
-[x] Clean Database 
-[x] Clean Cache
-[x] Cli command

## Sources
- Menu  
[Admin Menu & ACL](http://www.maximehuran.fr/creation-dun-menu-dans-ladmin-et-gestion-des-droits-sous-magento-2/)  
[Admin Block](https://magento.stackexchange.com/a/138005/56025)  
[Admin Controller](http://www.maximehuran.fr/creation-dun-controlleur-admin-dans-magento-2/)  
[Module version](https://magento.stackexchange.com/a/99535/56025)  
- Grid  
[UiComponent listing](https://magento.stackexchange.com/a/150283/56025) for file collection  
[UiComponent listing](http://www.maximehuran.fr/creation-dun-uicomponent-sous-magento-2/) for db collection  
[DataProvider without db](https://magento.stackexchange.com/q/209682/56025)  
<del>[Thumbnail column](https://magento.stackexchange.com/a/150858/56025)</del>  
[Thumbnail column](https://magento.stackexchange.com/a/98364/56025)    
[Column date](https://magento.stackexchange.com/a/217365/56025)  
[Export](https://magento.stackexchange.com/a/210436/56025)  
[Export remove Xml & Xls](https://magento.stackexchange.com/a/294231/56025)
- Design  
[Css](https://magento.stackexchange.com/a/137442/56025)  

