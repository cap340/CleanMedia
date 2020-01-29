2.0.0
=============
- Admin page
- Cache cleaner
- Database cleaner
- Cli command with --dry-run & --no-cache options  
`php bin/magento cap:clean:media`  
`php bin/magento cap:clean:media --dry-run`  
`php bin/magento cap:clean:media --no-cache`  

1.2.0
=============
- ! BUG FIX : Cache folder were completely removed 
- Regroup All Commands
- Step 1 => scan media FOLDER
- Step 2 => delete files with cache folder (no need to keep them, save a lot of disk usage)
- Step 3 => delete database entries
- New Command :  
`php bin/magento cap:clean:media`

1.1.2
=============
- Change Module Name
- New Commands :  
`php bin/magento cap:clean:media-folder`
`php bin/magento cap:clean:media-db`

Use `--dry-run` for TEST

1.1.1
=============
- Add Option for deleting records of images in databse<br/>
add `--include-db` to the Command Line

1.1.0
=============
- Add Option for scanning the `/cache` folder:
