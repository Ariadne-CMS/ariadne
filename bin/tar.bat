@echo off
if EXIST ..\lib\configs\windows.bat call ..\lib\configs\windows.bat
tar -zc -C %1 %2 -f %3 %4

