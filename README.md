# CleanMedia
Magento 2 CLI Command : remove images of deleted product in the `/media/catalog/product` folder

based on [EAV Cleaner Console Command](https://github.com/magento-hackathon/EAVCleaner/tree/magento2) from [FireGento e. V. - Hackathons](https://github.com/magento-hackathon)<br/>

--------------------
### Purpose of this module:  
When a product is deleted via Magento2 backend, all associated images still remain 
in the media folder which can quickly become a huge waste of disk space...  

### Steps: 
- query images used by products in the db :<br/>
`catalog_product_entity_media_gallery_value_to_entity` table gives `value_id` of images.<br/>
`catalog_product_entity_media_gallery` table gives `value` (real path).
- scan the `media` folder and compare with the previous query.
- delete results in the `media/catalog/product` folder.
- [fixme] delete related records in database.

----------------------
## Installation

- Clone this repository:  
`cd /path/to/magento/app/code`  
`git clone https://github.com/cap340/CleanMedia.git`  

- Via Composer:  
`composer require cap/cleanmedia`  

- Ftp:  
download and copy all files in `/path/to/magento/app/code/Cap/CleanMedia`  

Run commands :  
`php/bin magento module:enable Cap_CleanMedia`  
`php/bin magento setup:upgrade`  
`php/bin magento setup:di:compile`  
`php/bin magento cache:clean`  

----------------------
## Commands

Use --help to see command options.  
`php bin/magento cap:clean:media --help`  

`php bin/magento cap:clean:media`  

Use --dry-run to perform a dry-run command test.  
`php bin/magento cap:clean:media --dry-run`  

Use --limit=XXX: request issue ([#14][i14])  
limit the cleaner to XXX files for huge catalog.  
`php bin/magento cap:clean:media --limit=XXX`  

Combined options. (option order doesn't matter)  
`php bin/magento cap:clean:media --dry-run --limit=XXX`  
`php bin/magento cap:clean:media --limit=XXX --dry-run`  

----------------------
## Known issues

- Including cache folder could be very slow...  
We still can flush the cache in the Magento2 backend.
- Removing database entries is also very slow. (tested with 12.500 files to remove)  
elapsed time:  
  - With database (almost 35.000 entries): 8 minutes  
  - Without removing database entries : 12 secs !
  
----------------------
## Changelog

### 1.3.0 [2019-11-14]
#### Add  
- [feature] : add option: --limit=XXX  
request issue ([#14][i14])  
limit the cleaner to XXX files for huge catalog.

[i14]: https://github.com/cap340/CleanMedia/issues/14

- [feature] : add option: --dry-run  
perform a dry-run command test.  
(avoid double media folder iteration for testing & execute the cleaner)

#### Remove
- exclude the cache folder for performance  
- no longer uses the $objectManager !