@echo off
if EXIST ..\lib\configs\windows.bat call ..\lib\configs\windows.bat
tar -zx -C %1 -f %2 %3

