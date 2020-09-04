@echo off

echo *** Starting 6_view.bat ...

rem Query templates
rem /index.php?p=view_item&id=1[&title=something]
rem /item/1[/title/something]

set folder=6_view

rem Positive
call	:check	view-item-id-1	item id 1
call	:check	view-item-id-2	item id 2
call	:check	view-item-id-545		item id 545

rem Negative
call	:check	err-view-item		item
call	:check	err-view-item-id-	item id
call	:check	err-view-item-id-0	item id 0
call	:check	err-view-item-id--1	item id -1
call	:check	err-view-item-id-99999	item id 99999
call	:check	err-view-item-id-xxx	item id xxx
call	:check	err-view-item-id-xxx1	item id xxx1
call	:check	err-view-item-id-1xxx	item id 1xxx
call	:check	err-view-item-id1-	item id1
call	:check	err-view-item-id1-1	item id1 1
call	:check	err-view-item-inject	item id 'select+true'

set file=
set agent=
set query=
set mode=
set folder=

goto :EOF

:check
call :check_full %*
call :check_fine %*
call :check_direct %*
exit /b


rem -------------------------------------
:check_full

set query=index.php
if not "%2"=="" set "query=%query%?p=view_%2"
if not "%3"=="" set "query=%query%&%3="
if not "%4"=="" set "query=%query%%4"
set agent=TestFull
set mode=full
set file=%1.html
call :wget
exit /b


rem -------------------------------------
:check_fine
set query=
if not "%2"=="" set "query=%query%%2"
if not "%4"=="" set "query=%query%/%4"
set agent=TestFine
set mode=fine
set file=%1.html
call :wget
exit /b


rem -------------------------------------
:check_direct
set query=
if not "%2"=="" set "query=%query%%2"
if not "%4"=="" set "query=%query%/%4"
set agent=TestDirect
set mode=direct
set file=%1.html
call :wget
exit /b


rem -------------------------------------
:wget
call 98_folders.bat %folder% %mode%
echo "%wget%" -U %agent% -q -nv -a %log% -O "%output%\%folder%\%mode%\%file%" "_SITE_/%query%" >> %log%
call "%wget%" -U %agent% -q -nv -a %log% -O "%output%\%folder%\%mode%\%file%" "%site%/%query%" 2>nul
call 99_compare.bat %folder%\%mode%\%file%
exit /b
