@echo off
@call ../lib/configs/windows.bat
%AR_PHP%php -q import %1 %2 %3 %4 %5 %6 %7
