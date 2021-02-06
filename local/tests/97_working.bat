@echo off

if "%1"=="" goto :WEBSITE
if "%1"=="website" goto :WEBSITE
if "%1"=="mobile" goto :MOBILE
if "%1"=="rest" goto :REST

:WEBSITE
set site=%website%
set output=output
set origin=origin
goto :CHECK_FOLDERS

:MOBILE
set site=%mobile%
set output=output\mobile
set origin=origin\mobile
goto :CHECK_FOLDERS

:REST
set site=%website%
set output=output\rest
set origin=origin\rest
goto :CHECK_FOLDERS

:CHECK_FOLDERS
if not exist %output% mkdir %output%
if not exist %origin% mkdir %origin%

exit /b
