@echo off

echo *** Starting 4_pages.bat ...

rem Query templates
rem /index.php?p=items&source=something&list=2
rem /items/source/something/list/2

set folder=4_pages

rem Positive
call	:check	home
call	:check	items 			items
call	:check	sources			sources
call	:check	items-list-2		items list 2
call	:check	items-list-3		items list 3
call	:check	items-list-22		items list 22
call	:check	items-education		items source education.usnews.com
call	:check	items-education-list-2	items source education.usnews.com list 2
call	:check	items-health		items source health.usnews.com
call	:check	items-health-list-2	items source health.usnews.com list 2
call	:check	items-health-list-3	items source health.usnews.com list 3
call	:check	items-health-list-4	items source health.usnews.com list 4
call	:check	items-money		items source money.usnews.com
call	:check	items-news		items source news.usnews.com
call	:check	items-opinion		items source opinion.usnews.com
call	:check	items-travel		items source travel.usnews.com
call	:check	items-CA		items filter CA
call	:check	items-news-CA		items source news.usnews.com filter CA
call	:check	items-CA-news		items filter CA source news.usnews.com

rem Negative
call	:check	err-_-404-1		_
call	:check	err-xxx-404-2		xxx
call	:check	err-items-list-		items list
call	:check	err-items-list--1	items list -1
call	:check	err-items-list-99	items list 99
call	:check	err-items-list-abc	items list abc
call	:check	err-items-source	items source
call	:check	err-items-source-xxx	items source xxx
call	:check	err-items-filter-	items filter
call	:check	err-items-filter-xxx	items filter xxx
call	:check	err-items-news1-CA	items source news.usnews.com1 filter CA
call	:check	err-items-news1-CA1	items source news.usnews.com1 filter CA1
call	:check	err-items-CA1-news	items filter CA1 source news.usnews.com
call	:check	err-items-CA1-news1	items filter CA1 source news.usnews.com1
call	:check	err-items-source-inject	items source 'select+true'
call	:check	err-items-filter-inject	items filter 'select+true'

set result=
set mode=
set file=
set agent=
set query=
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
if not "%2"=="" set "query=%query%?p=%2"
if not "%3"=="" set "query=%query%&%3="
if not "%4"=="" set "query=%query%%4"
if not "%5"=="" set "query=%query%&%5="
if not "%6"=="" set "query=%query%%6"
set agent=TestFull
set mode=full
set file=%1.html
call :wget
exit /b

rem -------------------------------------
:check_fine
set query=
if not "%2"=="" set "query=%query%%2"
if not "%3"=="" set "query=%query%/%3"
if not "%4"=="" set "query=%query%/%4"
if not "%5"=="" set "query=%query%/%5"
if not "%6"=="" set "query=%query%/%6"
set agent=TestFine
set mode=fine
set file=%1.html
call :wget
exit /b

rem -------------------------------------
:check_direct
set query=
if not "%2"=="" set "query=%query%%2"
if not "%3"=="" set "query=%query%/%3"
if not "%4"=="" set "query=%query%/%4"
if not "%5"=="" set "query=%query%/%5"
if not "%6"=="" set "query=%query%/%6"
set agent=TestDirect
set mode=direct
set file=%1.html
call :wget
exit /b

rem -------------------------------------
:wget
call 98_folders.bat %folder% %mode%
set result=%folder%\%mode%\%file%
echo "%wget%" -U %agent% -q -nv -a %log% -O "%output%\%result%" "_SITE_/%query%" >> %log%
call "%wget%" -U %agent% -q -nv -a %log% -O "%output%\%result%" "%site%/%query%" 2>nul
call 99_compare.bat %result%
exit /b
