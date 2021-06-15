@echo off

rem Create sub-folders in output & origin

rem %1 %2 ... - names of sub-folders

if "%1"=="" goto :EOF
if not exist %output%\%1 mkdir %output%\%1
if not exist %origin_folder%\%1 mkdir %origin_folder%\%1

if "%2"=="" goto :EOF
if not exist %output%\%1\%2 mkdir %output%\%1\%2
if not exist %origin_folder%\%1\%2 mkdir %origin_folder%\%1\%2

if "%3"=="" goto :EOF
if not exist %output%\%1\%2\%3 mkdir %output%\%1\%2\%3
if not exist %origin_folder%\%1\%2\%3 mkdir %origin_folder%\%1\%2\%3

:EOF