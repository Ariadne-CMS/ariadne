@echo off
call ../lib/configs/windows.bat
%AR_PHP%php -q import %1 %2
