@echo off

echo *** Starting 5_actions.bat ...

rem Query templates
rem /action%ext%?p=do_redirect_item&id=1
rem /action%ext%?p=do_redirect_source&id=some_source
rem /redirect/item/1
rem /redirect/source/some_source

set folder=5_actions

rem Positive
call	:check	redirect-item-1			item 1
call	:check	redirect-source-news		source education.usnews.com

rem Negative
call	:check	err_redirect
call	:check	err-redirect-item-id-		item
call	:check	err-redirect-item-id-0		item 0
call	:check	err-redirect-item-id--1		item -1
call	:check	err-redirect-item-id-99999	item 99999
call	:check	err-redirect-item-id-xxx	item xxx
call	:check	err-redirect-item-id-xxx1	item xxx1
call	:check	err-redirect-item-id-1xxx	item 1xxx
call	:check	err-redirect-source-		source
call	:check	err-redirect-source-xxx.xxx	source xxx.xxx
call	:check	err-redirect-item-inject	item 'select+true'
call	:check	err-redirect-source-inject	source 'select+true'

set file=
set agent=
set query=
set mode=
set folder=

goto :EOF

:check
call :check_full %*
call :check_fine %*
exit /b


rem -------------------------------------
:check_full

set query=%action_page%
if not "%2"=="" set "query=%query%?p=do_redirect_%2"
if "%2"=="item" goto :ITEM
if not "%3"=="" set "query=%query%&source=%3"
goto :AGENT
:ITEM
if not "%3"=="" set "query=%query%&id=%3"
:AGENT
set agent=TestFull
set mode=full
set file=%1.html
call :wget
exit /b


rem -------------------------------------
:check_fine
set query=redirect
if not "%2"=="" set "query=%query%/%2"
if not "%3"=="" set "query=%query%/%3"
set agent=TestFine
set mode=fine
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
