# archivebinge
Source code of archivebinge.com, made publicly available for derivation and rehosting


Things that need to be updated before use. Any instance of:
- DOMAIN.COM should be updated to the host domain
- HANDLE should be updated to a live social media handle
- '/full/path/to/crawlerenv/' should be updated to the virtual env created under the installation steps
- DATABASE_HOST should be your database host (probably `localhost`)
- DATABASE_USER should be your database user
- DATABASE_PASSWORD should be your database password
- DATABASE_NAME should be your database name


files to rename:
mv crawler/minisup.sample.py crawler/minisup.py
mv crawler/supervisor.sample.py crawler/supervisor.py
mv includes/conf.inc.sample.php includes/conf.inc.php
