2.0.0
=============
- Add new commands  
`php bin/magento cap:clean:media --dry-run`  
`php bin/magento cap:clean:media --no-cache`  
- Add Db class in \Model\ResourceModel  

1.2.0
=============
* ! BUG FIX : Cache folder were completely removed 
* Regroup All Commands
* Step 1 => scan media FOLDER
* Step 2 => delete files with cache folder (no need to keep them, save a lot of disk usage)
* Step 3 => delete database entries
* New Command :

`php bin/magento cap:clean:media`

1.1.2
=============
* Change Module Name
* New Commands :

`php bin/magento cap:clean:media-folder`
`php bin/magento cap:clean:media-db`

Use `--dry-run` for TEST

1.1.1
=============
* Add Option for deleting records of images in databse<br/>
add `--include-db` to the Command Line

1.1.0
=============
* Add Option for scaning the `/cache` folder:
