@echo off
call ../lib/configs/windows.bat
echo ar_installpath=%AR_INSTALL%
%AR_PHP%php -q %AR_INSTALL%install.php
