# M2DeletedProductImage
Magento 2 Remove images of deleted product in /media/catalog/product folder and records in 'catalog_product_entity_media_gallery' table

--------------------
Purpose of this module 

1) find all images used by products in 'catalog_product_entity_media_gallery_value_to_entity'
    => table only with value_id need to find real path in 'catalog_product_entity_media_gallery'
    
2) find all images records in the 'catalog_product_entity_media_gallery' (same that media folder ? / seems to)

3) compare images records with images used in products
    => give path to delete !!! if image name already used by another available product 
    => need to exclude those already use
    
4) redults list of file to delete

Now we need to delete records of previous images in the 'catalog_product_entity_media_gallery' 
(using the script more than once)

Maybe add a clean cache for files older than 6 months instead of flushing all for performance


____________________

Second approach

- scan the media/catalog/product folder excluding cache | placeholder 
- compare with value_id in 'catalog_product_entity_media_gallery_value_to_entity'
  (need to find real name in 'catalog_product_entity_media_gallery')
- delete all files in the folder NOT in the table to_entity

+ fix problem of images name used for different product: 
deleted one AND still in catalog (in my case... maybe after migration from 1.7 ?)
+ avoid the long process of deleting old records in the database

add a clean cache for files older than 6 months instead of flushing all for performance
